<?php

/**
 * permits actions.
 *
 * @package    permit
 * @subpackage permits
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class permitsActions extends sfActions
{

    public function executeBarcode(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from("SavedPermit a")
            ->where("a.id = ?", $request->getParameter("id"));
        $saved_permit = $q->fetchOne();

        if ($request->getParameter("size")) {
            $_GET["size"] = $request->getParameter("size");
        }

        // Get pararameters that are passed in through $_GET or set to the default value
        $text = $saved_permit->getFormEntry()->getApplicationId() . ": " . $saved_permit->getDateOfExpiry();
        $size = (isset($_GET["size"]) ? $_GET["size"] : "20");
        $orientation = (isset($_GET["orientation"]) ? $_GET["orientation"] : "horizontal");
        $code_type = (isset($_GET["codetype"]) ? $_GET["codetype"] : "code128");
        $code_string = "";
        // Translate the $text into barcode the correct $code_type
        if (in_array(strtolower($code_type), array("code128", "code128b"))) {
            $chksum = 104;
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "\`" => "111422", "a" => "121124", "b" => "121421", "c" => "141122", "d" => "141221", "e" => "112214", "f" => "112412", "g" => "122114", "h" => "122411", "i" => "142112", "j" => "142211", "k" => "241211", "l" => "221114", "m" => "413111", "n" => "241112", "o" => "134111", "p" => "111242", "q" => "121142", "r" => "121241", "s" => "114212", "t" => "124112", "u" => "124211", "v" => "411212", "w" => "421112", "x" => "421211", "y" => "212141", "z" => "214121", "{" => "412121", "|" => "111143", "}" => "111341", "~" => "131141", "DEL" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "FNC 4" => "114131", "CODE A" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++) {
                $activeKey = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum = ($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
            $code_string = "211214" . $code_string . "2331112";
        } elseif (strtolower($code_type) == "code128a") {
            $chksum = 103;
            $text = strtoupper($text); // Code 128A doesn't support lower case
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++) {
                $activeKey = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum = ($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
            $code_string = "211412" . $code_string . "2331112";
        } elseif (strtolower($code_type) == "code39") {
            $code_array = array("0" => "111221211", "1" => "211211112", "2" => "112211112", "3" => "212211111", "4" => "111221112", "5" => "211221111", "6" => "112221111", "7" => "111211212", "8" => "211211211", "9" => "112211211", "A" => "211112112", "B" => "112112112", "C" => "212112111", "D" => "111122112", "E" => "211122111", "F" => "112122111", "G" => "111112212", "H" => "211112211", "I" => "112112211", "J" => "111122211", "K" => "211111122", "L" => "112111122", "M" => "212111121", "N" => "111121122", "O" => "211121121", "P" => "112121121", "Q" => "111111222", "R" => "211111221", "S" => "112111221", "T" => "111121221", "U" => "221111112", "V" => "122111112", "W" => "222111111", "X" => "121121112", "Y" => "221121111", "Z" => "122121111", "-" => "121111212", "." => "221111211", " " => "122111211", "$" => "121212111", "/" => "121211121", "+" => "121112121", "%" => "111212121", "*" => "121121211");
            // Convert to uppercase
            $upper_text = strtoupper($text);
            for ($X = 1; $X <= strlen($upper_text); $X++) {
                $code_string .= $code_array[substr($upper_text, ($X - 1), 1)] . "1";
            }
            $code_string = "1211212111" . $code_string . "121121211";
        } elseif (strtolower($code_type) == "code25") {
            $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
            $code_array2 = array("3-1-1-1-3", "1-3-1-1-3", "3-3-1-1-1", "1-1-3-1-3", "3-1-3-1-1", "1-3-3-1-1", "1-1-1-3-3", "3-1-1-3-1", "1-3-1-3-1", "1-1-3-3-1");
            for ($X = 1; $X <= strlen($text); $X++) {
                for ($Y = 0; $Y < count($code_array1); $Y++) {
                    if (substr($text, ($X - 1), 1) == $code_array1[$Y])
                        $temp[$X] = $code_array2[$Y];
                }
            }
            for ($X = 1; $X <= strlen($text); $X += 2) {
                if (isset($temp[$X]) && isset($temp[($X + 1)])) {
                    $temp1 = explode("-", $temp[$X]);
                    $temp2 = explode("-", $temp[($X + 1)]);
                    for ($Y = 0; $Y < count($temp1); $Y++)
                        $code_string .= $temp1[$Y] . $temp2[$Y];
                }
            }
            $code_string = "1111" . $code_string . "311";
        } elseif (strtolower($code_type) == "codabar") {
            $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "$", ":", "/", ".", "+", "A", "B", "C", "D");
            $code_array2 = array("1111221", "1112112", "2211111", "1121121", "2111121", "1211112", "1211211", "1221111", "2112111", "1111122", "1112211", "1122111", "2111212", "2121112", "2121211", "1121212", "1122121", "1212112", "1112122", "1112221");
            // Convert to uppercase
            $upper_text = strtoupper($text);
            for ($X = 1; $X <= strlen($upper_text); $X++) {
                for ($Y = 0; $Y < count($code_array1); $Y++) {
                    if (substr($upper_text, ($X - 1), 1) == $code_array1[$Y])
                        $code_string .= $code_array2[$Y] . "1";
                }
            }
            $code_string = "11221211" . $code_string . "1122121";
        }
        // Pad the edges of the barcode
        $code_length = 20;
        for ($i = 1; $i <= strlen($code_string); $i++)
            $code_length = $code_length + (int)(substr($code_string, ($i - 1), 1));
        if (strtolower($orientation) == "horizontal") {
            $img_width = $code_length;
            $img_height = $size;
        } else {
            $img_width = $size;
            $img_height = $code_length;
        }
        $image = imagecreate($img_width, $img_height);
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        $location = 10;
        for ($position = 1; $position <= strlen($code_string); $position++) {
            $cur_size = $location + (substr($code_string, ($position - 1), 1));
            if (strtolower($orientation) == "horizontal")
                imagefilledrectangle($image, $location, 0, $cur_size, $img_height, ($position % 2 == 0 ? $white : $black));
            else
                imagefilledrectangle($image, 0, $location, $img_width, $cur_size, ($position % 2 == 0 ? $white : $black));
            $location = $cur_size;
        }
        // Draw barcode to the screen
        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);

        $this->setLayout(false);
        exit;
    }

    /**
     * Executes 'List' action
     *
     * Displays list of all of the currently logged in client's permits
     *
     * @param sfRequest $request A request object
     */
    public function executeList(sfWebRequest $request)
    {
        $agency_manager = new AgencyManager();

        $this->filter_status = $request->getParameter("filter_status");
        $this->filter = $request->getParameter("filter"); //OTB code refactoring
        $this->fromdate = $request->getParameter("fromdate") ? date('Y-m-d', strtotime($request->getParameter("fromdate"))) : false; //OTB code refactoring
        $this->todate = $request->getParameter("todate") ? date('Y-m-d', strtotime($request->getParameter("todate"))) : false; //OTB code refactoring
        if (!$request->isXmlHttpRequest()) {
            $this->getUser()->setAttribute('filter_status', $this->filter_status);
            $this->getUser()->setAttribute('filter', $this->filter);
            $this->getUser()->setAttribute('fromdate', $this->fromdate);
            $this->getUser()->setAttribute('todate', $this->todate);
        }
        $q_form = Doctrine_Query::create()
            ->from('ApForms a')
            ->where('a.form_active = ? AND a.form_type = ?', [1, 1])
            ->orderBy('a.form_name ASC');
        $applicationforms = $q_form->execute();
        $agency_manager = new AgencyManager();
        $this->form_options = [];
        foreach ($applicationforms as $applicationform) {
            if ($agency_manager->checkAgencyStageAccess($this->getUser()->getAttribute('userid'), $applicationform->getFormStage())) { //OTB - Managing agency access
                $selected = "";
                if ($applicationform->getFormId() == $this->filter) {
                    $selected = 'selected="selected"';
                }

                $this->form_options[] = '<option value="' . $applicationform->getFormId() . '" ' . $selected . '>' . $applicationform->getFormName() . '</option>';
            }
        }
        if ($request->getParameter("export")) {
            $columns = [];
            $columns[] = "#";
            $columns[] = 'Form Title';
            $columns[] = 'Application Id';
            $columns[] = 'Applicant';
            $columns[] = 'Stage';
            $columns[] = 'Permit';
            $columns[] = 'Date issued';
            $columns[] = 'Date of expiry';
            $columns[] = 'Permit id';
            $columns[] = 'Status';

            $records = [];
            $q = $this->_permitsQuery();
            foreach ($q->execute() as $permit) {
                $data = [];
                $data[] = $permit->getId();
                $data[] = $permit->getFormEntry()->getForm()->getFormName();
                $data[] = $permit->getFormEntry()->getApplicationId();
                if ($permit->getFormEntry()->getSfGuardUserProfile()) {
                    $data[] = $permit->getFormEntry()->getSfGuardUserProfile()->getFullname();
                } else {
                    $data[] = '';
                }
                if ($permit->getFormEntry()->getStage()) {
                    $data[] = $permit->getFormEntry()->getStage()->getTitle();
                } else {
                    $data[] = '';
                }
                $data[] = $permit->getTemplate()->getTitle();
                $data[] = date('jS M Y H:i:s', strtotime($permit->getDateOfIssue()));
                if ($permit->getDateOfExpiry()) {
                    $data[] = date('jS M Y H:i:s', strtotime($permit->getDateOfExpiry()));
                } else {
                    $data[] = '';
                }
                $data[] = $permit->getPermitId();
                $data[] = $invoice->getPermitStatusDesc();

                $records[] = $data;
            }

            Outputsheet::ReportGenerator("Permits Report -" . date("Y-m-d"), $columns, $records);
            exit;
        }

        if ($request->isXmlHttpRequest() || $request->getParameter('draw')) {
            //columns
            $columns = array('p.id', 'f.form_name', 'e.application_id', 'p.fullname', 's.title', 't.title', 'p.date_of_issue', 'p.date_of_expiry', 'p.permit_id', 'p.permit_status');
            $q = $this->_permitsQuery($columns, $request);
            $result = array(
                "draw" => intval($request->getParameter('draw')),
                "recordsTotal" => $this->_permitsQuery(null, $request)->count(),
                "recordsFiltered" => $q->count(),
                "data" => []
            );
            //ORDER
            $q->orderBy($columns[$request->getParameter('order')[0]['column']] . ' ' . $request->getParameter('order')[0]['dir']);
            //For pagination
            $q->offset($request->getParameter('start'));
            $q->limit($request->getParameter('length'));

            foreach ($q->execute() as $permit) {
                $data = new stdClass;
                $data->id = $permit->getId();
                $data->form_name = '';
                $data->stage = '';
                $data->application_id = '';
                $data->app_id = '';
                $data->user = '';
                if ($permit->getFormEntry()) {
                    $data->form_name = $permit->getFormEntry()->getForm()->getFormName();
                    $data->stage = $permit->getFormEntry()->getStage()->getTitle();
                    $data->application_id = $permit->getFormEntry()->getApplicationId();
                    $data->app_id = $permit->getFormEntry()->getId();
                    if ($permit->getFormEntry()->getSfGuardUserProfile()) {
                        $data->user = $permit->getFormEntry()->getSfGuardUserProfile()->getFullname();
                    }
                }
                $data->status = $permit->getPermitStatusDesc();
                $data->date_issued = date('jS M Y H:i:s', strtotime($permit->getDateOfIssue()));
                if ($permit->getDateOfExpiry()) {
                    $data->expiry_date = date('jS M Y H:i:s', strtotime($permit->getDateOfExpiry()));
                }
                $data->permit = $permit->getTemplate()->getTitle();
                $data->permit_id = $permit->getPermitId();

                $result['data'][] = $data;
            }
            $this->getResponse()->setContent(json_encode($result));
            return sfView::NONE;
        }
        $this->setLayout('layout');
    }

    /**
     * Executes 'Viewsigned' action
     *
     * Sign permit
     *
     * @param sfRequest $request A request object
     */
    public function executeViewsigned(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $request->getParameter('id'));
        $this->permit = $q->fetchOne();
        $this->setLayout(false);
    }


    /**
     * Executes 'View' action
     *
     * Views permit
     *
     * @param sfRequest $request A request object
     */
    public function executeView(sfWebRequest $request)
    {
        $this->permit = false;
        $this->application = false;
        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $request->getParameter('id'));
        $this->permit = $q->fetchOne();
        
        if ($this->permit) {
            $q = Doctrine_Query::create()
                ->from('FormEntry a')
                ->where('a.id = ?', $this->permit->getApplicationId());
            $this->application = $q->fetchOne();
        }
    }


    public function executeCancelpermit(sfWebRequest $request)
    {
        //Audit 
        Audit::audit("", "Cancelled permit");

        $this->forward404Unless($permits = Doctrine_Core::getTable('SavedPermit')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
        $permits->setPermitStatus(3);
        $permits->save();

        $this->redirect('/backend.php/applications/view/id/' . $permits->getFormEntry()->getId());
    }

    public function executeUpdatepermit(sfWebRequest $request)
    {
        $this->forward404Unless($permit = Doctrine_Core::getTable('SavedPermit')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));

        //Run the save function first so that remote results are updated. Then generate pdf with new results and run save again
        $permit->save();

        $permit_manager = new PermitManager();

        $filename = $permit_manager->save_to_pdf_locally($permit->getId());
        $permit->setPdfPath($filename);
        $permit->save();

        $this->redirect('/backend.php/permits/view/id/' . $permit->getId());
    }

    public function executeUncancelpermit(sfWebRequest $request)
    {
        //Audit 
        Audit::audit("", "Uncancelled permit");

        $this->forward404Unless($permits = Doctrine_Core::getTable('SavedPermit')->find(array($request->getParameter('id'))), sprintf('Object permits does not exist (%s).', $request->getParameter('id')));
        $permits->setPermitStatus(1);
        $permits->save();

        $this->redirect('/backend.php/permits/view/id/' . $permits->getId());
    }


    public function executeUpdatesingleremote(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $request->getParameter('id'));
        $permit = $q->fetchOne();

        if ($permit) {
            try {
                $permit->save();

                $this->redirect("/backend.php/permits/view/id/" . $permit->getId());
            } catch (Exception $ex) {
                error_log("Updates Managert: Remote update error: " . $ex);
                echo "Remote Update Error Debug-v: " . $error;
                exit;
            }
        }
    }
    private function _permitsQuery($cols = null, $request = null)
    {
        $filter_status = $this->getUser()->getAttribute("filter_status", 1);
        $filter = $this->getUser()->getAttribute("filter"); //OTB code refactoring
        $fromdate = $this->getUser()->getAttribute("fromdate") ? date('Y-m-d', strtotime($this->getUser()->getAttribute("fromdate"))) : false; //OTB code refactoring
        $todate = $this->getUser()->getAttribute("todate") ? date('Y-m-d', strtotime($this->getUser()->getAttribute("todate"))) : false; //OTB code refactoring
        $q = Doctrine_Query::create()
            ->from("SavedPermit p")
            ->leftJoin('p.Template t')
            ->leftJoin('p.FormEntry e')
            ->leftJoin('e.Form f')
            ->leftJoin('e.Stage s')
            ->leftJoin('e.SfGuardUserProfile u');
        if ($filter_status) {
            $q->where("p.permit_status = ?", $filter_status); //OTB code refactoring
        }
        if ($fromdate && $todate) {
            $q->andWhere("p.date_of_issue BETWEEN ? AND ?", array($fromdate . " 00:00:00", $todate . " 23:59:59"));
        }
        if ($filter) {
            $q->andWhere("f.form_id = ?", $filter);
        }
        if (null === $cols) return $q;

        $search = $request->getParameter('search')['value'];

        if ("" === $search) return $q;
        $sql = [];
        $params = [];

        foreach ($cols as $i => $col) {
            $sql[] = $col . " LIKE ?";
            $params[] = '%' . $search . '%';
        }
        //error_log('-----------SQL----------');
        //error_log(print_r($sql,true));
        //error_log('-------------PARAMS---------');
        //error_log(print_r($params,true));
        //error_log('-------SQL------'.$q->getSqlQuery());

        $q->addWhere("(" . implode(" OR ", $sql) . ")", $params);
        return $q;
    }
    # SASALOG begin signing
    public function executeSigning(sfWebRequest $request)
    {
        # save file
        # login to docusign
        # pass document for signing
        # sign in
        # download it back to server

        $action = $request->getParameter('permitaction');
        $permit_ids = (gettype($in_r = $request->getParameter('id')) == 'array') ? implode(',', $in_r) : $request->getParameter('id');

        $client_id = sfConfig::get('app_docusign_integration_key');

        # authenticate the user
        if ($action == 'signdocument') {
            # login to docusign
            $redirect_uri = $this->base_url_ . "/backend.php/permits/signing";
            $data_pass = json_encode(
                array(
                    "id" => $permit_ids,
                    "permitaction" => "getaccesstoken",
                    'l_redirect' => $request['l_redirect'],
                    'next_action' => $request->getParameter('next_action')
                )
            );

            $login_url = "https://account-d.docusign.com/oauth/auth?"
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
            $permit_ids = $state['id'];
            $next_action = $state['next_action'];

            if ($state['permitaction'] == 'getaccesstoken') {
                $code = $request->getParameter('code');
                $args = $this->getAccessToken($code);
                $args['permits'] = explode(',', $permit_ids);
                $args['l_redirect'] = $state['l_redirect'];

                if ($next_action == 'download') {
                    $args = $args + $state;
                    $this->download_signed_document_from_docusign($args);
                } else {
                    $url = $this->embedded_signing_ceremony($args);
                    $this->redirect($url);
                }
            }
        }

        if ($action == 'download_signed_permit') {
            $redirect_uri = $this->base_url_ . "/backend.php/permits/signing";
            $data_pass = json_encode(
                array(
                    "id" => $permit_ids,
                    "permitaction" => "getaccesstoken",
                    'next_action' => 'download'
                ) + $request->getGetParameters()
            );

            $login_url = "https://account-d.docusign.com/oauth/auth?"
                . "response_type=code"
                . "&scope=signature"
                . "&client_id=$client_id"
                . "&state=$data_pass"
                . "&redirect_uri=$redirect_uri";
            $this->redirect($login_url);
        }

        if (isset($args) && ($redirect_to = $args['l_redirect'])) {
            $this->redirect($redirect_to);
        }
        $this->redirect("/backend.php/permits/view/id/$permit_ids");
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
        $authorization_request = curl_init('https://account-d.docusign.com/oauth/token');
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
        $user_details_request = curl_init('https://account-d.docusign.com/oauth/userinfo');
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
        $permit_ids = $args["permits"]; // hold documents
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

        foreach ($permit_ids as $permit_id) {
            $permit = Doctrine_Query::create()
                ->from('SavedPermit a')
                ->where('a.id = ?', $permit_id)
                ->fetchOne();

            $file_name = $permit->getFileName();
            $file_path = $permit->getUnSignedFilePath();

            # Step 1. The envelope definition is created.
            #         One signHere tab is added.
            #         The document path supplied is relative to the working directory
            #
            # Create the component objects for the envelope definition...
            $contentBytes = file_get_contents($appPath . "/" . $file_path);
            $base64FileContent = base64_encode($contentBytes);

            # create the DocuSign document object
            array_push($documents, new DocuSign\eSign\Model\Document([
                'document_base64' => $base64FileContent,
                'name' => $file_name, # can be different from actual file name
                'file_extension' => 'pdf', # many different document types are accepted
                'document_id' => $permit_id # a label used to reference the doc
            ]));
        }

        # DocuSign SignHere field/tab object
        array_push($sign_position, $signHere = new DocuSign\eSign\Model\SignHere([
            'document_id' => $permit_id,
            'page_number' => '1',
            'tab_label' => "Sign Permit $file_name",

            'anchor_string' => 'sig|req|signer1',
            'anchor_y_offset' => '10',
            'anchor_x_offset' => '8'
        ]));

        //        TEMPORARY START for demo
        array_push($sign_position, $signHere = new DocuSign\eSign\Model\SignHere([
            'document_id' => $permit_id,
            'page_number' => '1',
            'tab_label' => "Sign Permit $file_name",

            'anchor_string' => 'Your premises has',
            'anchor_y_offset' => '40',
            'anchor_x_offset' => '0'
        ]));
        //        TEMPORARY END for demo


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

        $returnUrl = $this->base_url_ . "/backend.php/permits/signing?"
            . "id=" . implode(',', $permit_ids)
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

        $results = $envelopeApi->createRecipientView(
            $accountId,
            $envelopeId,
            $recipientViewRequest
        );


        ### note signing session
        # BEGIN log start of signing session
        $now = date('Y-m-d h:i:s');
        $last_signing_session = Functions::lastSigningSession()['id'];
        $q = "UPDATE user_signing_sessions SET started_at = '$now', envelop_id = '$envelopeId' WHERE id = $last_signing_session";
        Doctrine_Manager::getInstance()->getCurrentConnection()->execute($q);
        # END logging signing session

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
            $me = $this->getUser()->getAttribute('userid');

            foreach (explode(',', $args['id']) as $permit_id) {
                $result = $envelope_api->getDocument($args['account_id'], $permit_id, $args['envelope_id']);
                $permit = Doctrine_Query::create()
                    ->from('SavedPermit a')
                    ->where('a.id = ?', $permit_id)
                    ->fetchOne();

                $path = "app/permits/signed/" . $permit->getFileName();
                rename($result->getPathname(), $path);

                # update permit to show who signed it

                $permit->setSignedBy($me);
                $permit->save();

                # update task to completed
                # -> find task(s) for signing this permit
                $slug = $permit->getTaskSlug();
                $conn->execute("UPDATE task SET status = 25 WHERE task_application_slug = \"$slug\"");

                # BEGIN log start of signing session
                # find the session for this envelope
                $envelopeId = $args['envelope_id'];

                if ($found = $conn->fetchAssoc("SELECT * FROM user_signing_sessions WHERE envelop_id = '$envelopeId'")) {
                    $me = Functions::current_user()->getNid();
                    # update my remaining sessions
                    if (
                        $found[0] and
                        !$found[0]['completed_at'] and
                        $result = $conn->fetchAssoc("SELECT used_signatures FROM user_signings WHERE user_id = $me")
                    ) {
                        $k = $result[0]['used_signatures'] ?: 0;
                        $k++;
                        $conn->execute("UPDATE user_signings SET used_signatures = $k WHERE user_id = $me");
                    }

                    $now = date('Y-m-d h:i:s');
                    Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UPDATE user_signing_sessions  SET completed_at = '$now', status = 2 WHERE envelop_id = '$envelopeId' and user_id = $me");
                }
                # END logging signing session
            }
        } catch (Exception $e) {
            print($e->getMessage());
        }
    }

    function executeDownloadsignedpermit(sfRequest $request)
    {
        $permit_id = $request->getParameter('permit_id');
        $permit = Doctrine_Query::create()->from("SavedPermit a")->where('a.id = ?', $permit_id)->fetchOne();
        $file_name = (new PermitManager())->permit_file_name($permit);
        $file_name = "apps/permits/signed/$file_name";
        error_log('----------FileName-------' . $file_name);
        if (file_exists($file_name)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_name));
            readfile($file_name);
            exit();
        } else
            return $this->redirect('/backend.php/applications/view/id/' . $permit->getApplicationId());
    }

    public function executeDownloadpermit(sfWebRequest $request)
    {
        $permit_id = $request->getParameter('permit_id');

        $q = Doctrine_Query::create()
            ->from('SavedPermit a')
            ->where('a.id = ?', $permit_id);
        $permit = $q->fetchOne();


        if ($permit) {
            $file_path = Functions::saveUnsignedPermit($permit);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
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
    public function __construct($context, $moduleName, $actionName)
    {
        parent::__construct($context, $moduleName, $actionName);
        $this->base_url_ = (empty($_SERVER['HTTPS']) ? "http://" : "https://") . $_SERVER['HTTP_HOST'];
    }
    # SASALOG end signing
    /**
     * get application details to preview while showing
     *
     * @param sfWebRequest $request
     * @return string
     */
    public function executePreviewapplicationdetails(sfWebRequest $request): string
    {
        $id = $request->getParameter('id');
        $application = (new ApplicationManager())->get_application_by_id($id);

        $form_id = $application->getFormId();
        $entry_id = $application->getEntryId();

        $prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";

        //require_once($prefix_folder.'config.php');
        require_once($prefix_folder . 'includes/db-core.php');
        require_once($prefix_folder . 'includes/helper-functions.php');

        require_once($prefix_folder . 'includes/entry-functions.php');
        $dbh = mf_connect_db();
        $entry_details = mf_get_entry_details($dbh, $form_id, $entry_id, [], $this->getUser()->getCulture(), $path = 'backend.php');
        $row_style = $row_markup = "";
        foreach ($entry_details as $row) {
            if ($row['element_type'] == "page_break") {
                //skip
            } else if ($row['element_type'] == "section") {
                $row_markup = "";
                $row_markup .= "<tr {$row_style}>\n";
                $row_markup .= "<td colspan='2'><h3>{$row['label']}</h3></td>\n";
                $row_markup .= "</tr>\n";
                echo $row_markup;
            } else {
                $row_markup = "";
                if ($row['label'] === 'Current SBP Number') {
                    $row_markup = "";
                    $row_markup .= "<tr {$row_style}>\n";
                    $row_markup .= "<td><strong>{$row['label']}</strong></td>\n";
                    $row_markup .= "<td><a href='/backend.php/applications/closeOfBusiness/target_application/" . $row['value'] . "/application_id/" . $application->getId() . "' target='_blank'>" . nl2br($row['value']) . "</a></td>\n";
                    $row_markup .= "</tr>\n";
                } else {
                    $row_markup = "";
                    $row_markup .= "<tr {$row_style}>\n";
                    $row_markup .= "<td><strong>{$row['label']}</strong></td>\n";
                    $row_markup .= "<td>" . nl2br($row['value']) . "</td>\n";
                    $row_markup .= "</tr>\n";
                }
                echo $row_markup;
            }
        }
        exit;
    }
}
