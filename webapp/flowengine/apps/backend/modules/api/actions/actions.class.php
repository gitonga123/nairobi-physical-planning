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

        $invoice_details['id']  = $invoice->getId();
        $invoice_details['paid']  = $invoice->getStatus();
        $invoice_details['created_at']  = $invoice->getCreatedAt();
        $invoice_details['invoice_number']  = $invoice->getInvoiceNumber();
        $invoice_details['expires_at']  = $invoice->getExpiresAt();
        $invoice_details['amount']  = $invoice->getTotalAmount();
        $invoice_details['currency']  = $invoice->getCurrency();
        $invoice_details['transaction_id']  = $invoice->getTransactionId();
        $application = $invoice->getFormEntry();
        $applications = $application_manager->getExtraApplicationInfo($application->getFormId(), $application->getEntryId());
        $invoice_details['plot_no'] = $applications[0];
        $invoice_details['owner_name'] = $applications[1];
        $invoice_details['fees']  =  [];

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


    private function json($content, $status = 200)
    {
        $this->getResponse()->setHttpHeader('Content-Type', 'application/json');
        $this->getResponse()->setContent(json_encode($content));
        $this->getResponse()->setStatusCode($status);
        return sfView::NONE;
    }
}
