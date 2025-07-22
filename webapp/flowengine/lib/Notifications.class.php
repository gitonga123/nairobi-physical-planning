<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use AfricasTalking\SDK\AfricasTalking;

class mailnotifications
{
	public function __construct()
	{

	}

	public function sendemail($from, $to, $subject, $body, $send_emails = false)
	{

		if (!$send_emails) {
			return;
		}
		$q = Doctrine_Query::create()
			->from("SfGuardUserProfile a")
			->where("a.email = ?", $to);
		$user = $q->fetchOne();
		$username = '';
		$userid = "";
		if ($user) {
			$userid = $user->getUserId();
			$username = $user->getFullname();
		}

		//Get settings
		$q = Doctrine_Query::create()
			->from("ApSettings a")
			->orderBy("a.id DESC");
		$apsettings = $q->fetchOne();

		$organisation_name = "";
		$organisation_email = "";

		if ($apsettings) {
			$organisation_name = $apsettings->getOrganisationName();
			$organisation_email = $apsettings->getOrganisationEmail();
			$organisation_logo = '/' . $apsettings->getUploadDir() . '/' . $apsettings->getAdminImageUrl();
		}

		$tophtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
						<html xmlns="http://www.w3.org/1999/xhtml">
						<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
						<title>' . $subject . '</title>
						<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />
						<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,600,600italic,700,700italic,800,800italic"/>
						<style type="text/css">
						body {
							background-color: #f9f9f9;
							margin: 0px;
							padding: 0px;
							text-align: center;
							width: 100%;
						}
						html { width: 100%; }
						.contentbg
						{
								background-color:#f9f9f9;
						}
						img {
							border:0px;
							outline:none;
							text-decoration:none;
							display:block;
						}
						a img {
							border: none;
						}
						.maintext {
							font-family: \'Open Sans\', sans-serif;
							font-size: 25px;
							font-weight: 400;
							color: #000;
							line-height: 40px;
						}
						.headingtitle {
							font-family: \'Open Sans\', sans-serif;
							font-size: 18px;
							font-weight: 600;
							color: #ffffff;
							line-height: 24px;
							text-align:left;
						}
						.textwhite {
							font-family: Arial, Helvetica, sans-serif;
							font-size: 12px;
							font-weight: normal;
							color: #ffffff;
							line-height: 16px;
							text-align:left;
						}
						.textdark {
							font-family: Arial, Helvetica, sans-serif;
							font-size: 14px;
							font-weight: normal;
							color: #111111;
							line-height: 18px;
							text-align:left;
						}
						.subtext {
							font-family: Arial, Helvetica, sans-serif;
							font-size: 12px;
							font-weight: normal;
							color: #aaaaaa;
							line-height: 16px;
							text-align: center;
						}
						.headingtitletwo {
							font-family: \'Open Sans\', sans-serif;
							font-size: 18px;
							font-weight: 600;
							color: #959595;
							line-height: 24px;
							text-align:left;
						}
						.headingtitlethree {
							font-family: \'Open Sans\', sans-serif;
							font-size: 24px;
							font-weight: 600;
							color: #959595;
							line-height: 28px;
							text-align: center;
						}
						.rating {
							font-family: \'Open Sans\', sans-serif;
							font-size: 36px;
							font-weight: 700;
							color: #959595;
						}
						.footertitle {
							font-family: \'Open Sans\', sans-serif;
							font-size: 18px;
							font-weight: 600;
							color: #ffffff;
							line-height: 24px;
							text-align:left;
						}
						.footertext {
							font-family: \'Open Sans\', sans-serif;
							font-size: 12px;
							font-weight: normal;
							color: #a2a2a2;
							line-height: 18px;
							text-align:left;
							padding-top:10px;
						}
						.copyright {
							font-family: Arial, Helvetica, sans-serif;
							font-size: 11px;
							font-weight: normal;
							color: #dddddd;
							line-height: 15px;
							text-align: center;
						}
						a.footerlink:link {
							font-family: \'Open Sans\', sans-serif;
							font-size: 13px;
							font-weight: normal;
							color: #ffffff;
							text-decoration: none;
						}
						a.footerlink:hover {
							font-family: \'Open Sans\', sans-serif;
							font-size: 13px;
							font-weight: normal;
							color: #ffffff;
							text-decoration: underline;
						}
						a.footerlink:active {
							font-family: \'Open Sans\', sans-serif;
							font-size: 13px;
							font-weight: normal;
							color: #ffffff;
							text-decoration: underline;
						}
						a.footerlink:visited {
							font-family: \'Open Sans\', sans-serif;
							font-size: 13px;
							font-weight: normal;
							color: #ffffff;
							text-decoration: none;
						}

						@media only screen and (max-width:640px)

						{
							body{width:auto!important;}
							.main{width:446px !important;}
							.compare-image{width: 390px !important; display:block;}
							.compare-details{width: 390px !important; display:block; padding: 20px 0px 0px 0px !important;}
							.footershadow{padding: 0px 75px !important;}
							.footertextright{padding-top: 10px !important; padding-right: 0px !important;}
							.footercopyright{padding: 20px 25px !important;}
						}

						@media only screen and (max-width:450px)
						{
							body{width:auto!important;}
							.main{width:320px !important;}
							.compare-image{width: 264px !important; display:block;}
							.compare-details{width: 264px !important; display:block; padding: 20px 0px 0px 0px !important;}
							.footershadow{padding: 0px 25px !important;}
							.footertextright{padding-top: 10px !important; padding-right: 0px !important;}
							.footercopyright{padding: 20px 25px !important;}
						}

						</style>

						<!-- Internet Explorer fix -->
						<!--[if IE]>
						<style type="text/css">

						@media only screen and (max-width:640px)
						{
							.compare-image{width: 390px !important; float:left; display:block;}
							.compare-details{width: 390px !important; float:left; display:block; padding: 20px 0px 0px 0px !important;}
						}

						@media only screen and (max-width:450px)
						{
							.compare-image{width: 264px !important; float:left; display:block;}
							.compare-details{width: 264px !important; float:left; display:block; padding: 20px 0px 0px 0px !important;}
						}

						</style>
						<![endif]-->
						<!-- / Internet Explorer fix -->

						</head>

						<body>
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="contentbg">
							<tr>
							<td valign="top">
							<!--Table Start-->
							<table width="606" border="0" align="center" cellpadding="0" cellspacing="0" class="main" style="margin-top:10px;">
								<tr>
								<td valign="top" bgcolor="#e8e8e8" style="padding:0px 1px; border-top:1px solid #e8e8e8;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
									<td valign="top" bgcolor="#FFFFFF">

									<!--Header Start-->
									<table width="100%" border="0" cellspacing="0" cellpadding="0">
										<tr>
										<td valign="top" style="padding:15px 0px;">

										<!--Logo and Social Start-->
										<table width="180" border="0" align="left" cellpadding="0" cellspacing="0">
											<tr>
											<td valign="top"><a href="#" target="_blank"><img mc:edit="logo" src="http://' . $_SERVER['HTTP_HOST'] . '' . $organisation_logo . '" width="150" height="50" alt="logo"/></a></td>
											</tr>
										</table>
										<!--Logo and Social End-->

										</td>
										</tr>
									</table>
									<!--Header End-->

									</td>
									</tr>
									<tr>
									<td valign="top" bgcolor="#FFFFFF">
									<!--Content Start-->
									<table width="100%" border="0" cellspacing="0" cellpadding="0">
										<tr>
										<td valign="top">
										<!--Main Content Start-->
										<table width="100%" border="0" cellspacing="0" cellpadding="0" style=" border-bottom:1px solid #e8e8e8;">
											<tr>
											<td align="left" valign="top" bgcolor="#ffffff" mc:edit="main-text" style="padding:10px 20px; border-top:1px solid #e8e8e8" class="maintext">' . $subject . '</td>
											</tr>
											<tr>
											<td valign="top" bgcolor="#ffffff" style="padding:10px 20px;" mc:edit="main-content" class="textdark" align="left">';


		$bottomhtml = '</td></td></table></td>
							</tr>
						</table>
						<!--Content End-->
						</td>
						</tr>
					</table>
					</td>

					</td>
					<td>


					</tr>
				</table>
				<!--Table End-->

				</td>
				</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<table width="606" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
			<tr>
			<td align="left" class="footertext">
				&copy; ' . date('Y') . ' ' . $organisation_name . ' . All Rights Reserved.</p>
			</td>
			</table>
				</table>
			</body>
			</html>
			';

		$body = $tophtml . $body . $bottomhtml;

		// Instantiation and passing `true` enables exceptions
		$mail = new PHPMailer(true);
		//notification history
		try {
			// Enable verbose debug output
			//$mail->SMTPDebug = 2;
			if (strlen($apsettings->getSmtpHost()) && $apsettings->getSmtpEnable()) {
				$mail->isSMTP();
				// Specify main and backup SMTP servers
				$mail->Host = $apsettings->getSmtpHost();
				$mail->Port = $apsettings->getSmtpPort();
				$mail->SMTPAuth = $apsettings->getSmtpAuth();
				$mail->Username = $apsettings->getSmtpUsername();
				$mail->Password = $apsettings->getSmtpPassword();
				$mail->SMTPSecure = $apsettings->getSmtpSecure();
			}
			$mail->setFrom($organisation_email, $organisation_name);
			$mail->addAddress($to, $username);
			$mail->addReplyTo($organisation_email, $organisation_name);
			// Attachments
			//$mail->addAttachment('/var/tmp/file.tar.gz'); 
			// Content
			// Set email format to HTML
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $body;
			//$mail->AltBody='';
			$mail->send();
			//notification history
			$ntf_arch = new NotificationHistory();
			if (strlen($userid)) {
				$ntf_arch->setUserId($userid);
			}
			$ntf_arch->setNotification($subject);
			$ntf_arch->setNotificationType('email');
			$ntf_arch->setSentOn(date('Y-m-d'));
			$ntf_arch->setConfirmedReceipt('1');
			$ntf_arch->save();
			error_log("Message sent! ---> daniel 1");
		} catch (Exception $e) {
			error_log("Could not send notification. Mailer error: {$mail->ErrorInfo}");
			//try mail function
			try {
				$headers = "";
				$headers .= "Reply-To: " . $organisation_name . " <" . $organisation_email . ">\r\n";
				$headers .= "Return-Path: " . $organisation_name . " <" . $organisation_email . ">\r\n";
				$headers .= "From: " . $organisation_name . " <" . $organisation_email . ">\r\n";
				$headers .= "Organization: " . $organisation_name . "r\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= "X-Priority: 3\r\n";
				$headers .= "X-Mailer: PHP" . phpversion() . "\r\n";
				//mail($to,$subject,$body,$headers);
				error_log('Mail sent!');
				$ntf_arch = new NotificationHistory();
				if (strlen($userid)) {
					$ntf_arch->setUserId($userid);
				}
				$ntf_arch->setNotification($subject);
				$ntf_arch->setNotificationType('email');
				$ntf_arch->setSentOn(date('Y-m-d'));
				$ntf_arch->setConfirmedReceipt('1');
				$ntf_arch->save();
			} catch (Exception $e) {
				$ntf_arch = new NotificationHistory();
				if (strlen($userid)) {
					$ntf_arch->setUserId($userid);
				}
				$ntf_arch->setNotification($subject);
				$ntf_arch->setNotificationType('email');
				$ntf_arch->setSentOn(date('Y-m-d'));
				$ntf_arch->setConfirmedReceipt('0');
				$ntf_arch->save();
				error_log("Mail failed to send! Error:{$e->getMessage()}");
			}

		}
	}

	public function sendsms($receiver, $body)
	{
		$original = $receiver;
		error_log('Sending SMS to (raw input) ---> ' . $original);

		try {
			// Remove all non-digit characters
			$receiver = preg_replace('/\D+/', '', $receiver);

			// Normalize phone number to international format
			if (substr($receiver, 0, 1) === "0") {
				// Replace starting 0 with country code
				$receiver = sfConfig::get('app_country_code') . substr($receiver, 1);
			} elseif (substr($receiver, 0, 3) === "254") {
				// Already in correct format, do nothing
			} elseif (substr($receiver, 0, 4) === "254") {
				// Just a fallback case, already handled
			} elseif (substr($receiver, 0, 4) === "+254") {
				// Strip the plus
				$receiver = substr($receiver, 1);
			} else {
				error_log("Invalid phone number format: " . $original);
				return;
			}

			// Retrieve token from session
			$token = $_SESSION['jambo_token'] ??
				$_SESSION['jambo_token_backend'] ??
				$_SESSION['jambo_backup_token'] ?? null;

			if (empty($token)) {
				error_log("Unable to send SMS because no token was found.");
				return;
			}

			error_log('Sending SMS to (normalized) ---> ' . $receiver);

			$stream = new Stream();
			$stream_response = $stream->sendRequest([
				'url' => sfConfig::get('app_api_jambo_url') . 'api/v1/accounts/send_sms/',
				'method' => 'POST',
				'ssl' => 'default',
				'contentType' => 'json',
				'data' => [
					'phone_number' => $receiver,
					'message' => $body
				],
				'headers' => [
					'Authorization' => "JWT " . $token
				]
			]);

			error_log("SMS response: " . print_r($stream_response, true));

		} catch (Exception $ex) {
			error_log("Error while sending SMS: " . $ex->getMessage());
		}
	}


	public function sendsms_older($receiver, $body)
	{
		error_log('Sending sms to --->1' . $receiver);
		try {
			if (substr($receiver, 0, 1) == "0") {
				//ADD COUNTRY CODE
				$receiver = substr($receiver, 1);
				$receiver = sfConfig::get('app_country_code') . $receiver;
			}

			$token = $_SESSION['jambo_token'];

			if (empty($token)) {
				$token = $_SESSION['jambo_token_backend'];
			}


			if (empty($token)) {
				$token = $_SESSION['jambo_backup_token'];
			}

			if (empty($token)) {

				error_log("Unable to send sms cause the tokens can't be found");
				return;
			}

			error_log('Sending sms to --->2' . $receiver);

			$stream = new Stream();
			$stream_response = $stream->sendRequest(
				[
					'url' => sfConfig::get('app_api_jambo_url') . 'api/v1/accounts/send_sms/',
					'method' => 'POST',  // GET, POST, PUT, DELETE,
					'ssl' => 'default',  // default: ensure SSL verification
					'contentType' => 'json', // text: for sending plain text content to the server
					'data' => [
						'phone_number' => $receiver,
						'message' => $body
					],
					'headers' => [
						// Additional headers to send with the request
						'Authorization' => "JWT " . $token
					]
				]
			);
			error_log(print_r($stream_response, true));

		} catch (Exception $ex) {
			error_log("Encountered an error while sending: " . $ex->getMessage());
		}
	}

	public function queueemail($from, $to, $subject, $body, $userid, $application_id)
	{
		try {
			$q = Doctrine_Query::create()
				->from("NotificationQueue a")
				->where("a.application_id = ?", $application_id)
				->andWhere("a.notification = ?", $body)
				->andWhere("a.notification_type = ?", "email");

			//Don't queue duplicate messages
			if ($q->count() == 0) {
				$notification = new NotificationQueue();
				$notification->setUserId($userid);
				$notification->setNotification($subject);
				$notification->setNotificationType('email');
				$notification->setSentOn('' . date('Y-m-d') . '');
				$notification->setSent('0');
				$notification->setApplicationId($application_id);

				$notification->save();
			}
		} catch (Exception $ex) {
			error_log("Encountered an error while queuing: " . $ex->getMessage());
		}
	}

	public function queuesms($receiver, $body, $userid, $application_id)
	{
		try {
			$q = Doctrine_Query::create()
				->from("NotificationQueue a")
				->where("a.application_id = ?", $application_id)
				->andWhere("a.notification = ?", $body)
				->andWhere("a.notification_type = ?", "sms");

			//Don't queue duplicate messages
			if ($q->count() == 0) {
				$notification = new NotificationQueue();
				$notification->setUserId($userid);
				$notification->setNotification($body);
				$notification->setNotificationType('sms');
				$notification->setSentOn('' . date('Y-m-d') . '');
				$notification->setSent('0');
				$notification->setApplicationId($application_id);

				$notification->save();
			}
		} catch (Exception $ex) {
			error_log("Encountered an error while queuing: " . $ex->getMessage());
		}
	}
}
?>