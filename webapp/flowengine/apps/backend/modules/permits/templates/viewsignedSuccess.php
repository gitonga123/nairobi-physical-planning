<?php

/*
 *  Setup variables
 *  Change these before testing
 */
$api_key = sfConfig::get('app_echo_sign_key');

//for testing document methods not required to start
$document_key = $permit->getDocumentKey(); 

$api_key = sfConfig::get('app_echo_sign_key');
    
$ESLoader = new SplClassLoader('EchoSign', dirname(__FILE__).'/../../../../../lib/vendor');
$ESLoader->register();

$client = new SoapClient(EchoSign\API::getWSDL());
$api = new EchoSign\API($client, $api_key);

$document_key = $permit->getDocumentKey();
$signed_document_key = "";

if($document_key)
{
  try{
      $result = $api->getFormData($document_key);
  }catch(Exception $e){
      print '<h3>An exception occurred:</h3>';
  }
  
  $child_key = explode(",",$result->getFormDataResult->formDataCsv);
  $signed_document_key = str_replace('"','',$child_key[14]);
}

$options = new EchoSign\Options\GetDocumentsOptions;

try{
    $result = $api->getDocuments($signed_document_key, $options);
}catch(Exception $e){
    print '<h3>An exception occurred:</h3>';
}

header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="service.pdf"');
echo $result->getDocumentsResult->documents->DocumentContent->bytes;
exit;
?>
