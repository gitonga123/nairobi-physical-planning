<?php
class CustomPostValidator
{
  private $form_data;
  private $validation_error;

  /**
  *
  * Fetch all form values in an array
  *
  **/
  public function __construct($form_data)
  {
    if($form_data == null)
    {
      $dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
      mysql_select_db(sfConfig::get('app_mysql_db'),$db_connection);

      $sql = "SELECT * FROM ap_form_17855_review WHERE session_id = '".session_id()."'";
      $results = mysql_query($sql, $dbconn);

      $form_data = array();

      while($form_row = mysql_fetch_assoc($results))
      {
        foreach($form_row as $key => $value)
        {
          $form_data[$key] = array("default_value" => $value);
        }
      }

      $this->form_data = $form_data;

    }
    else
    {
      $this->form_data = $form_data;
    }
  }

  /**
  *
  * Apply Ag Shares 1 validation formula
  *
  * Example of fetching data from form_data
  * e.g $field_a = intval($this->form_data['element_34']['default_value']);
  *
  **/
  public function AgShares1($field_value)
  {
    $total_shares = intval($this->form_data['element_34']['default_value']);

    $directors = json_decode($field_value, true);

    foreach($directors as $director)
    {
      $total_shares = $total_shares - $director['shares_taken'];
    }

    if($total_shares >= 0)
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
  *
  * Apply Ag Shares 2 validation formula
  *
  **/
  public function AgShares2($field_value)
  {
    return true;
  }
}
