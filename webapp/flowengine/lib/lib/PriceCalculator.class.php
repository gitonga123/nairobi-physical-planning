<?php
class PriceCalculator
{
  public function __construct()
  {

  }

  public function AGStampDuty($field_value)
  {
    return (($field_value * 0.01) + 2020);
  }

  public function LandsStampDuty($field_value)
  {
    return $field_value * 0.04;
  }

  public function LandsStampDuty2($field_value)
  {
    return $field_value * 0.02;
  }

  /**
   * Retrieve a fee from dataflow
   *
   * $dataset - The dataset containing the records
   * $remote_username - HTTP authentication username
   * $remote_password - HTTP authentication password
   * $search_field - The field to search
   * $retrieve_field - The field that contains the fee
   * $value - The value from the application to use in the search field
   *
   * */
  public function DataflowWithPenaltiesFixedCalculator($remote_url, $remote_username, $remote_password, $retrieve_field)
  {
    //Replace the fieldset in the remote url with the fieldset value from session 
    if(sfContext::getInstance()->getUser()->getAttribute('dataset'))
    {
      $dataset = json_decode(sfContext::getInstance()->getUser()->getAttribute('dataset'), true);
      $record_data = $dataset['records'][0];

      //parse remote url with record data 
      $remote_url = templateparser::parseWithDust($remote_url, $record_data);
    }

    error_log("Dataflow URL: ".$remote_url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

    //If username and password are set, use http authentication
    if(!empty($remote_username) && !empty($remote_password))
    {
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      curl_setopt($ch, CURLOPT_USERPWD, "$remote_username:$remote_password");
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);

    if(empty($error))
    {
      $dataset = json_decode($response, true);
      $record_data = $dataset['records'][0];
      
      $remote_url = templateparser::parseWithDust($remote_url, $record_data);

      //Add date fields for penality tables
      $date_data = array();
      $date_data['m'] = date("m") * 1;
      $date_data['d'] = date("d") * 1;
      $date_data['Y'] = date("Y");

      $retrieve_field  = templateparser::parseWithDust($retrieve_field, $date_data);

      error_log("Dataflow Price Calculator: Sent - ".$remote_url.", Response: ".$record_data[$retrieve_field]);

      return $record_data[$retrieve_field];
    }
    else 
    {
      error_log("Dataflow Price Calculator: Sent - ".$remote_url.", Response: ".$response);
      return 0;
    }
    
  }

  /**
   * Retrieve a fee from dataflow
   *
   * $dataset - The dataset containing the records
   * $remote_username - HTTP authentication username
   * $remote_password - HTTP authentication password
   * $search_field - The field to search
   * $retrieve_field - The field that contains the fee
   * $value - The value from the application to use in the search field
   *
   * */
  public function DataflowWithPenaltiesDynamicCalculator($remote_url, $dataset, $remote_username, $remote_password, $search_field, $retrieve_field, $value)
  {
    //Replace the fieldset in the remote url with the fieldset value from session 
    if(sfContext::getInstance()->getUser()->getAttribute('dataset'))
    {
      $dataset = json_decode(sfContext::getInstance()->getUser()->getAttribute('dataset'), true);
      $record_data = $dataset['records'][0];

      //parse remote url with record data 
      $remote_url = templateparser::parseWithDust($remote_url, $record_data);
    }

    error_log("Dataflow URL: ".$remote_url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

    //If username and password are set, use http authentication
    if(!empty($remote_username) && !empty($remote_password))
    {
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      curl_setopt($ch, CURLOPT_USERPWD, "$remote_username:$remote_password");
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);

    if(empty($error))
    {
      $dataset = json_decode($response, true);
      $record_data = $dataset['records'][0];

      //Add date fields for penality tables
      $date_data = array();
      $date_data['m'] = date("m") * 1;
      $date_data['d'] = date("d") * 1;
      $date_data['Y'] = date("Y");

      $retrieve_field  = templateparser::parseWithDust($retrieve_field, $date_data);

      error_log("Dataflow Price Calculator: Sent - ".$remote_url.", Response: ".$record_data[$retrieve_field]);

      return $record_data[$retrieve_field];
    }
    else 
    {
      error_log("Dataflow Price Calculator: Sent - ".$remote_url.", Response: ".$response);
      return 0;
    }
  }

}
