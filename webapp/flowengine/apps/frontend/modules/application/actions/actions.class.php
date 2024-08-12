<?php

/**
 * Application actions.
 *
 * Displays applications submitted by the client
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use Dompdf\Dompdf;

class applicationActions extends sfActions
{
    /**
     * Executes 'View' action
     *
     * Displays full application details
     *
     * @param sfRequest $request A request object
     */
    public function executeView(sfWebRequest $request)
    {
        //Check JSON. Generate if empty
        $application_manager = new ApplicationManager();
        $application_manager->check_json($request->getParameter('id'));

        //Fetch application using id and user_id of currently logged in user
        if ($request->getParameter("profile")) {
            $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.id = ?', $request->getParameter("id"))
                ->andWhere('a.business_id = ?', $request->getParameter("profile"));
            $this->application = $q->fetchOne();
        } else {
            $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.id = ?', $request->getParameter("id"))
                ->andWhere('a.user_id = ?', $this->getUser()->getGuardUser()->getId());
            $this->application = $q->fetchOne();
        }

        //$application_manager = new ApplicationManager();
        $application_manager->update_services($this->application->getId());

        //Check if any pending invoices have already been paid using remote reconcile if possible
        $invoice_manager = new InvoiceManager();
        $invoice_manager->update_invoices($this->application->getId());

        $_SESSION['just_submitted'] = false;

        //If page does not exist then redirect to 404
        if (empty($this->application)) {
            return $this->redirect("/index.php//errors/notfound");
        }

        $this->getUser()->setAttribute("checkout", false);

        $this->getUser()->setAttribute('form_id', $this->application->getFormId());
        $this->getUser()->setAttribute('entry_id', $this->application->getEntryId());

        //Mark all the messages as read
        if ($request->getParameter("messages") == "read") {
            $this->open = "messages";

            $q = Doctrine_Query::create()
                ->from("Communications a")
                ->Where('a.messageread = ?', '0')
                ->andWhere('a.application_id = ?', $this->application->getId());
            $messages = $q->execute();
            foreach ($messages as $message) {
                if ($message->getArchitectId() == "") {
                    $message->setMessageread("1");
                    $message->save();
                }
            }
        }

        $q = Doctrine_Query::create()
            ->from("SavedPermit a")
            ->where("a.application_id = ?", $this->application->getId())
            ->andWhere("a.expiry_trigger = 0")
            ->orderBy("a.id ASC");
        $this->saved_permits = $q->execute();

        $q = Doctrine_Query::create()
            ->from('Communications a')
            ->where('a.application_id = ?', $this->application->getId())
            ->orderBy('a.id ASC');
        $this->communications = $q->execute();

        //If done parameter is set then set done variable
        $this->done = $request->getParameter("done", 0);

        //OTB Fix Show linkto if a user is permitted to access a particular form 
        $this->form_link_btns = '';
        $user_registered_as = Doctrine_Query::create()
            ->from('sfGuardUserProfile u')
            ->where('u.user_id = ?', $this->getUser()->getGuardUser()->getId());
        $user_registered_as_res = $user_registered_as->fetchOne();
        //if we have something
        if ($user_registered_as_res) {
            $q = Doctrine_Query::create()
                ->select('f.formid')
                ->from('SfGuardUserCategoriesForms f')
                ->where('f.categoryid = ? and f.formid <> ?', array($user_registered_as_res->getRegisteras(), $this->application->getFormId()));
            $cat_forms = $q->fetchArray();
            $q = Doctrine_Query::create()
                ->from('ApForms f')
                ->where('f.form_stage =? and f.form_active =? and f.form_type =?', array($this->application->getApproved(), 1, 1))
                ->andWhereIn('f.form_id', array_column($cat_forms, 'formid'));
            $this->forms_link = $q->execute();
        }
        //get revison
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.parent_submission = ?', $this->application->getId())
            ->orderBy('a.id ASC');
        $this->revisions = $q->execute();
        //Set layout
        //$this->setLayout("layoutdash");
        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'Viewentrypdf' action
     *
     * Generate PDF for form details
     *
     * @param sfRequest $request A request object
     */
    public function executeViewentrypdf(sfWebRequest $request)
    {
        $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
        require ($prefix_folder . 'includes/init.php');

        require ($prefix_folder . '../../../config/form_builder_config.php');
        require ($prefix_folder . 'includes/db-core.php');
        require ($prefix_folder . 'includes/helper-functions.php');
        require ($prefix_folder . 'includes/check-session.php');

        require ($prefix_folder . 'includes/language.php');
        require ($prefix_folder . 'includes/entry-functions.php');
        require ($prefix_folder . 'includes/post-functions.php');
        require ($prefix_folder . 'includes/users-functions.php');
        //require($prefix_folder.'lib/dompdf/dompdf_config.inc.php');

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $request->getParameter("id"))
            ->andWhere('a.user_id = ?', $this->getUser()->getGuardUser()->getId());
        $application = $q->fetchOne();

        $form_id = (int) $application->getFormId();
        $entry_id = (int) $application->getEntryId();

        if (empty($form_id) || empty($entry_id)) {
            die("Invalid Request");
        }

        $dbh = mf_connect_db();
        $mf_settings = mf_get_settings($dbh);

        //check permission, is the user allowed to access this page?
        if (empty($_SESSION['mf_user_privileges']['priv_administer'])) {
            $user_perms = mf_get_user_permissions($dbh, $form_id, $_SESSION['mf_user_id']);

            //this page need edit_entries or view_entries permission
            if (empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])) {
                $_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

                $ssl_suffix = mf_get_ssl_suffix();
                header("Location: http{$ssl_suffix}://" . $_SERVER['HTTP_HOST'] . mf_get_dirname($_SERVER['PHP_SELF']) . "/restricted.php");
                exit;
            }
        }

        $template_data_options = array();

        $template_data_options['as_plain_text'] = false;
        $template_data_options['target_is_admin'] = true;
        $template_data_options['machform_path'] = $mf_settings['base_url'];
        $template_data_options['show_image_preview'] = true;
        $template_data_options['use_list_layout'] = true;

        $template_data = mf_get_template_variables($dbh, $form_id, $entry_id, $template_data_options);

        $template_variables = $template_data['variables'];
        $template_values = $template_data['values'];

        $pdf_content = '<html><body>{entry_data}</body></html>';

        //parse pdf template
        $pdf_content = str_replace($template_variables, $template_values, $pdf_content);

        //generate PDF file
        $dompdf = new Dompdf();

        //paper size: letter, legal, ledger, tabloid, executive, folio, a0, a1, a2, a3, a4,a5, a6, etc
        //orientation: portrait, landscape
        $dompdf->setPaper('letter', 'portrait');

        $dompdf->loadHtml($pdf_content);
        $dompdf->render();
        $dompdf->stream("Entry #{$entry_id} - Form #{$form_id}.pdf");

        exit;
    }

    /**
     * Executes 'Canceltransfer' action
     *
     * Cancel Change of Ownership of an Application
     *
     * @param sfRequest $request A request object
     */
    public function executeCanceltransfer(sfWebRequest $request)
    {
        $data = $request->getParameter('code');
        $data = json_decode(base64_decode($data), true);

        $application_id = $data['id'];

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id);
        $this->application = $q->fetchOne();

        $this->application->setCirculationId("");
        $this->application->save();

        $this->getUser()->setFlash('notice', 'The request for transfer of ownership has been cancelled');
        return $this->redirect("/index.php//dashboard");
    }


    /**
     * Executes 'Accepttransfer' action
     *
     * Cancel Change of Ownership of an Application
     *
     * @param sfRequest $request A request object
     */
    public function executeAccepttransfer(sfWebRequest $request)
    {
        $data = $request->getParameter('code');
        $data = json_decode(base64_decode($data), true);

        $application_id = $data['id'];

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $application_id);
        $this->application = $q->fetchOne();

        $previous_owner = $this->application->getCirculationId();

        $this->application->setUserId($this->application->getCirculationId());
        $this->application->setCirculationId("");
        $this->application->save();

        $q = Doctrine_Query::create()
            ->from("SfGuardUserProfile a")
            ->where("a.user_id = ?", $this->application->getUserId());

        $new_user = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("SfGuardUserProfile a")
            ->where("a.user_id = ?", $previous_owner);

        $previous_user = $q->fetchOne();

        //Send email to new owner to alert them
        //Send account recovery email
        $body = "
                Hi {$new_user->getFullname()}, <br>
                <br>
                Application '" . $this->application->getApplicationId() . "' has been transferred to your account from '" . $previous_user->getFullname() . "'. <br>
                <br>
                Click here to view the application details:<br>
                <a href='http://" . $_SERVER['HTTP_HOST'] . "/index.php//application/view/id/" . $this->application->getId() . "'>" . $this->application->getApplicationId() . "</a>
                <br>
                <br>
                Thanks,<br>
                " . sfConfig::get('app_organisation_name') . ".<br>
            ";

        $mailnotifications = new mailnotifications();
        $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $new_user->getEmail(), "Application Transfer", $body);


        $this->getUser()->setFlash('notice', 'The request for transfer of ownership has been accepted');
        return $this->redirect("/index.php//dashboard");
    }

    /**
     * Executes 'Edit' action
     *
     * Allows client and resubmit an application
     *
     * @param sfRequest $request A request object
     */
    public function executeEdit(sfWebRequest $request)
    {
        //Fetch application using id and user_id of currently logged in user
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $request->getParameter("id"))
            ->andWhere('a.user_id = ?', $this->getUser()->getGuardUser()->getId());
        $this->application = $q->fetchOne();

        //If page does not exist then redirect to 404
        if (empty($this->application)) {
            return $this->redirect("/index.php//errors/notfound");
        }

        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
            ->andWhere("a.approved <> ?", 0)
            ->where('a.id = ?', $request->getParameter("id"))
            ->andWhere('a.parent_submission = ? and a.deleted_status = ?', [0, 0])
            ->andWhere("a.declined = 1")
            ->limit(1);
        $this->corrections_applications = $q->execute();

        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->leftJoin("a.MfInvoice b")
            ->where("a.user_id = ?", $this->getUser()->getGuardUser()->getId())
            ->andWhere("a.approved <> ?", 0)
            ->where('a.id = ?', $request->getParameter("id"))
            ->andWhere('a.parent_submission = ? and a.deleted_status = ?', [0, 0])
            ->andWhere("b.paid = 1")
            ->orderBy("a.id DESC");
        $this->renewal_applications = $q->execute();

        $q = Doctrine_Query::create()
            ->from("FormEntry a")
            ->where("a.circulation_id = ?", $this->getUser()->getGuardUser()->getId())
            ->where('a.id = ?', $request->getParameter("id"))
            ->limit(2);
        $this->transferring_applications = $q->execute();

        $this->setLayout("layoutmentordash");
    }

    /**
     * Executes 'Viewpermit' action
     *
     * Displays the generated permit that is attached to an application
     *
     * @param sfRequest $request A request object
     */
    public function executeViewpermit(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $request->getParameter('id'));
        $savedpermit = $q->fetchOne();

        if ($savedpermit) {
            $application = $savedpermit->getApplication();

            #require_once(dirname(__FILE__)."/../../../../../lib/vendor/dompdf/dompdf_config.inc.php");


            $html = "<html>
        <body>
        ";

            $templateparser = new TemplateParser();

            $html .= $templateparser->parsePermit($application->getId(), $application->getFormId(), $application->getEntryId(), $savedpermit->getId(), $savedpermit->getPermit());

            $html .= "
        </body>
        </html>";

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream($application->getApplicationId() . ".pdf");
        } else {
            echo "Invalid Permit Link";
        }

        exit;
    }
    /* Executes 'Share' action
     *
     * Allows the client to share selected application with another client
     *
     * @param sfRequest $request A request object
     */
    public function executeShare(sfWebRequest $request)
    {

        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->application = $q->fetchOne();
        //Check if stage is set for share
        $otbhelper = new OTBHelper();
        $this->forward404Unless($otbhelper->isSharedStage($this->application->getApproved()), 'Can\'t share application! Application needs to be on a allowed share stage');

        if ($request->getParameter("filter")) {
            $this->filter = trim($request->getParameter("filter"));
        }

        if ($request->getParameter("page")) {
            $this->page = $request->getParameter("page");
        }

        if ($request->getParameter("architect") && $request->getParameter("architect") != "") {
            $user_share = Doctrine_Core::getTable('sfGuardUserProfile')->findByUserId($request->getParameter("architect"));
            if ($otbhelper->checkifAppShared($request->getParameter("architect"), $request->getParameter("id")) == "shared") {
                sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
                $this->getUser()->setFlash('shared_error', $user_share);
            } else {
                //Link shared application
                $share = new FormEntryShares();
                $share->setSenderid($this->getUser()->getGuardUser()->getId());
                $share->setReceiverid($request->getParameter("architect"));
                $share->setFormentryid($request->getParameter("id"));
                $share->save();
                //Check if sub_menus is set to automatically send to stage
                if ($this->application->getStage()->getSharedStageMove() && $otbhelper->isSharedStage($this->application->getApproved())) {
                    //move application
                    error_log('------' . $this->application->getStage()->getSharedStage() . '----------');
                    $this->application->setApproved($this->application->getStage()->getSharedStage());
                    $this->application->save();
                }
                //Notifications
                $notify = new mailnotifications();
                $body = "<p>User " . $this->getUser() . " has shared application " . $this->application->getApplicationId() . " with you (" . $user_share[0]['fullname'] . " (" . $user_share[0]['email'] . ")).</p><p>Kindly login to " . sfConfig::get('app_organisation_name') . " " . sfConfig::get('app_organisation_description') . " to proceed";
                $notify->sendemail('', $user_share[0]['email'], 'Shared Application', $body);
                $this->redirect("/index.php//application/shared");
            }
        }
        $this->setLayout("layoutdash");
    }

    /* Executes 'Shared' action
     *
     * Shows success message if application is shared successfully
     *
     * @param sfRequest $request A request object
     */
    public function executeShared(sfWebRequest $request)
    {
        $this->setLayout("layoutdash");
    }
    /*
     * Search for application 
     * OTB patch
     */
    public function executeSearch(sfWebRequest $request)
    {
        $user = $this->getUser();
        //check if a user is logged in else redirect to login
        if ($user->isAuthenticated()) {
            $this->forwardUnless($query = $request->getParameter('query'), 'application', 'index');
            //lets override this query and pass and id to search for
            $q = Doctrine_Query::create()
                ->from('FormEntry f')
                ->where('f.application_id LIKE ?', "%" . $query . "%")
                ->andWhere('f.user_id = ? and f.deleted_status = ? and f.parent_submission = ?', [$this->getUser()->getGuardUser()->getId(), 0, 0]);

            $this->results = $q->execute();
            $this->setLayout("layoutdash");
        } else {
            //redirect to login.
            $this->redirect('@sf_guard_signin');
        }
    }
    //OTB ADD
    public function executeMessaging(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry f')
            ->where('f.id = ?', $request->getParameter('id'));
        $application = $q->fetchOne();

        $this->forward404Unless($request->isMethod('POST') && $application, sprintf('Either not a post or application %s doesn\'t exist', $request->getParameter('id')));

        if ($request->getPostParameter("txtmessage")) {
            $message = new Communications();
            $message->setArchitectId($this->getUser()->getGuardUser()->getId());
            $message->setMessageread("0");
            $message->setContent(trim($request->getPostParameter("txtmessage")));
            $message->setApplicationId($application->getId());
            $message->setActionTimestamp(date('c'));
            $message->save();

            $activity = new Activity();
            $activity->setUserId($this->getUser()->getGuardUser()->getId());
            $activity->setFormEntryId($application->getId());
            $activity->setAction("User sent a message");
            $activity->setActionTimestamp(date('Y-m-d'));
            $activity->save();

            echo json_encode(array('success' => true, 'message' => array('name' => $this->getUser()->getGuardUser()->getSfGuardUserProfile()->getFullname(), 'content' => trim($request->getPostParameter("txtmessage")), 'time' => $message->getActionTimestamp())));
        } else {
            echo json_encode(array('success' => false));
        }

        exit;
    }
    //OTB ADD
    public function executeSharemove(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('FormEntry a')
            ->where('a.id = ?', $request->getParameter("id"));
        $this->application = $q->fetchOne();
        //Check if stage is set for share
        $otbhelper = new OTBHelper();
        $this->forward404Unless($otbhelper->isSharedStage($this->application->getApproved()), 'Can\'t share application! Application needs to be on a allowed share stage');
        if ($otbhelper->checkifAppShared($request->getParameter("architect"), $request->getParameter("id")) == "shared") {
            if ($this->application->getStage()->getSharedStageMove() && $otbhelper->isSharedStage($this->application->getApproved())) {
                //move application
                $this->application->setApproved($this->application->getStage()->getSharedStage());
                $this->application->save();
            }
            $this->redirect("/index.php//application/shared");
        } else {
            $this->getUser()->setFlash('shared_error', htmlentities('<p>Application wasn\'t shared with the user!</p>'));
            $this->redirect("/index.php//application/share");
        }
    }
    //OTB END
}
