<?php

use HelloSign\Client as HelloSignClient;

/**
 * signing actions.
 *
 * @package    permitflow
 * @subpackage signing
 * @author     George
 * @version    SVN: $Id$
 */
class signingActions extends sfActions
{
    public function __construct($context, $moduleName, $actionName)
    {
        parent::__construct($context, $moduleName, $actionName);
        $this->base_url_ = (empty($_SERVER['HTTPS']) ? "http://" : "https://") . $_SERVER['HTTP_HOST'];
    }

    #######################################
    ############### DOCUSIGN ##############
    #######################################
    public function executeIndex(sfWebRequest $request)
    {
        # save file
        # login to docusign
        # pass document for signing
        # sign in
        # download it back to server
        $action = $request->getParameter('permitaction');

        # path to storage
        $client_id = sfConfig::get('app_docusign_integration_key');

        # authenticate the user
        if ($action == 'signdocument') {
            $redirect_uri = $this->base_url_ . "/backend.php/signing";

            $data_pass = json_encode(
                array("permitaction" => "getaccesstoken",
                    'next_action' => $request->getParameter('next_action')
                ));
                
            $login_url = sfConfig::get('app_docusign_path')
                ."/oauth/auth?"
                . "response_type=code"
                . "&scope=signature"
                . "&client_id=$client_id"
                . "&state=$data_pass"
                . "&redirect_uri=$redirect_uri";
            $this->redirect($login_url);
        }

        # Start the actual signing
        if (($state = $request->getParameter('state'))) {
            error_log($state);
            $state = json_decode($state, true);
            $next_action = $state['next_action'];
            $k = explode('/', $state['local_file']);
            $file_name = $k[count($k) - 1];

            if ($state['permitaction'] == 'getaccesstoken') {
                $code = $request->getParameter('code');
                $args = $this->getAccessToken($code);
                $args['document_path'] = $state['local_file'];

                $args = $args + $state;
                if ($next_action == 'download') {
                    $this->download_signed_document_from_docusign($args);
                } else {
                    $url = $this->embedded_signing_ceremony($args);
                    $this->redirect($url);
                }
            }
        }

        if ($action == 'download_signed_permit') {
            $redirect_uri = $this->base_url_ . "/backend.php/signing";
            $data_pass = json_encode(
                ["permitaction" => "getaccesstoken",
                    'next_action' => 'download'] + $request->getGetParameters());

            $login_url = sfConfig::get('app_docusign_path')
                . "/oauth/auth?"
                . "response_type=code"
                . "&scope=signature"
                . "&client_id=$client_id"
                . "&state=$data_pass"
                . "&redirect_uri=$redirect_uri";
            $this->redirect($login_url);
        }

        $this->redirect('/backend.php/dashboard');
    }

    /**
     * save file to un-signed permits
     */
    function saveUnSignedDocument($permit_id)
    {
        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $permit_id);
        $permit = $q->fetchOne();


        if ($permit) {
            $permit_manager = new PermitManager();
            $file_name = $permit_manager->permit_file_name($permit);

            if (file_exists("app/permits/unsigned/$file_name")) {
                error_log("$file_name already exists as unsigned");
                return;
            }

            $output = $permit_manager->get_pdf_output($permit->getId());

            $file = fopen("app/permits/unsigned/$file_name", 'w');
            fwrite($file, $output);
            fclose($file);

        } else {
            echo "Invalid Permit Link";
        }
    }

    function getAccessToken($code)
    {
        # Authorization
        $auth_link=sfConfig::get('app_docusign_path')."/oauth/token";
        $authorization_request = curl_init($auth_link);
        $integration_and_secret_key = base64_encode(sfConfig::get('app_docusign_integration_key') . ':' . sfConfig::get('app_docusign_secret_key'));

        curl_setopt($authorization_request, CURLOPT_HTTPHEADER, array(
            "Authorization: Basic " . $integration_and_secret_key,
            "Accepts: application/json",
            "Content-Type: application/x-www-form-urlencoded",
        ));

        curl_setopt_array($authorization_request, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'grant_type' => 'authorization_code',
                'code' => $code,
            )),
        ));

        curl_setopt($authorization_request, CURLOPT_RETURNTRANSFER, true);
        $authorization_response = curl_exec($authorization_request);
        curl_close($authorization_request);

        error_log("authorization :: " . $authorization_response);

        $authorization_response = json_decode($authorization_response, true);


        # User details
        $auth_user=sfConfig::get('app_docusign_path')."/oauth/userinfo";
        $user_details_request = curl_init($auth_user);
        curl_setopt($user_details_request, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $authorization_response['access_token'],
            "Accepts: application/json",
        ));

        curl_setopt($user_details_request, CURLOPT_RETURNTRANSFER, true);
        $user_details_response = curl_exec($user_details_request);

        error_log("user details :: " . $user_details_response);
        $user_details_response = json_decode($user_details_response, true);
        print_r($user_details_request);
        curl_close($user_details_request);

        return [
            'access_token' => $authorization_response['access_token'],
            'account_id' => $user_details_response['accounts'][0]['account_id'],
            'signer_name' => $user_details_response['name'],
            'signer_email' => $user_details_response['email'],
        ];
    }

    function embedded_signing_ceremony($args)
    {
        $files = json_decode(Functions::lastSigningSession()['documents']);
        $accountId = $args['account_id'];
        $signerName = $args['signer_name'];
        $signerEmail = $args['signer_email'];

        # The url of this web application's folder. If you leave it blank, the script will attempt to figure it out.
        $clientUserId = '123'; # Used to indicate that the signer will use an embedded
        # Signing Ceremony. Represents the signer's userId within
        # your application.
        $authenticationMethod = 'None'; # How is this application authenticating
        # the signer? See the `authenticationMethod' definition
        # https://developers.docusign.com/esign-rest-api/reference/Envelopes/EnvelopeViews/createRecipient

        # Constants
        $appPath = getcwd();

        $documents = [];
        $sign_position = [];

        foreach ($files as $file) {
            $file_name = $file->name;
            $file_path = $appPath . "/" . $file->url;

            $contentBytes = file_get_contents($file_path);
            $base64FileContent = base64_encode($contentBytes);

            # create the DocuSign document object
            array_push($documents, new DocuSign\eSign\Model\Document([
                'document_base64' => $base64FileContent,
                'name' => $file_name, # can be different from actual file name
                'file_extension' => 'pdf', # many different document types are accepted
                'document_id' => $file->id # a label used to reference the doc
            ]));
        }

        # DocuSign SignHere field/tab object
        array_push($sign_position, $signHere = new DocuSign\eSign\Model\SignHere([
            'document_id' => sizeof(base64_encode(json_encode($files))),
            'page_number' => '1',
            'tab_label' => "Sign Permit " . count($documents),

            'anchor_string' => 'sig|req|signer1',
            'anchor_y_offset' => '10',
            'anchor_x_offset' => '8'
        ]));

        # The signer object
        $signer = new DocuSign\eSign\Model\Signer([
            'email' => $signerEmail, 'name' => $signerName, 'recipient_id' => "1", 'routing_order' => "1",
            'client_user_id' => $clientUserId # Setting the client_user_id marks the signer as embedded
        ]);


        # Add the tabs to the signer object
        # The Tabs object wants arrays of the different field/tab types
        $signer->setTabs(new DocuSign\eSign\Model\Tabs(['sign_here_tabs' => $sign_position]));

        # Next, create the top level envelope definition and populate it.
        $envelopeDefinition = new DocuSign\eSign\Model\EnvelopeDefinition([
            'email_subject' => "Please sign this document",
            'documents' => $documents, # The order in the docs array determines the order in the envelope
            # The Recipients object wants arrays for each recipient type
            'recipients' => new DocuSign\eSign\Model\Recipients(['signers' => [$signer]]),
            'status' => "sent" # requests that the envelope be created and sent.
        ]);

        #
        #  Step 2. Create/send the envelope.
        #

        $envelopeApi = $this->get_envelops_api($args);
        $results = $envelopeApi->createEnvelope($accountId, $envelopeDefinition);
        $envelopeId = $results['envelope_id'];

        #
        # Step 3. The envelope has been created.
        #         Request a Recipient View URL (the Signing Ceremony URL)
        #

        $returnUrl = $this->base_url_ . "/backend.php/signing?"
            . "&permitaction=download_signed_permit"
            . "&l_redirect=" . $args['l_redirect']
            . "&envelope_id=$envelopeId"
            . '&account_id=' . $args['account_id']
            . "&access_token=" . $args['access_token'];

        error_log("return url " . $returnUrl);

        $recipientViewRequest = new DocuSign\eSign\Model\RecipientViewRequest([
            'authentication_method' => $authenticationMethod,
            'client_user_id' => $clientUserId,
            'recipient_id' => '1',
            'return_url' => $returnUrl,
            'user_name' => $signerName,
            'email' => $signerEmail
        ]);

        $results = $envelopeApi->createRecipientView($accountId, $envelopeId, $recipientViewRequest);

        ### note signing session
        $this->update_current_signing_session($envelopeId);

        # Step 4. The Recipient View URL (the Signing Ceremony URL) has been received.
        #         The user's browser will be redirected to it.
        #
        return $results['url'];
    }

    function download_signed_document_from_docusign($args)
    {
        error_log("Started download");
        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();

        try {
            $envelope_api = $this->get_envelops_api($args);
            # BEGIN log start of signing session
            # find the session for this envelope
            $envelopeId = $args['envelope_id'];

            if ($session = $conn->fetchAssoc("SELECT * FROM user_signing_sessions WHERE envelop_id = '$envelopeId'")) {
                $me = Functions::current_user()->getNid();
                $session = $session[0];

                foreach (json_decode($session['documents']) as $doc) {
                    $result = $envelope_api->getDocument($args['account_id'], $doc->id, $args['envelope_id']);
                    if ($doc->type == 'SavedPermit') {
                        $permit = Doctrine_Core::getTable('SavedPermit')->find($doc->id);
                        $path = "app/permits/signed/" . $permit->getFileName();
//                        rename($result->getPathname(), $path);
                        file_put_contents($path, file_get_contents($result->getPathname()));
                        /*$new_file = fopen($path, "w");
                        $old = fopen($result->getPathname(), 'r');
                        $content = fread($old, $result->ftell()); # assumes that pointer is on EOF
                        error_log('-------Content--------');
                        error_log(var_export($content,true));
                        fwrite($new_file, $content);
                        fclose($new_file);*/

                        # update permit to show who signed it
                        $permit->setSignedBy($me);
                        $slug = $permit->getTaskSlug();
                        $permit->save();

                        # update task to completed
                        # -> find task(s) for signing this permit
                    }

                    if ($doc->type == 'Attachment') {
                        rename($result->getPathname(), str_replace('.pdf', '--signed.pdf', $doc->url));
                        $slug = $doc->slug;
                    }

                    $conn->execute("UPDATE task SET status = 25 WHERE task_application_slug = \"$slug\"");
                }

                $this->update_my_remaining_session($session);
            }
            # END logging signing session
        } catch (Exception $e) {
            print ($e->getMessage());
        }
    }

    function get_envelops_api($args)
    {
        # The API base_path
        $basePath = 'https://demo.docusign.net/restapi';
        $accessToken = $args['access_token'];

        $config = new DocuSign\eSign\Configuration();
        $config->setHost($basePath);
        $config->addDefaultHeader("Authorization", "Bearer " . $accessToken);
        $apiClient = new DocuSign\eSign\Client\ApiClient($config);
        return new DocuSign\eSign\Api\EnvelopesApi($apiClient);
    }

    #######################################
    ########### END DOCUSIGN ##############
    #######################################


    #######################################
    ############ HELLOSIGN ################
    #######################################
    /**
     * receive all callbacks on signing requests
     * download the documents
     *
     * @param sfWebRequest $request
     */
    function executeCallback(sfWebRequest $request)
    {
        # callbacks come here
        $get_result = json_encode($_GET);
        error_log('get :: ' . $get_result);

        if (array_key_exists('json', $_POST)) {
            $post_data = $_POST['json'];
            error_log('post:: ' . $post_data);

            $client = $this->getClient();
            $post_data = json_decode($post_data, true);

            if (array_key_exists('event', $post_data)
                and $event = $post_data['event']
                and $event_type = $event['event_type']) {

                if (array_key_exists('signature_request', $post_data) and $signature_request = $post_data['signature_request']) {
                    $metadata = (array_key_exists('metadata', $signature_request) and $metadata = $signature_request['metadata']) ? $metadata : null;
                    $signature_request_id = null;

                    # download files
                    if (array_key_exists('signature_request_id', $signature_request)
                        and $signature_request_id = $signature_request['signature_request_id']) {
                        if ($event_type == 'signature_request_downloadable') { # ready to download
                            $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
                            if ($session = $conn->fetchAssoc("SELECT * FROM user_signing_sessions WHERE envelop_id = '$signature_request_id'")) {
                                $session = $session[0];

                                $person = $session['user_id'];
                                $session_documents = json_decode($session['documents']);

                                $zip_folder = 'app/permits/signed/' . $signature_request_id;

                                # download the files as a zip
                                $client->getFiles($signature_request_id, "$zip_folder.zip", 'zip');

                                if (array_key_exists('files', $metadata) and $files = explode(',', $metadata['files'])) {

                                    # extract the zip
                                    $zip_archive = new ZipArchive;
                                    if ($res = $zip_archive->open("$zip_folder.zip")) {
                                        $zip_archive->extractTo($zip_folder);
                                        $zip_archive->close();

                                        $downloaded_files = scandir($zip_folder, 1);
                                        # rename and move each file to app/permits/signed/_file_name_.pdf
                                        foreach ($files as $index => $file) {
                                            foreach ($downloaded_files as $downloaded_file) {
                                                $substr_ = $index + 1;
                                                $should_commit = strpos($downloaded_file, "$substr_");
                                                if ($should_commit !== false) {
                                                    $downloaded_file = $zip_folder . "/" . $downloaded_file;

                                                    if (strpos($file, 'unsigned/') !== false and $permit_id = explode('-', $file) and $permit_id = array_pop($permit_id)) {
                                                        $signed_file_path = str_replace('unsigned', 'signed', $file);
                                                        rename($downloaded_file, $signed_file_path);
                                                        if ($permit = Doctrine_Core::getTable('SavedPermit')->find($permit_id)) {
                                                            $permit->setSignedBy($person);
                                                            $permit->save();

                                                            $slug = $permit->getTaskSlug();
                                                            $conn->execute("UPDATE task SET status = 25 WHERE task_application_slug = \"$slug\"");
                                                        }
                                                    } else {
                                                        # attachment
                                                        $signed_file_path = str_replace('.pdf', '--signed.pdf', $file);
                                                        rename($downloaded_file, $signed_file_path);

                                                        # get the slug for this attachment
                                                        $attachment_file_name_arr = explode( '/', $file);
                                                        $attachment_file_name = array_pop($attachment_file_name_arr);

                                                        $slug = array_filter($session_documents, function ($doc) use (&$attachment_file_name) {
                                                            $doc_file_name_arr = explode( '/', $doc->url);
                                                            $doc_file_name = array_pop($doc_file_name_arr);
                                                            if ($doc->type == "Attachment" and $attachment_file_name == $doc_file_name) {
                                                                return $doc->slug;
                                                            }
                                                            return null;
                                                        });

                                                        if ($slug) {
                                                            $slug = $slug[0]->slug;
                                                            $conn->execute("UPDATE task SET status = 25 WHERE task_application_slug = '$slug'");
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        #move audit file
                                        rename($zip_folder . "/Audit Trail.pdf", "app/permits/signed/$signature_request_id.pdf");
                                        rmdir($zip_folder);
                                        unlink($zip_folder . ".zip");
                                    } else {
                                        error_log("Failed to unzip $zip_folder.zip");
                                    }
                                }
                            }
                        }
                    }

                    if ($metadata and array_key_exists('session_id', $metadata)
                        and $session_id = $metadata['session_id']
                        and $signature_request_id) {
                        $this->update_current_signing_session($signature_request_id, $session_id);
                    }
                }
            }
        }


        echo "Hello API Event Received";
        return sfView::NONE;
    }

    /**
     * take documents on queue,
     * avail them for signing request creation
     * get url to use with embedded.js for hellosign
     * return to the calling url
     * embeddedsigningrequest
     * @param sfWebRequest $request
     */
    function executeEmbeddedsigningrequest(sfWebRequest $request)
    {
        $GET = $request->getGetParameters();
        $redirect_to = $GET['redirect_to'];

        $last_session = Functions::lastSigningSession();
        $files = json_decode($last_session['documents']);
        error_log('-----------FILES LAST SESSION--------');
        error_log(var_export($files,true));

        if (!($client = $this->getClient())) {
            echo 'HelloSign need to be set up first';
            exit;
        }

        $request = new HelloSign\SignatureRequest();
        $request->enableTestMode();
        $request->setSubject('Signing CP Documents');
        $request->setMessage('Proceed to sign these document(s)');

        # my email address
        $request->addSigner(sfConfig::get('app_hellosign_useremail'), sfConfig::get('app_hellosign_username'), 0);
        $request->setUseTextTags(true);
        $request->setHideTextTags(false);

        $signing_positions = [];
        $appPath = getcwd();

        $file_names = [];

        foreach ($files as $file) {
            $file_path = $appPath . "/" . $file->url;
            array_push($file_names, $file_path);
            $request->addFile($file_path);
        }

        # note session
        $request->addMetadata('session_id', $last_session['id']);
        $request->addMetadata('files', implode(',', $file_names));

        $hello_client_id = sfConfig::get('app_hellosign_client_id');
        $embedded_request = new HelloSign\EmbeddedSignatureRequest($request, $hello_client_id);
        $embedded_request->enableTestMode();
        $response = $client->createEmbeddedSignatureRequest($embedded_request);

        $signatures = $response->getSignatures();
        $signature_id = $signatures[0]->getId();

        $response = $client->getEmbeddedSignUrl($signature_id);
        $sign_url = $response->getSignUrl();

        $session_id = $last_session['id'];
        return $this->redirect(base64_decode($redirect_to) . "SESS=$session_id&sign_url=" . base64_encode($sign_url) . '&client_id=' . base64_encode($hello_client_id));
    }

    protected $hello_client;

    function getClient()
    {
        $hello_api_key = sfConfig::get('app_hellosign_api_key');

        if (!$hello_api_key) {
            return null;
        }

        if (!$this->hello_client) {
            $this->hello_client = new HelloSignClient($hello_api_key);
        }

        return $this->hello_client;
    }

    /**
     * once a session has been complete mark it as completed
     * marksessionascomplete
     * @param sfWebRequest $request
     * @throws Doctrine_Connection_Exception
     */
    function executeMarksessionascomplete(sfWebRequest $request)
    {
        if ($session_id = $request->getPostParameter('session_id')) {
            if ($session = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc("SELECT * FROM user_signing_sessions WHERE id = $session_id")) {
                $session = $session[0];
                $this->update_my_remaining_session($session);
            }
        }

        return $this->renderJson(['status' => 'complete']);
    }

    #######################################
    ########## END HELLOSIGN ##############
    #######################################


    /**
     * for Docusign the envelop id has a direct relationship
     * Hellosign will store its signing_request_id on the envelop_id
     *
     * @param $envelopeId
     * @throws Doctrine_Connection_Exception
     */
    function update_current_signing_session($envelopeId, $last_signing_session = null)
    {
        # BEGIN log start of signing session
        $now = date('Y-m-d h:i:s');

        if (!$last_signing_session) {
            $last_signing_session = Functions::lastSigningSession()['id'];
        }

        if (!$last_signing_session)
            return;

        $q = "UPDATE user_signing_sessions SET started_at = '$now', envelop_id = '$envelopeId' WHERE id = $last_signing_session";
        Doctrine_Manager::getInstance()->getCurrentConnection()->execute($q);
        # END logging signing session
    }

    function update_my_remaining_session($session)
    {
        $me = $session['user_id'];
        $envelopeId = $session['envelop_id'];

        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        $result = $conn->fetchAssoc("SELECT used_signatures FROM user_signings WHERE user_id = $me");

        # update my remaining sessions
        if ($session and !$session['completed_at'] and $result) {
            $k = $result[0]['used_signatures'] ?: 0;
            $k++;
            $conn->execute("UPDATE user_signings SET used_signatures = $k WHERE user_id = $me");
        }

        if ($session and !$session['completed_at']) {
            $now = date('Y-m-d h:i:s');
            $conn->execute("UPDATE user_signing_sessions  SET completed_at = '$now', status = 2 WHERE envelop_id = '$envelopeId' and user_id = $me");
        }
    }
}
