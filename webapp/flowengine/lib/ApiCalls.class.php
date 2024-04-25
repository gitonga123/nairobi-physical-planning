<?php

class ApiCalls
{

    protected $token;
    protected $expires;
    protected $merchant_username;
    protected $merchant_pwd;
    public function __construct()
    {
        $this->merchant_username = sfConfig::get('app_merchant_username');
        $this->merchant_pwd = sfConfig::get('app_merchant_pwd');
    }

    public function getApiTokenForRabbitMQCall()
    {
        $q = Doctrine_Query::create()
            ->from('ApiContent c')
            ->where('c.form_id = ? and c.api_use =?', array(0, 'login'));
        $api_content = $q->fetchOne();
        if ($api_content) {
            //if set login
            $stream = new Stream();
            $stream_response = $stream->sendRequest(
                array(
                    'url' => $api_content->getMerchant()->getLink() . $api_content->getRequestUrl(),
                    'multipart' => false, // Use this when uploading files
                    'method' => 'GET', // GET, POST, PUT, DELETE,
                    'ssl' => 'none',
                    'contentType' => 'json', // Whether to convert the response to an array using json_decode or not
                    'headers' => array(
                        "Authorization" => "Basic " . base64_encode($this->merchant_username . ":" . $this->merchant_pwd),
                    ),
                )
            );
            $this->token = $stream_response->content['data']['token'];
            $this->expires = $stream_response->content['data']['expires'];
            //save to session
            return $stream_response->content['data']['token'];
        }

        return false;

    }
    public function login()
    {
        //login
        $api_token = sfContext::getInstance()->getUser()->getAttribute('api_token', array());
        //Check if set or expired
        if (!count($api_token) || strtotime($api_token['expires_at']) <= time()) {
            $q = Doctrine_Query::create()
                ->from('ApiContent c')
                ->where('c.form_id = ? and c.api_use =?', array(0, 'login'));
            $api_content = $q->fetchOne();
            if ($api_content) {
                error_log('---URL----' . $api_content->getMerchant()->getLink() . $api_content->getRequestUrl());
                //if set login
                $stream = new Stream();
                $stream_response = $stream->sendRequest(
                    array(
                        'url' => $api_content->getMerchant()->getLink() . $api_content->getRequestUrl(),
                        'multipart' => false, // Use this when uploading files
                        'method' => 'GET', // GET, POST, PUT, DELETE,
                        'ssl' => 'none',
                        'contentType' => 'json', // Whether to convert the response to an array using json_decode or not
                        'headers' => array(
                            "Authorization" => "Basic " . base64_encode($this->merchant_username . ":" . $this->merchant_pwd),
                        ),
                    )
                );
                $this->token = $stream_response->content['data']['token'];
                $this->expires = $stream_response->content['data']['expires'];
                //save to session
                sfContext::getInstance()->getUser()->setAttribute('api_token', array("token" => $this->token, "expires_at" => $this->expires));
			}
        } else {
            $this->token = $api_token['token'];
            $this->expires = $api_token['expires_at'];
		}
    }

    public function registerPlan($form_id, $submission)
    {
        //get form element owner type
        $q = Doctrine_Query::create()
            ->from('ApFormElements e')
            ->where('e.element_ownertype = ? and e.form_id = ? and e.element_status = ?', [1, $form_id, 1]);
        $element_ownertype = $q->fetchOne();
        //parse value
        $parsed_ownertype='';
        $templateparse = new Templateparser();
        if($element_ownertype)
        {
            $parsed_ownertype = $templateparse->parse($submission->getId(), $submission->getFormId(), $submission->getEntryId(), '{fm_element_' . $element_ownertype->getElementId() . '}');
        }
        error_log('-------parsed_ownertype----' . $parsed_ownertype);

        if (strlen($parsed_ownertype)) {
            $q = Doctrine_Query::create()
                ->from('ApiContent c')
                ->where('c.form_id = ? and c.api_use =? and c.api_use_diff =?', array($form_id, 'register', $parsed_ownertype));
            $api_content_register = $q->fetchOne();
        } else {
            $q = Doctrine_Query::create()
                ->from('ApiContent c')
                ->where('c.form_id = ? and c.api_use =?', array($form_id, 'register'));
            $api_content_register = $q->fetchOne();
        }
        if ($api_content_register) {
            //$this->login();
            //parse content
            $parsed_api_content = $templateparse->parse($submission->getId(), $submission->getFormId(), $submission->getEntryId(), $api_content_register->getContent());
            error_log('----API content----' . $parsed_api_content);
            $data_decode = json_decode($parsed_api_content, true);
            if (is_array($data_decode)) {
                // $stream = new Stream();
                // $stream_response=$stream->sendRequest(
                $api_content_zizi = [
                    "data" => array(
                        'url' => $api_content_register->getMerchant()->getLink() . $api_content_register->getRequestUrl(),
                        'multipart' => false, // Use this when uploading files
                        'method' => 'POST', // GET, POST, PUT, DELETE,
                        'ssl' => 'none', // Whether to convert the response to an array using json_decode or not
                        'contentType' => 'json',
                        'headers' => array(
                            //"Authorization" => "Bearer " . $this->token,
                            "Authorization" => "",

                        ),
                        'data' => $data_decode,
                    ),
                ];
                error_log('----------REGISTER PLAN--------');
                error_log('----------Sending to queue--------');
                $queue_manager = new QueueManager();
                $queue_manager->queue_data($api_content_zizi);
                error_log('----------Added  to the queue--------');

                //);

                // error_log(print_r($stream_response,true));
            }
        }
    }

    public function postInvoice($submission, $invoice, $just_submitted=false)
    {
        $q = Doctrine_Query::create()
            ->from('ApiContent c')
            ->where('c.form_id =? and c.api_use =?', array($submission->getFormId(), 'post_invoice'));
        $api_content = $q->fetchOne();
        if ($api_content) {
            error_log('----URL----' . $api_content->getMerchant()->getLink() . $api_content->getRequestUrl());
            //$this->login();
            //parse content
            $templateparse = new Templateparser();
            $parsed_api_content = html_entity_decode($templateparse->parseInvoice($submission->getId(), $submission->getFormId(), $submission->getEntryId(), $invoice->getId(), $api_content->getContent()));
            error_log('----API content----' . $parsed_api_content);
            $data_decode = json_decode($parsed_api_content, true);
            if (is_array($data_decode)) {
                //for application fee payment
                if($just_submitted){
                    $data_decode['invoiceType'] = 'application';
                }
                // $stream = new Stream();
                // $stream_response=$stream->sendRequest(
                $api_content_zizi = [
                    "data" => array(
                        'url' => $api_content->getMerchant()->getLink() . $api_content->getRequestUrl(),
                        'multipart' => false, // Use this when uploading files
                        'method' => 'POST', // GET, POST, PUT, DELETE,
                        'ssl' => 'none', // Whether to convert the response to an array using json_decode or not
                        'contentType' => 'json',
                        'headers' => array(
                            //"Authorization" => "Bearer " . $this->token,
                            "Authorization" => "",
                        ),
                        'data' => $data_decode,
                    ),
                ];
                // );
                error_log('----------SEND INVOICE--------');
                error_log('----------Sending to queue--------');
                $queue_manager = new QueueManager();
                $queue_manager->queue_data($api_content_zizi);
                error_log('----------SENT TO QUEUE PLAN--------');
            }
        }
    }

    public function cancelInvoice($invoice)
    {
        $q = Doctrine_Query::create()
            ->from('ApiContent c')
            ->where('c.form_id =? and c.api_use =?', array($invoice->getFormEntry()->getFormId(), 'cancel_invoice'));
        $api_content = $q->fetchOne();
        if ($api_content) {
            error_log('----URL----' . $api_content->getMerchant()->getLink() . $api_content->getRequestUrl());
            $this->login();
            //parse content
            $templateparse = new Templateparser();
            $parsed_api_content = html_entity_decode($templateparse->parseInvoice($invoice->getFormEntry()->getId(), $invoice->getFormEntry()->getFormId(), $invoice->getFormEntry()->getEntryId(), $invoice->getId(), $api_content->getContent()));
            error_log('----API content----' . $parsed_api_content);
            $data_decode = json_decode($parsed_api_content, true);
            if (is_array($data_decode)) {
                $stream = new Stream();
                $stream_response = $stream->sendRequest(
                    array(
                        'url' => $api_content->getMerchant()->getLink() . $api_content->getRequestUrl(),
                        'multipart' => false, // Use this when uploading files
                        'method' => 'POST', // GET, POST, PUT, DELETE,
                        'ssl' => 'none', // Whether to convert the response to an array using json_decode or not
                        'contentType' => 'json',
                        'headers' => array(
                            "Authorization" => "Bearer " . $this->token,
                        ),
                        'data' => $data_decode,
                    )
                );
                error_log(print_r($stream_response, true));
            }
        }
    }

    public function getZones($county_code)
    {
        //GET
        $q = Doctrine_Query::create()
            ->from('ApiContent c')
            ->where('c.form_id =? and c.api_use =?', array(0, 'get_zone'));
        $api_content = $q->fetchOne();
        if ($api_content) {
            error_log('----URL----' . $api_content->getMerchant()->getLink() . $api_content->getRequestUrl());
            $this->login();
            $stream = new Stream();
            $stream_response = $stream->sendRequest(
                array(
                    'url' => $api_content->getMerchant()->getLink() . $api_content->getRequestUrl(),
                    'multipart' => false, // Use this when uploading files
                    'method' => 'GET', // GET, POST, PUT, DELETE,
                    'ssl' => 'none', // Whether to convert the response to an array using json_decode or not
                    'contentType' => 'default',
                    'headers' => array(
                        "Authorization" => "Bearer " . $this->token,
                    ),
                    'data' => ['countyCode' => $county_code],
                )
            );
            error_log(print_r($stream_response, true));
            return $stream_response->content['data']['zones'];
        }
    }
    public function getServices($county_code, $zone)
    {
        //GET
        $q = Doctrine_Query::create()
            ->from('ApiContent c')
            ->where('c.form_id =? and c.api_use =?', array(0, 'get_services'));
        $api_content = $q->fetchOne();
        if ($api_content) {
            error_log('----URL----' . $api_content->getMerchant()->getLink() . $api_content->getRequestUrl());
            $this->login();
            $stream = new Stream();
            $stream_response = $stream->sendRequest(
                array(
                    'url' => $api_content->getMerchant()->getLink() . $api_content->getRequestUrl(),
                    'multipart' => false, // Use this when uploading files
                    'method' => 'GET', // GET, POST, PUT, DELETE,
                    'ssl' => 'none', // Whether to convert the response to an array using json_decode or not
                    'contentType' => 'default',
                    'headers' => array(
                        "Authorization" => "Bearer " . $this->token,
                    ),
                    'data' => ['countyCode' => $county_code, 'zoneId' => $zone],
                )
            );
            error_log(print_r($stream_response, true));
            return $stream_response->content['data']['services'];
        }
    }
}
