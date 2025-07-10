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
                "first" => "/plan/api/plots?" . $f_param_array,
                "next" => "/plan/api/plots?" . $query_param_array,
                "last" => "/plan/api/plots?" . $l_param_array
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

        error_log(json_encode($response));

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
                if (array_key_exists('receipt_number', $response)) {
                    $invoice->setReceiptNumber(json_encode($response['receipt_number']));
                }
                $invoice->save();

                return $this->json(['data' => ['msg' => 'paid', 'payload' => $response]]);
            } else {
                return $this->json(['data' => ['msg' => 'Bill Reference not found.', 'payload' => $response]], 404);
            }
        } else {
            return $this->json(['data' => ['msg' => 'Something went Wrong.', 'payload' => $response]], 500);
        }
    }

    public function executeValidateplotdetails(sfWebRequest $request)
    {
        $block_number = trim($request->getParameter('block_number'));
        $plot_number = trim($request->getParameter('plot_no'));

        $username = $request->getParameter('username');

        error_log("Username is --->{$username}");

        $block_number_key = "block_number_{$username}";
        $plot_number_key = "plot_number_{$username}";

        error_log("\n\n");

        $stream = new Stream();
        $url = sfConfig::get('app_api_jambo_url') . 'api/v1/land/bill/land_payment_status_details/';


        // Initialize cache
        $cache = new sfFileCache([
            'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
        ]);
        error_log("Query url is this one ---->1 --->{$url}");

        // Store block_number in cache
        if ($block_number) {
            $cache->set($block_number_key, $block_number, 3600); // expires in 1 hour
            error_log("Block number set in cache ---> {$block_number}");
        }

        // Store plot_number in cache
        if ($plot_number) {
            $cache->set($plot_number_key, $plot_number, 3600); // expires in 1 hour
            error_log("Plot number set in cache ---> {$plot_number}");
            return $this->json(['success' => true, 'value' => true, 'message' => '']);
            // return $this->renderText(json_encode(['success' => true, 'value' => true, 'message' => '']));
        }

        // Retrieve block_number and plot_number from cache
        $block_number = $cache->get($block_number_key);
        $plot_number = $cache->get($plot_number_key);

        error_log("Block number from cache --->{$block_number}");
        error_log("Plot number from cache --->{$plot_number}");

        if (empty($block_number)) {
            error_log("Block number not set in cache, returning false.");
            return $this->json(['success' => false, 'value' => false, 'message' => 'Something Went Wrong!. Try Again later.']);
        }

        if (empty($plot_number)) {
            error_log("Plot number not set in cache, returning false.");
            return $this->json(['success' => false, 'value' => false, 'message' => 'Something Went Wrong!. Try Again later.']);
        }

        $token = $cache->get("jambo_token_{$username}");

        // $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoyMTQsImlzX2FjdGl2ZSI6dHJ1ZSwidXNlcm5hbWUiOiIyNTQ3MTA1OTQyOTgiLCJmaXJzdF9uYW1lIjoiREFOSUVMIiwibGFzdF9uYW1lIjoiTVVUV0lSSSIsImV4cCI6MTcyMDU5MzI1MiwicGVybWlzc2lvbnMiOnsiYWNjZXNzX3NlbGZfc2VydmljZV9wb3J0YWwiOnRydWUsImNyZWF0ZV9iaWxsIjp0cnVlLCJyZWdpc3Rlcl9idXNpbmVzcyI6dHJ1ZSwicmVxdWVzdF9pbnNwZWN0aW9uIjp0cnVlLCJyZXF1ZXN0X2xpY2Vuc2UiOnRydWUsImxvZ19wYXltZW50Ijp0cnVlLCJhY2Nlc3NfYWRtaW4iOmZhbHNlLCJ2aWV3X2Rhc2hib2FyZCI6ZmFsc2V9LCJyb2xlcyI6WyJjaXRpemVuIl0sInJldmVudWVfc3RyZWFtX3JvbGVzIjp7fSwiY3VzdG9tZXIiOiI3NWY5NzA5NS00ZTkzLTQ0OGMtOTliZS00YTYwNmFhN2JkNzEiLCJpZF9ubyI6IjMwMTE1ODM1IiwiZW1haWwiOiJtdXR3aXJpZGFuaWVsc2NpQGdtYWlsLmNvbSIsInBob25lIjoiMjU0NzEwNTk0Mjk4In0.j4Y627hV471zcXP6bFl6_LxD2kpEgvQjA6sRvm4QB9U";


        error_log("Token is this --->" . $token);

        error_log("Query url is this one ----> 2" . $url);

        // $url .= "?plot_number={$plot_number}&block_number={$block_number}";

        error_log("URL many years ago ----> {$url}");
        $query_response = $stream->sendRequest([
            'url' => $url,
            'method' => 'GET',
            'ssl' => 'none',
            'contentType' => 'json',
            'headers' => [
                "Authorization" => "JWT {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'data' => [
                'plot_number' => $plot_number,
                'block_number' => $block_number
            ]
        ]);


        error_log("Response status code is ----> {$query_response->status}");
        $content = $query_response->content;

        error_log("Content received is below for review ---->");
        error_log(json_encode($content));

        if ($query_response->status == 200 || $query_response->status == 201) {
            error_log("Content received, proceeding...");
            error_log($content);

            if (isset($content['upto_date']) && $content['upto_date']) {
                error_log("JSON Encode --->");
                error_log(json_encode($content['details']['clearance']));
                if (isset($content['details']['clearance']) && ($content['details']['clearance']['status'] == 'Active')) {
                    $cache->set("{$username}_{$block_number}_{$plot_number}", json_encode($content), 3600);

                    return $this->json(['success' => true, 'value' => true, 'message' => '<p style="font-size:12px; color: #df0000;">' . $content['message'] . " Balance: KES" . $content['balance'] . '</p>']);
                } else {
                    return $this->json(['success' => false, 'value' => false, 'message' => '<p style="font-size:12px; color: #df0000;"> Please visit the Uasin County Government offices to get a valid Clearance Certificate</p>']);
                }
            } else {
                return $this->json(['success' => false, 'value' => false, 'message' => '<p style="font-size:12px; color: #df0000;">' . $content['message'] . " Balance: KES" . $content['balance'] . '</p>']);
            }
        } else {
            error_log("Failed response with status code: {$query_response->status}");

            $message = '';

            if (isset($content['errors'])) {
                $message .= $content['errors'];
            } else {

                if (isset($content['errors'])) {
                    $message .= $content['errors'];
                } else {
                    $message .= $content['message'];
                }

                if (isset($content['balance'])) {
                    $message .= " Balance: KES {$content['balance']}";
                }

                if (strlen($message) < 2) {
                    $message = 'Something Went Wrong. Please try again later.';
                }
            }


            return $this->json(['success' => false, 'value' => false, 'message' => '<p style="font-size:12px; color: #df0000;">' . $message . '</p>']);

        }
    }

    public function executeCachedPlotDetails(sfWebRequest $request)
    {
        $cache = new sfFileCache([
            'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
        ]);

        $cached_key = trim($request->getParameter('key'));

        $cached_plot_details = $cache->get($cached_key);

        return $this->json(['success' => true, 'plot_details' => $cached_plot_details]);
    }

    private function json($content, $status = 200)
    {
        $this->getResponse()->setHttpHeader('Content-Type', 'application/json');
        $this->getResponse()->setContent(json_encode($content));
        $this->getResponse()->setStatusCode($status);
        return sfView::NONE;
    }

    private function permitType()
    {
        $groups = [
            25952 => "DEVELOPMENT PERMISSION BUILDING PLAN",
            47349 => "DEVELOPMENT PERMISSION PERIMETER WALL",
            67355 => "BUILDING PLANS APPLICATION RENEWAL",
            38732 => "DEMOLITION APPROVAL",
            46092 => "OUTDOOR ADVERTISING",
            25445 => "PLANNING APPLICATION",
            89966 => "HOARDING APPLICATION",
            88401 => "RENOVATION WORKS"
        ];

        return $groups;
    }

    public function executeApplicationTypes(sfWebRequest $request)
    {
        $groups = $this->permitType();
        $group_list = [];
        $temp = [];
        foreach ($groups as $group => $value) {
            $temp = [
                'id' => $group,
                'name' => $value
            ];
            array_push($group_list, $temp);
        }

        return $this->renderText(json_encode([
            'success' => true,
            'data' => $group_list,
            'itemCount' => count($group_list)
        ]));
    }

    private function getSubCounties()
    {
        //Get list of all objects
        $q = Doctrine_Query::create()
            ->from('Subcounty s')
            ->orderBy('s.id ASC');
        $sub_counties = $q->execute();
        $sub_counties_list = [];

        foreach ($sub_counties as $subcounty) {
            $temp = [
                'id' => $subcounty->getId(),
                'name' => $subcounty->getName()
            ];

            array_push($sub_counties_list, $temp);
            $temp = [];
        }



        return $sub_counties_list;
    }

    public function executeSubCounties(sfWebRequest $request)
    {
        $sub_counties = $this->getSubCounties();
        return $this->renderText(json_encode([
            'success' => true,
            'data' => $sub_counties,
            'itemCount' => count($sub_counties)
        ]));
    }

    public function executeSubCountiesView(sfWebRequest $request)
    {
        $id = $request->getParameter('id');

        $subcounty = Doctrine_Core::getTable('Subcounty')->find(array($id));

        if (!$subcounty) {
            return $this->renderText(json_encode([
                'success' => false,
                'message' => 'Subcounty not found.'
            ]));
        }

        $wards_list = [];

        foreach ($subcounty->getWards() as $ward) {
            $wards_list[] = [
                'id' => $ward->getId(),
                'name' => $ward->getName()
            ];
        }

        $response = [
            'success' => true,
            'data' => [
                'id' => $subcounty->getId(),
                'name' => $subcounty->getName(),
                'wards' => $wards_list
            ]
        ];

        return $this->renderText(json_encode($response));
    }

    public function executeWards(sfWebRequest $request)
    {
        $wards = Doctrine_Query::create()
            ->from('Ward w')
            ->leftJoin('w.Subcounty s')
            ->orderBy('w.id ASC')
            ->execute();

        $data = [];

        foreach ($wards as $ward) {
            $data[] = [
                'id' => $ward->getId(),
                'name' => $ward->getName(),
                'subcounty' => $ward->getSubcounty() ? $ward->getSubcounty()->getName() : null
            ];
        }

        return $this->renderText(json_encode([
            'success' => true,
            'data' => $data,
            'itemCount' => count($data)
        ]));
    }

    public function executeWardView(sfWebRequest $request)
    {
        $id = $request->getParameter('id');

        $ward = Doctrine_Query::create()
            ->from('Ward w')
            ->leftJoin('w.Subcounty s')
            ->where('w.id = ?', $id)
            ->fetchOne();

        if (!$ward) {
            return $this->renderText(json_encode([
                'success' => false,
                'message' => 'Ward not found.'
            ]));
        }

        $response = [
            'success' => true,
            'data' => [
                'id' => $ward->getId(),
                'name' => $ward->getName(),
                'subcounty' => $ward->getSubcounty()
                    ? [
                        'id' => $ward->getSubcounty()->getId(),
                        'name' => $ward->getSubcounty()->getName()
                    ]
                    : null
            ]
        ];

        return $this->renderText(json_encode($response));
    }



    public function executeApplicationsList(sfWebRequest $request)
    {
        $new_start_date = null;
        $new_end_date = null;

        $page = $request->getParameter('page');
        $limit = $request->getParameter('limit');
        $group_filter = $request->getParameter('application_type');
        $with_permit = $request->getParameter('with_permit');

        // filter with subcounty and plot_no
        $subcounty = $request->getParameter('subcounty');
        $plot_no = $request->getParameter('plot_no');

        $start_date = $request->getParameter('start_date');
        $end_date = $request->getParameter('end_date');

        if (!is_null($start_date)) {
            $start_date = date_create($start_date);
            $new_start_date = date_format($start_date, "Y-m-d H:i:s");
        }

        if (!is_null($end_date)) {
            $end_date = date_create($end_date);
            $new_end_date = date_format($end_date, "Y-m-d H:i:s");
        }

        $limit = is_null($limit) ? 10 : intval($limit);

        $form_list = [];

        $groups = $this->permitType();


        $q = Doctrine_Query::create()
            ->from('ApForms a')
            ->where('a.form_id in ?', array('6', '7', '15', '16', '17'))
            ->andWhere("a.form_active = 1")
            ->andWhere("a.form_type = 1")
            ->orderBy('a.form_name ASC');

        if (!is_null($subcounty)) {
            $qt = Doctrine_Query::create()
                ->from("SubMenus a")
                ->where("a.menu_id = ?", $subcounty);
            $stages = $qt->execute();
            $filtered_stages = array();

            foreach ($stages as $stage) {
                array_push($filtered_stages, $stage->getId());
            }

            $q->andWhereIn("a.form_stage", $filtered_stages);
        }

        $forms = $q->execute();
        $form_listing = [];

        foreach ($forms as $apform) {
            $form_groups[$apform->getFormId()] = $groups[$apform->getFormGroup()];
            if (!is_null($group_filter) && $apform->getFormGroup() == $group_filter) {
                array_push($form_list, $apform->getFormId());
            } else {
                array_push($form_listing, $apform->getFormId());
            }
        }

        if (!is_null($plot_no)) {
            $new_form_ = $this->mapping_forms_with_plot_no_id($form_listing);
            $entries = $this->get_entries_with_plot_no($new_form_, $plot_no);

            $result = $this->get_application_with_entry_id($entries[0], $entries[1], $with_permit, $limit, $page, $new_start_date, $new_end_date, $entries[2]);
            $app_list = $result[0];
            $total_count = $result[1];
            $page = $result[2];
        } else {
            if (!is_null($subcounty)) {
                $form_list = $form_listing;
            }
            $q_app = Doctrine_Query::create()
                ->addSelect('f.id')
                ->addSelect('f.application_id')
                ->addSelect('f.entry_id')
                ->addSelect('f.form_id')
                ->addSelect('s.title')
                ->addSelect('f.date_of_submission')
                ->addSelect('p.permit_id')
                ->from('FormEntry f')
                ->leftJoin('f.SubMenus s');
            if (!is_null($with_permit) && $with_permit == '0') {
                $q_app->leftJoin('f.SavedPermits p')->where('p.id IS NULL');
            } else if (!is_null($with_permit) && $with_permit == '1') {
                $q_app->leftJoin('f.SavedPermits p');
                $q_app->where('f.id = p.application_id')
                    ->andWhere('f.approved = s.id')
                    ->andWhere('p.permit_id IS NOT NULL')
                    ->andWhere("p.permit_id <>''");
            } else {
                $q_app->leftJoin('f.SavedPermits p');
            }
            // pull data from applications list

            if (count($form_list) > 0) {
                $q_app->andWhereIn(
                    'f.form_id',
                    $form_list
                );
            }

            error_log("New Start Date ---->" . $new_start_date);
            error_log("New End Date ---->" . $new_end_date);

            if (!is_null($new_start_date) && !is_null($new_end_date)) {
                $q_app->andWhere('f.date_of_submission BETWEEN ? AND ?', array($new_start_date, $new_end_date));
            }
            if (!is_null($new_start_date) && is_null($new_end_date)) {
                $new_start_date = date_create($new_start_date);
                $q_app->andWhere(
                    'f.date_of_submission BETWEEN ? AND ?',
                    array(date_format($new_start_date, "Y-m-d") . " 00:00:00", date_format($new_start_date, "Y-m-d") . " 23:59:59")
                );
            }
            if (is_null($new_start_date) && !is_null($new_end_date)) {
                $new_end_date = date_create($new_end_date);
                $q_app->andWhere('f.date_of_submission BETWEEN ? AND ?', array(date_format($new_end_date, "Y-m-d") . " 00:00:00", date_format($new_end_date, "Y-m-d") . " 23:59:59"));
            }


            $q_app->orderBy('f.id DESC');
            $total_count = $q_app->count();

            $q_app->limit($limit);

            if (!is_null($page)) {
                $from = $page * is_null($limit) ? 10 : $limit;
                $q_app->offset($from);
            }
            $page = is_null($page) ? 1 : intval($page);

            $app_list = $q_app->execute();
            $app_array = [];
        }
        $application_manager = new ApplicationManager();

        foreach ($app_list as $app) {
            $app_info = [];
            $sizes = [];
            $entry_details = $application_manager->get_application_details(
                $app->getFormId(),
                $app->getEntryId()
            );

            foreach ($entry_details as $data) {
                if ($data['element_type'] == "text" || $data['element_type'] == "select" || $data['element_type'] == "number") {
                    $new_label = str_replace(' ', '', $data['label']);
                    $new_label = strtolower($new_label);
                    if (stristr($new_label, 'plotno')) {
                        $app_info['plot_no2'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'plotplot_no')) {
                        $app_info['plot_no2'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'buildingtype')) {
                        $app_info['building_type'] = trim($data['value']);
                    }

                    if (stristr($new_label, 'buildingcategory')) {
                        $app_info['building_category'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'buildingcoverage')) {
                        $app_info['building_coverage'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'spacedesignated')) {
                        $app_info['space_designated'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'numberofdwellingunits')) {
                        $app_info['number_of_dwelling_units'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'province')) {
                        $app_info['province'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'subcounty')) {
                        $app_info['subcounty'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'sector')) {
                        $app_info['sector'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'cell')) {
                        $app_info['cell'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'village')) {
                        $app_info['village'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'zoning Plan')) {
                        $app_info['zoning'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'plotsize')) {

                        $sizes['plot_size'] = trim($data['value']);
                    }
                    if (stristr($new_label, 'builtuparea')) {
                        $sizes['built_up_area'] = trim($data['value']);
                    }
                    $app_info['shape_area'] = $sizes;
                }
            }
            $permits = $app->getSavedPermits() ? $app->getSavedPermits()->getData()[0] : false;
            $app_info['application_date'] = $app->getDateOfSubmission();
            $app_info['application_id'] = $app->getId();
            $app_info['application_number'] = $app->getApplicationId();
            $app_info['stage'] = $app->getSubMenus() ? $app->getSubMenus()->getTitle() : "";
            $app_info['permit_number'] = $permits ? $permits->getPermitId() : "";
            $app_info['permit_issue_date'] = $permits ? $permits->getDateOfIssue() : "";
            $app_info['permit_type'] = $form_groups[$app->getFormId()];
            $app_array[] = $app_info;
            $app_info = [];
            $sizes = [];
        }

        $last = ceil($total_count / $limit);
        $next = $last == $page ? $last : $page + 1;
        $param_array = [
            'permit_types' => $group_filter,
            'with_permit' => $with_permit,
            'subcounty' => $subcounty,
            'plot_no' => $plot_no,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'limit' => $limit,
            'page' => $next
        ];

        $new_param_array = array_filter($param_array);
        $query_param_array = http_build_query($new_param_array);

        $param_array['page'] = null;
        $new_param_array = array_filter($param_array);
        $f_param_array = http_build_query($new_param_array);

        $param_array['page'] = $last;
        $new_param_array = array_filter($param_array);
        $l_param_array = http_build_query($new_param_array);

        if (is_null($app_array)) {
            return $this->renderText(json_encode([
                'success' => false,
                'data' => [],
                'links' => [],
                'meta' => [],
                'message' => "Application with plot_no Not Found "
            ]));
        }
        return $this->renderText(json_encode([
            'success' => true,
            'data' => $app_array,
            "links" => [
                "first" => "/bpmis/api/v1.1/applications?" . $f_param_array,
                "next" => "/bpmis/api/v1.1/applications?" . $query_param_array,
                "last" => "/bpmis/api/v1.1/applications?" . $l_param_array
            ],
            "meta" => [
                "permit_types" => $groups,
                "currentPage" => $page,
                "itemCount" => $limit > $total_count ? $total_count : $limit,
                "totalItems" => $total_count,
                "totalPages" => $last
            ]
        ]));

        sfView::NONE;
    }

    public function get_application_with_entry_id($entry_list, $form_list, $with_permit, $limit, $page, $new_start_date, $new_end_date, $entries = [])
    {

        $q_app = '';
        $app_list = '';
        // pull data from applications list
        $q_app = Doctrine_Query::create()
            ->addSelect('f.id')
            ->addSelect('f.application_id')
            ->addSelect('f.entry_id')
            ->addSelect('f.form_id')
            ->addSelect('s.title')
            ->addSelect('f.date_of_submission')
            ->addSelect('p.permit_id')
            ->from('FormEntry f');
        if (count($entries) > 0) {
            //$q_app->andWhereIn(
            // 'f.entry_id',
            //$entry_list
            //);

            $new_entry_list = '(' . implode(' OR ', $entries) . ')';
            $q_app->where($new_entry_list);
        } else {
            return [
                [],
                0,
                0
            ];
        }
        // $q_app->andWhereIn(
        //     'f.form_id',
        //     $form_list
        // );

        if (!is_null($new_start_date) && !is_null($new_end_date)) {
            $q_app->andWhere('f.date_of_submission BETWEEN ? AND ?', array($new_start_date, $new_end_date));
        }
        if (!is_null($new_start_date) && is_null($new_end_date)) {
            $new_start_date = date_create($new_start_date);
            $q_app->andWhere('f.date_of_submission BETWEEN ? AND ?', array(date_format($new_start_date, "Y-m-d") . " 00:00:00", date_format($new_start_date, "Y-m-d") . " 23:59:59"));
        }
        if (is_null($new_start_date) && !is_null($new_end_date)) {
            $new_end_date = date_create($new_end_date);
            $q_app->andWhere('f.date_of_submission BETWEEN ? AND ?', array(date_format($new_end_date, "Y-m-d") . " 00:00:00", date_format($new_end_date, "Y-m-d") . " 23:59:59"));
        }


        if (!is_null($with_permit)) {
            $q_app->leftJoin('f.SavedPermits p')->andWhere("p.permit_id <>''")->andWhere('p.permit_id IS NOT NULL');
        }
        $q_app->orderBy('f.id DESC');
        $total_count = $q_app->count();

        $q_app->limit($limit);

        if (!is_null($page)) {
            $from = $page * is_null($limit) ? 10 : $limit;
            $q_app->offset($from);
        }

        $page = is_null($page) ? 1 : intval($page);
        $app_list = $q_app->execute();
        return [$app_list, $total_count, $page];
    }

}
