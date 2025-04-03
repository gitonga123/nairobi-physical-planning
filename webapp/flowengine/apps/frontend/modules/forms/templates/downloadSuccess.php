<?php
use setasign\Fpdi\Fpdi;
$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
require($prefix_folder . 'includes/init.php');

require($prefix_folder . '../../../config/form_builder_config.php');
require($prefix_folder . 'includes/db-core.php');
require($prefix_folder . 'includes/helper-functions.php');
require dirname(__FILE__) . "/../../../../../vendor/pendalff/phpqrcode/phpqrcode.php";
//the default is not to store file upload as blob, unless defined otherwise within config.php file
defined('MF_STORE_FILES_AS_BLOB') or define('MF_STORE_FILES_AS_BLOB', false);

ob_clean(); //clean the output buffer

//get query string and parse it, query string is base64 encoded
$query_string = trim($_GET['q']);
parse_str(base64_decode($query_string), $params);

$form_id = (int) $params['form_id'];
$id = (int) $params['id'];
$field_name = str_replace(array("`", "'", ';'), '', $params['el']);
$file_hash = $params['hash'];
//OTB ADD
$element_mark_file_with_qr_code = $params['element_mark_file_with_qr_code'];//OTB Africa Add QR on attachments
$element_file_qr_all_pages = $params['element_file_qr_all_pages'];//OTB Africa Add QR on attachments
$element_file_qr_page_position = is_null($params['element_file_qr_page_position']) ? "top_right" : $params['element_file_qr_page_position'];//OTB Africa Add QR on attachments

error_log(json_encode($params));

error_log("element_mark_file_with_qr_code ---->{$element_mark_file_with_qr_code}");
error_log("element_file_qr_all_pages ---->{$element_file_qr_all_pages}");
error_log("element_file_qr_page_position ---->{$element_file_qr_page_position}");

error_log("Debug above");

$otbhelper = new OTBHelper();
$app_details = $otbhelper->getApplicationDetailsUsingCompositeDetails($form_id, $id);
$stage_approved = $otbhelper->getStageWithSetStageType();

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);

//get filename
$query = "select `{$field_name}` from `" . MF_TABLE_PREFIX . "form_{$form_id}` where id=?";
$params = array($id);

$sth = mf_do_query($query, $params, $dbh);
$row = mf_do_fetch_result($sth);

$filename_array = array();
$filename_array = explode('|', $row[$field_name]);

$filename_md5_array = array();
foreach ($filename_array as $value) {
	$filename_md5_array[] = md5($value);
}

$file_key = array_keys($filename_md5_array, $file_hash);
if (empty($file_key)) {
	die("Error. File not exist.");
} else {
	$file_key = $file_key[0];
}

$complete_filename = $filename_array[$file_key];

//remove the element_x-xx- suffix we added to all uploaded files
$file_1 = substr($complete_filename, strpos($complete_filename, '-') + 1);
$filename_only = substr($file_1, strpos($file_1, '-') + 1);

$target_file = $mf_settings['upload_dir'] . "/form_{$form_id}/files/{$complete_filename}";

// Get extension of requested file
$extension = pathinfo(strtolower($filename_only), PATHINFO_EXTENSION);

// Determine correct MIME type
switch ($extension) {
	case "asf":
		$type = "video/x-ms-asf";
		break;
	case "avi":
		$type = "video/x-msvideo";
		break;
	case "bin":
		$type = "application/octet-stream";
		break;
	case "bmp":
		$type = "image/bmp";
		break;
	case "cgi":
		$type = "magnus-internal/cgi";
		break;
	case "css":
		$type = "text/css";
		break;
	case "dcr":
		$type = "application/x-director";
		break;
	case "dxr":
		$type = "application/x-director";
		break;
	case "dll":
		$type = "application/octet-stream";
		break;
	case "doc":
		$type = "application/msword";
		break;
	case "exe":
		$type = "application/octet-stream";
		break;
	case "gif":
		$type = "image/gif";
		break;
	case "gtar":
		$type = "application/x-gtar";
		break;
	case "gz":
		$type = "application/gzip";
		break;
	case "htm":
		$type = "text/html";
		break;
	case "html":
		$type = "text/html";
		break;
	case "iso":
		$type = "application/octet-stream";
		break;
	case "jar":
		$type = "application/java-archive";
		break;
	case "java":
		$type = "text/x-java-source";
		break;
	case "jnlp":
		$type = "application/x-java-jnlp-file";
		break;
	case "js":
		$type = "application/x-javascript";
		break;
	case "jpg":
		$type = "image/jpeg";
		break;
	case "jpe":
		$type = "image/jpeg";
		break;
	case "jpeg":
		$type = "image/jpeg";
		break;
	case "lzh":
		$type = "application/octet-stream";
		break;
	case "mdb":
		$type = "application/mdb";
		break;
	case "mid":
		$type = "audio/x-midi";
		break;
	case "midi":
		$type = "audio/x-midi";
		break;
	case "mov":
		$type = "video/quicktime";
		break;
	case "mp2":
		$type = "audio/x-mpeg";
		break;
	case "mp3":
		$type = "audio/mpeg";
		break;
	case "mpg":
		$type = "video/mpeg";
		break;
	case "mpe":
		$type = "video/mpeg";
		break;
	case "mpeg":
		$type = "video/mpeg";
		break;
	case "pdf":
		$type = "application/pdf";
		break;
	case "php":
		$type = "application/x-httpd-php";
		break;
	case "php3":
		$type = "application/x-httpd-php3";
		break;
	case "php4":
		$type = "application/x-httpd-php";
		break;
	case "png":
		$type = "image/png";
		break;
	case "ppt":
		$type = "application/mspowerpoint";
		break;
	case "qt":
		$type = "video/quicktime";
		break;
	case "qti":
		$type = "image/x-quicktime";
		break;
	case "rar":
		$type = "encoding/x-compress";
		break;
	case "ra":
		$type = "audio/x-pn-realaudio";
		break;
	case "rm":
		$type = "audio/x-pn-realaudio";
		break;
	case "ram":
		$type = "audio/x-pn-realaudio";
		break;
	case "rtf":
		$type = "application/rtf";
		break;
	case "swa":
		$type = "application/x-director";
		break;
	case "swf":
		$type = "application/x-shockwave-flash";
		break;
	case "tar":
		$type = "application/x-tar";
		break;
	case "tgz":
		$type = "application/gzip";
		break;
	case "tif":
		$type = "image/tiff";
		break;
	case "tiff":
		$type = "image/tiff";
		break;
	case "torrent":
		$type = "application/x-bittorrent";
		break;
	case "txt":
		$type = "text/plain";
		break;
	case "wav":
		$type = "audio/wav";
		break;
	case "wma":
		$type = "audio/x-ms-wma";
		break;
	case "wmv":
		$type = "video/x-ms-wmv";
		break;
	case "xls":
		$type = "application/vnd.ms-excel";
		break;
	case "xml":
		$type = "application/xml";
		break;
	case "7z":
		$type = "application/x-compress";
		break;
	case "zip":
		$type = "application/x-zip-compressed";
		break;
	default:
		$type = "application/force-download";
		break;
}

// Fix IE bug [0]
$header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $filename_only, substr_count($filename_only, '.') - 1) : $filename_only;

//Prepare headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public", false);
header("Content-Description: File Transfer");
header("Content-Type: " . $type);
header("Accept-Ranges: bytes");
header("Content-Disposition: attachment; filename=\"" . addslashes($header_file) . "\"");
header("Content-Transfer-Encoding: binary");
//OTB Africa Add QR on attachments
error_log('----$extension--' . $extension . '-----element_mark_file_with_qr_code---' . $element_mark_file_with_qr_code);
error_log("Extension type is --->{$extension}");
error_log("Stage Approved ---->{$stage_approved} - Application id {$app_details->getId()}");
error_log("Check application aproved ----> {$otbhelper->checkApplicationApproved($stage_approved, $app_details->getId())}");

if ($extension == 'pdf' && $element_mark_file_with_qr_code && $otbhelper->checkApplicationApproved($stage_approved, $app_details->getId())) {
	error_log('-------------QR CODE TO BE MARKED----------');
	#$prefix_folder_fpdf = dirname(__FILE__)."/../../../../../lib/vendor/otbafrica/fpdf/";
	#require_once($prefix_folder_fpdf.'fpdf181/fpdf.php');
	#require_once($prefix_folder_fpdf.'fpdf/fpdi.php');
	#require_once dirname(__FILE__)."/../../../../../lib/vendor/phpqrcode/qrlib.php";

	$PNG_TEMP_DIR = dirname(__FILE__) . "/../../../../../web/asset_data/";
	if (!file_exists($PNG_TEMP_DIR))
		mkdir($PNG_TEMP_DIR);
	$filename = $PNG_TEMP_DIR . 'test.png';

	//remember to sanitize user input in real-life solution !!!
	$errorCorrectionLevel = 'L';
	if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L', 'M', 'Q', 'H')))
		$errorCorrectionLevel = $_REQUEST['level'];
	$matrixPointSize = 2;
	if (isset($_REQUEST['size']))
		$matrixPointSize = min(max((int) $_REQUEST['size'], 1), 10);
	$ssl_suffix = mf_get_ssl_suffix();
	#$link = "http".$ssl_suffix."://".$_SERVER[HTTP_HOST]."/".$target_file;
	//$link = "http".$ssl_suffix."://".$_SERVER[HTTP_HOST]."/plan/forms/download?q=".$_GET['q'];//Show original file with login required
	// $link = "http".$ssl_suffix."://".$_SERVER[HTTP_HOST]."/plan/forms/download?q=".$_GET['q'];//Show original file with login required
	$link = "/index.php/forms/download?q=" . $_GET['q'];

	$filename = $PNG_TEMP_DIR . 'test' . md5($link . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';

	QRcode::png($link, $filename, $errorCorrectionLevel, $matrixPointSize, 1);

	$pieces = explode("/", $filename);

	$actual_filename = $pieces[sizeof($pieces) - 1];

	///Extend FPDI
	//https://gist.github.com/benshimmin/4088493
	function pixelsToMM($val)
	{
		//DPI = 96; MM_IN_INCH = 25.4;
		return $val * 25.4 / 96;
	}
	///////
	$pdf = new Fpdi();

	$pages = $pdf->setSourceFile($target_file);
	error_log('-----PAge count-- setSourceFile ---' . $pages);
	$qr_code_image = dirname(__FILE__) . "/../../../../../web/asset_data/" . $actual_filename;
	$size_qr_code_image = getimagesize($qr_code_image);
	error_log(print_r($size_qr_code_image, true));
	$width_qr_code_image = pixelsToMM($size_qr_code_image[0]);
	$height_qr_code_image = pixelsToMM($size_qr_code_image[1]);
	$x_qr_pos = 0;
	$y_qr_pos = 0;
	//mark all pages with QR code if set on form builder
	$page_count = 1;
	while ($page_count <= $pages) {
		$tplIdx = $pdf->importPage($page_count, '/MediaBox');
		$page_size = $pdf->getTemplatesize($tplIdx);
		error_log('-------' . print_r($page_size, true));
		error_log('-------' . $page_size['width'] . '-----' . $page_size['height']);
		//$pdf->addPage();
		//Add new page based on the original file's width, height and orientation
		if ($page_size['width'] > $page_size['height']) {
			error_log('--------Width > height-----');
			$pdf->addPage('L', array($page_size['width'], $page_size['height']));
		} else {
			error_log('--------Width < height-----');
			$pdf->addPage('P', array($page_size['width'], $page_size['height']));
			error_log('------------add page-----');
		}
		$pdf->useTemplate($tplIdx, 0, 0, null, null, true);
		if ($element_file_qr_page_position == "top_left") {//Set position of QR code
			error_log('-----------top left-------');
		} else if ($element_file_qr_page_position == "top_right") {
			error_log('-----------top_right-------');
			$x_qr_pos = floatval($page_size['width']) - $width_qr_code_image;
			$y_qr_pos = 0;
		} else if ($element_file_qr_page_position == "bottom_left") {
			error_log('-----------bottom_left-------');
			$x_qr_pos = 0;
			$y_qr_pos = floatval($page_size['height']) - $height_qr_code_image;
		} else if ($element_file_qr_page_position == "bottom_right") {
			error_log('-----------bottom_right-------');
			$x_qr_pos = floatval($page_size['width']) - $width_qr_code_image;
			$y_qr_pos = floatval($page_size['height']) - $height_qr_code_image;
		}
		error_log('------Qr position---' . $element_file_qr_page_position);
		error_log('------x-qr--' . $x_qr_pos . '----y_qr_pos---' . $y_qr_pos);
		$pdf->SetFont('Arial', 'BU');
		$pdf->SetTextColor(34, 139, 34);
		$pdf->SetXY(23, 10);
		////// check if application already approved
		//if(){
		//error_log("Application approved!! Bar code permitted") ;
		if ($element_file_qr_all_pages != 1) {//If this file field is not set to mark qr on all pages, set pages to first page only
			if ($page_count == 1) {
				// $pdf->Write(0, "Scan QR code to confirm authenticity");
				$pdf->Image($qr_code_image, $x_qr_pos, $y_qr_pos);
			}
		} else {
			// $pdf->Write(0, "Scan QR code to confirm authenticity");
			$pdf->Image($qr_code_image, $x_qr_pos, $y_qr_pos);
		}
		//}else{
		//error_log("Application not approved!! No Bar code permitted") ;
		//}


		/*if($element_file_qr_all_pages != 1){//If this file field is not set to mark qr on all pages, set pages to first page only
											 if($page_count == 1){
												 $pdf->Write(0,"Scan QR code to confirm authenticity");
												 $pdf->Image($qr_code_image,$x_qr_pos,$y_qr_pos);
											 }
										 }else{
											 $pdf->Write(0,"Scan QR code to confirm authenticity");
											 $pdf->Image($qr_code_image,$x_qr_pos,$y_qr_pos);
										 }*/
		$page_count++;
	}
	//remove the element_x-xx- suffix we added to all uploaded files
	$file_1 = substr($complete_filename, strpos($complete_filename, '-') + 1);
	$filename_only = substr($file_1, strpos($file_1, '-') + 1);
	$pdf->Output("D", $filename_only);
	exit;
	//OTB Africa - End mark PDF with QR Code
} else if (MF_STORE_FILES_AS_BLOB !== true) {
	if (!file_exists($target_file)) {
		$target_file .= '.tmp';
	}

	if (file_exists($target_file)) {
		header("Content-Length: " . filesize($target_file));

		// Send file for download
		if ($stream = fopen($target_file, 'rb')) {
			while (!feof($stream) && connection_status() == 0) {
				//reset time limit for big files
				@set_time_limit(0);
				print (fread($stream, 1024 * 8));
				flush();
			}
			fclose($stream);
		}
	} else {
		echo 'Error. File not found!';
	}
} else {
	$query = "SELECT file_content FROM " . MF_TABLE_PREFIX . "form_{$form_id}_files WHERE file_name = ? or file_name = ?";

	$sth = $dbh->prepare($query);
	try {
		$sth->execute(array($complete_filename, $complete_filename . '.tmp'));
		$sth->bindColumn(1, $file_data, PDO::PARAM_LOB);
		$sth->fetch(PDO::FETCH_BOUND);

		if (is_string($file_data)) {
			echo $file_data;
		} else {
			fpassthru($file_data);
		}
	} catch (PDOException $e) {
		$sth->debugDumpParams();
		die("Query Failed: " . $e->getMessage());
	}
}

exit;
?>