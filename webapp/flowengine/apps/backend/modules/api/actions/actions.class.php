<?php

/**
 * api actions.
 *
 * @package    symfony
 * @subpackage API
 * @author     OTB AFRICA
 * @version    SVN: $Id: actions.class.php 23810 2023-08-29 15:46:44Z 
 */
class apiActions extends sfActions
{
    public function executeIndex(sfWebRequest $request)
    {
        return $this->json(['success' => true, 'message' => 'api endpoints']);
    }
    public function executeInvoices(sfWebRequest $request)
    {
        $application_manager = new ApplicationManager();
        $q = Doctrine_Query::create()
            ->from('MfInvoice m')
            ->leftJoin('m.MfInvoiceDetail d')
            ->andWhere("m.invoice_number = ?", $request->getParameter('reference'))
            ->orderBy('m.id DESC');

        $invoice = $q->fetchOne();

        if (!$invoice) {
            return $this->json(['success' => false, 'data' => '']);
        }


        $invoice_details = [];

        $invoice_details['id'] = $invoice->getId();
        $invoice_details['paid'] = $invoice->getStatus();
        $invoice_details['created_at'] = $invoice->getCreatedAt();
        $invoice_details['invoice_number'] = $invoice->getInvoiceNumber();
        $invoice_details['expires_at'] = $invoice->getExpiresAt();
        $invoice_details['amount'] = $invoice->getTotalAmount();
        $invoice_details['currency'] = $invoice->getCurrency();
        $invoice_details['transaction_id'] = $invoice->getTransactionId();
        $application = $invoice->getFormEntry();
        $applications = $application_manager->getExtraApplicationInfo($application->getFormId(), $application->getEntryId());
        $invoice_details['plot_no'] = $applications[0];
        $invoice_details['owner_name'] = $applications[1];
        $invoice_details['fees'] = [];

        foreach ($invoice->getMfInvoiceDetail() as $fee) {
            if ($fee->amount > 0) {
                array_push($invoice_details['fees'], ['id' => $fee->getId(), 'description' => $fee->getDescription(), 'amount' => $fee->getAmount()]);
            }
        }

        return $this->json(['success' => true, 'data' => $invoice_details]);
    }

    public function executePlots(sfWebRequest $request)
    {
        $page = $request->getParameter('page');
        $limit = $request->getParameter('limit');

        $limit = is_null($limit) ? 10 : intval($limit);

        $q_app = Doctrine_Query::create()->from("Plot p")->limit($limit);

        if (!is_null($page)) {
            $from = is_null($limit) ? 10 : intval($page) * $limit;
            $q_app->offset($from);
        }

        $q_app->orderBy('p.id asc');

        $total_count = $q_app->count();

        $page = is_null($page) ? 1 : intval($page);

        $last = ceil($total_count / $limit);
        $next = $last == $page ? $last : $page + 1;

        $param_array = [
            'limit' => $limit,
            'page' => $next
        ];

        $app_results = $q_app->execute();

        $app_array = [];

        $temp = [];
        foreach ($app_results as $result) {

            $temp['id'] = $result->id;
            $temp['plot_no'] = $result->plot_no;
            $temp['plot_type'] = $result->plot_type;
            $temp['plot_status'] = $result->plot_status;
            $temp['plot_size'] = $result->plot_size;
            $temp['plot_lat'] = $result->plot_lat;
            $temp['plot_long'] = $result->plot_long;
            $temp['plot_location'] = $result->plot_location;
            $temp['plot_comments'] = $result->plot_comments;
            $temp['owner_name'] = $result->owner_name;
            $temp['owner_phone'] = $result->owner_phone;
            $temp['physical_address'] = $result->physical_address;
            $temp['block_number'] = $result->block_number;
            $temp['ward'] = $result->ward;
            $temp['property_usage'] = $result->property_usage;
            $temp['plot_size_ha'] = $result->plot_size_ha;
            $temp['upn'] = $result->upn;
            $temp['parent_upn'] = $result->parent_upn;
            $temp['measurements'] = $result->measurements;
            $temp['po_box'] = $result->po_box;
            $temp['postal_code'] = $result->postal_code;
            $temp['email'] = $result->email;
            $temp['amount_land_rates'] = $result->amount_land_rates;
            $temp['town'] = $result->town;
            $temp['customer_supplier_id'] = $result->customer_supplier_id;
            array_push($app_array, $temp);
            $temp = [];
        }

        $new_param_array = array_filter($param_array);
        $query_param_array = http_build_query($new_param_array);

        $param_array['page'] = null;
        $new_param_array = array_filter($param_array);
        $f_param_array = http_build_query($new_param_array);

        $param_array['page'] = $last;
        $new_param_array = array_filter($param_array);
        $l_param_array = http_build_query($new_param_array);

        return $this->renderText(json_encode([
            'success' => true,
            'data' => $app_array,
            "links" => [
                "first" => "/backend.php/api/plots?" . $f_param_array,
                "next" => "/backend.php/api/plots?" . $query_param_array,
                "last" => "/backend.php/api/plots?" . $l_param_array
            ],
            "meta" => [
                "currentPage" => $page,
                "itemCount" => $limit > $total_count ? $total_count : $limit,
                "totalItems" => $total_count,
                "totalPages" => $last
            ]
        ]));
    }


    public function executeProcessPayments(sfWebRequest $request)
    {
        $response = $request->getContent();
        $response = json_decode($response, true);

        error_log(print_r($response, true));

        if (strtolower($response['status']) == 'success') {
            $q = Doctrine_Query::create()
                ->from("ApFormPayments a")
                ->where("a.payment_id = ?", $response['bill_number'])
                ->where("a.narration = ?", $response['ref'])
                ->orderBy('a.afp_id desc');
            $transaction = $q->fetchOne();

            error_log($transaction);

            if ($transaction) {
                $transaction->setPaymentTestMode($response['mode_of_payment']);

                $transaction->setPaymentStatus('paid');
                $transaction->setStatus(2);

                $transaction->save();


                $q = Doctrine_Query::create()
                    ->from('MfInvoice m')
                    ->where('m.id = ?', $transaction->getInvoiceId());

                $invoice = $q->fetchOne();

                $invoice->setPaid(2);

                $invoice->save();

                return $this->json(['data' => ['msg' => 'paid', 'payload' => $response]]);
            } else {
                return $this->json(['data' => ['msg' => 'Bill Reference not found.', 'payload' => $response]], 404);
            }
        } else {
            return $this->json(['data' => ['msg' => 'Something went Wrong.', 'payload' => $response]], 500);
        }
    }
    // public function executeValidateplotdetails(sfWebRequest $request)
    // {
    //     // Retrieve parameters
    //     $block_number = trim($request->getParameter('block_number'));
    //     $plot_number = trim($request->getParameter('plot_no'));

    //     error_log("\n\n");

    //     // Initialize the stream and URL
    //     $stream = new Stream();
    //     $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/land/bill/land_payment_status/';

    //     // Store block number in session if provided
    //     if ($block_number) {
    //         $_SESSION['block_number'] = $block_number;
    //     }

    //     error_log("Block number --->" . (isset($_SESSION['block_number']) ? $_SESSION['block_number'] : 'Not set'));

    //     // Store plot number in session if provided
    //     if ($plot_number) {
    //         $_SESSION['plot_number'] = $plot_number;
    //         error_log("Plot number set, returning true.");
    //         return $this->renderText(true);
    //     }

    //     // Validate both block number and plot number are set in session
    //     if (!isset($_SESSION['block_number']) || !isset($_SESSION['plot_number'])) {
    //         error_log("Block number or Plot number not set, returning false.");
    //         return $this->renderText(false);
    //     }

    //     error_log("Plot number --->" . $_SESSION['plot_number']);
    //     error_log("Found both block number and plot number, proceeding...");

    //     // Set the JWT token if not already set
    //     if (!isset($_SESSION['jambo_token'])) {
    //         $_SESSION['jambo_token'] = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoyMTQsImlzX2FjdGl2ZSI6dHJ1ZSwidXNlcm5hbWUiOiIyNTQ3MTA1OTQyOTgiLCJmaXJzdF9uYW1lIjoiREFOSUVMIiwibGFzdF9uYW1lIjoiTVVUV0lSSSIsImV4cCI6MTcxNzY1MjU4OCwicGVybWlzc2lvbnMiOnsiYWNjZXNzX3NlbGZfc2VydmljZV9wb3J0YWwiOnRydWUsImNyZWF0ZV9iaWxsIjp0cnVlLCJyZWdpc3Rlcl9idXNpbmVzcyI6dHJ1ZSwicmVxdWVzdF9pbnNwZWN0aW9uIjp0cnVlLCJyZXF1ZXN0X2xpY2Vuc2UiOnRydWUsImxvZ19wYXltZW50Ijp0cnVlLCJhY2Nlc3NfYWRtaW4iOmZhbHNlLCJ2aWV3X2Rhc2hib2FyZCI6ZmFsc2V9LCJyb2xlcyI6WyJjaXRpemVuIl0sInJldmVudWVfc3RyZWFtX3JvbGVzIjp7fSwiY3VzdG9tZXIiOiI3NWY5NzA5NS00ZTkzLTQ0OGMtOTliZS00YTYwNmFhN2JkNzEiLCJpZF9ubyI6IjMwMTE1ODM1IiwiZW1haWwiOiJtdXR3aXJpZGFuaWVsc2NpQGdtYWlsLmNvbSIsInBob25lIjoiMjU0NzEwNTk0Mjk4In0.o-l-orFsrCuGHYYqmPYGkjnj-NuAduj6rjdsLxUPphc";
    //     }

    //     // Send request to the API
    //     $query_response = $stream->sendRequest([
    //         'url' => $url,
    //         'method' => 'GET',
    //         'ssl' => 'none',
    //         'contentType' => 'json',
    //         'headers' => [
    //             "Authorization" => "JWT " . $_SESSION['jambo_token'],
    //         ],
    //         'data' => [
    //             'plot_number' => $_SESSION['plot_number'],
    //             'block_number' => $_SESSION['block_number']
    //         ]
    //     ]);

    //     // Log response status
    //     error_log("Response status code is ----> {$query_response->status}");
    //     $content = json_decode($query_response->content, true);

    //     if ($query_response->status == 200 || $query_response->status == 201) {
    //         error_log("Content received, proceeding...");
    //         error_log("<pre>" . print_r($content, true) . "</pre>");

    //         if (isset($content['upto_date']) && $content['upto_date']) {
    //             error_log("Payments are up to date.");
    //             return $this->renderText(true);
    //         } else {
    //             error_log("Payments are not up to date.");
    //             return $this->renderText(false);
    //         }
    //     } else {
    //         error_log("Failed response with status code: " . $query_response->status);
    //         return $this->renderText(false);
    //     }
    // }

    public function executeValidateplotdetails(sfWebRequest $request)
    {
        $block_number = trim($request->getParameter('block_number'));
        $plot_number = trim($request->getParameter('plot_no'));

        error_log("\n\n");

        $stream = new Stream();
        $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/land/bill/land_payment_status/';

        // Initialize cache
        $cache = new sfFileCache([
            'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
        ]);

        // Store block_number in cache
        if ($block_number) {
            $cache->set('block_number', $block_number, 3600); // expires in 1 hour
            error_log("Block number set in cache --->" . $block_number);
        }

        // Store plot_number in cache
        if ($plot_number) {
            $cache->set('plot_number', $plot_number, 3600); // expires in 1 hour
            error_log("Plot number set in cache --->" . $plot_number);
            return $this->json(['success' => true, 'value' => true, 'message' => '']);
            // return $this->renderText(json_encode(['success' => true, 'value' => true, 'message' => '']));
        }

        // Retrieve block_number and plot_number from cache
        $block_number = $cache->get('block_number');
        $plot_number = $cache->get('plot_number');

        error_log("Block number from cache --->" . $block_number);
        error_log("Plot number from cache --->" . $plot_number);

        if (empty($block_number)) {
            error_log("Block number not set in cache, returning false.");
            return $this->json(['success' => false, 'value' => false, 'message' => 'Something Went Wrong!. Try Again later.']);
        }

        if (empty($plot_number)) {
            error_log("Plot number not set in cache, returning false.");
            return $this->json(['success' => false, 'value' => false, 'message' => 'Something Went Wrong!. Try Again later.']);
        }

        $username = $request->getParameter('username');

        $token = $cache->get("jambo_token_{$username}");


        error_log("Token is this --->" . $token);


        // $url .= "?plot_number={$plot_number}&block_number={$block_number}";

        error_log("URL many years ago ----> {$url}");
        $query_response = $stream->sendRequest([
            'url' => $url,
            'method' => 'GET',
            'ssl' => 'none',
            'contentType' => 'json',
            'headers' => [
                "Authorization" => "JWT " . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'data' => [
                'plot_number' => $plot_number,
                'block_number' => $block_number
            ]
        ]);

        error_log(print_r($query_response, true));
        error_log("Response status code is ----> {$query_response->status}");
        $content = $query_response->content;
        if ($query_response->status == 200 || $query_response->status == 201) {
            error_log("Content received, proceeding...");
            error_log($content);

            if (isset($content['upto_date']) && $content['upto_date']) {
                error_log("Payments are up to date.");
                return $this->json(['success' => true, 'value' => true, 'message' => '<p style="font-size:12px; color: #df0000;">' . $content['message'] . " Balance: KES" . $content['balance'] . '</p>']);
            } else {
                error_log("Payments are not up to date.");
                return $this->json(['success' => false, 'value' => false, 'message' => '<p style="font-size:12px; color: #df0000;">' . $content['message'] . " Balance: KES" . $content['balance'] . '</p>']);
            }
        } else {
            error_log("Failed response with status code: " . $query_response->status);
            echo false;
            return $this->json(['success' => false, 'value' => false, 'message' => '<p style="font-size:12px; color: #df0000;">' . $content['message'] . " Balance: KES" . $content['balance'] . '</p>']);
        }
    }

    // public function executeValidateplotdetails(sfWebRequest $request)
    // {
    //     $block_number = trim($request->getParameter('block_number'));

    //     $plot_number = trim($request->getParameter('plot_no'));

    //     error_log("\n\n");

    //     $stream = new Stream();
    //     $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/land/bill/land_payment_status/';
    //     if ($block_number) {
    //         $_SESSION['block_number'] = $block_number;
    //     }

    //     error_log("Block number --->" . $_SESSION['block_number']);

    //     if ($plot_number) {
    //         $_SESSION['plot_number'] = $plot_number;

    //         return $this->renderText(true);
    //     }


    //     if (!isset($_SESSION['block_number'])) {
    //         return $this->renderText(false);
    //     }

    //     if (!isset($_SESSION['plot_number'])) {
    //         return $this->renderText(false);
    //     }


    //     error_log("Plot number --->" . $_SESSION['plot_number']);

    //     error_log("Found both so we continue --->");

    //     if (!isset($_SESSION['jambo_token'])) {
    //         $_SESSION['jambo_token'] = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoyMTQsImlzX2FjdGl2ZSI6dHJ1ZSwidXNlcm5hbWUiOiIyNTQ3MTA1OTQyOTgiLCJmaXJzdF9uYW1lIjoiREFOSUVMIiwibGFzdF9uYW1lIjoiTVVUV0lSSSIsImV4cCI6MTcxNzY1MjU4OCwicGVybWlzc2lvbnMiOnsiYWNjZXNzX3NlbGZfc2VydmljZV9wb3J0YWwiOnRydWUsImNyZWF0ZV9iaWxsIjp0cnVlLCJyZWdpc3Rlcl9idXNpbmVzcyI6dHJ1ZSwicmVxdWVzdF9pbnNwZWN0aW9uIjp0cnVlLCJyZXF1ZXN0X2xpY2Vuc2UiOnRydWUsImxvZ19wYXltZW50Ijp0cnVlLCJhY2Nlc3NfYWRtaW4iOmZhbHNlLCJ2aWV3X2Rhc2hib2FyZCI6ZmFsc2V9LCJyb2xlcyI6WyJjaXRpemVuIl0sInJldmVudWVfc3RyZWFtX3JvbGVzIjp7fSwiY3VzdG9tZXIiOiI3NWY5NzA5NS00ZTkzLTQ0OGMtOTliZS00YTYwNmFhN2JkNzEiLCJpZF9ubyI6IjMwMTE1ODM1IiwiZW1haWwiOiJtdXR3aXJpZGFuaWVsc2NpQGdtYWlsLmNvbSIsInBob25lIjoiMjU0NzEwNTk0Mjk4In0.o-l-orFsrCuGHYYqmPYGkjnj-NuAduj6rjdsLxUPphc";
    //     }


    //     $query_response = $stream->sendRequest([
    //         'url' => $url,
    //         'method' => 'GET',
    //         'ssl' => 'none',
    //         'contentType' => 'json',
    //         'headers' => [
    //             "Authorization" => "JWT " . $_SESSION['jambo_token'],
    //         ],
    //         'data' => [
    //             'plot_number' => $_SESSION['plot_number'],
    //             'block_number' => $_SESSION['block_number']
    //         ]
    //     ]);

    //     error_log("Response status code is ----> {$query_response->status}");
    //     $content = $query_response->content;

    //     if ($query_response->status == 200 || $query_response->status == 201) {

    //         error_log("Content is below so we continue ---->");

    //         error_log("<pre>" . print_r($content, true) . "</pre>");

    //         error_log("\n\n");

    //         if ($content['upto_date']) {
    //             return $this->renderText(true);
    //         } else {
    //             return $this->renderText(false);
    //         }
    //     } else {
    //         error_log("Response cde is not 200 or 201");
    //         error_log("\n\n");
    //         return $this->renderText(false);
    //     }

    //     return sfView::NONE;
    // }

    private function json($content, $status = 200)
    {
        $this->getResponse()->setHttpHeader('Content-Type', 'application/json');
        $this->getResponse()->setContent(json_encode($content));
        $this->getResponse()->setStatusCode($status);
        return sfView::NONE;
    }
}
