#!/usr/bin/php
<?php

error_log("Loading rabbit worker ... ");

$message = $argv[1];
$original = base64_decode($message);


$received_data = json_decode($original, true);

// var_dump($received_data);

try {
    sendDetails($received_data['data']);
} catch (Exception $ex) {
    updateZizResponseFile(["error" => $ex->getMessage()]);
    updateQueueManagerOnError($received_data['data']);
}

function sendDetails($payment_details)
{
    require_once dirname(__FILE__) . '/../config/ProjectConfiguration.class.php';
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    new sfDatabaseManager($configuration);
    $api_token_obj = new ApiCalls(); 
    $api_token = $api_token_obj->getApiTokenForRabbitMQCall();
    $payment_details['headers']['Authorization'] = "Bearer {$api_token}";
    $stream = new Stream();
    $new_payment_details = (array) $payment_details;
    $stream_response = $stream->sendRequest($new_payment_details);
    error_log('-------STREAM RESPONSE---------');
    error_log(var_export($stream_response,true));

    if ($stream_response->status != 200 || $stream_response->content['status'] != "01") {
        updateQueueManagerOnError($payment_details);
        updateZizResponseFile($payment_details);
        updateZizResponseFile($stream_response);
    } else if ($stream_response->status == 200 && $stream_response->content['status'] != "00") {
        updateZizResponseFile($payment_details);
        updateZizResponseFile($stream_response);
        
    }
    return true;
}

function updateQueueManagerOnError($new_payment_details)
{
    require_once dirname(__FILE__) . '/../config/ProjectConfiguration.class.php';
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
    usleep(120000000);
    $queue_data = array("data" => $new_payment_details);
    $queuemanager = new QueueManager();
    $queuemanager->queue_data($queue_data);
}

function updateZizResponseFile($message)
{
    $myfile = fopen("zizi_responses.log", "a+") or die("Unable to open file!");
    $txt = var_export($message, true);
    fwrite($myfile, $txt);
    fwrite($myfile, "\n");
    fclose($myfile);
}
?>
