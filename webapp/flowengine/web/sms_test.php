<?php

$api_key = "a37qFAd78t6f93F9Df8Jx65vZea2zt3f143ivATcB44729etBy8Xcx7c7495TN6e";

$shortcode = "CG_KAJIADO";
$serviceId = '0';
$mobile = "0725601244";
$message = "Test outbound bulk message";

$smsdata = array(
    "api_key" => $api_key,
    "shortcode" => $shortcode,
    "mobile" => $mobile,
    "message" => $message,
    "serviceId" => $serviceId,
    "response_type" => "json",
    );

$smsdata_string = json_encode($smsdata);
echo $smsdata_string . "\n";

$smsURL = "http://sms.crowdcomm.co.ke:7211/sms/v3/sendsms";

//POST
$ch = curl_init($smsURL);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

curl_setopt($ch, CURLOPT_POSTFIELDS, $smsdata_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($smsdata_string))
);

$apiresult = curl_exec($ch);
echo("API Response: $apiresult\n");

if (!$apiresult) {
    die("ERROR on URL[$urls] | error[" . curl_error($ch) . "] | error code[" . curl_errno($ch) . "]\n");
}

curl_close($ch);

?>