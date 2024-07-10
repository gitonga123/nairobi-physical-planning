<?php
/**
 *
 * Updates manager classes is used to pull and push updates from and to remote servers/databases
 *
 * Created by Atom Editor.
 * User: Thomas
 * Date: 23/04/2015
 * Time: 10:00
 */

class UpdatesManager
{

  public $curl_request = null;

  //Public constructor for the updates manager class
  public function __construct()
  {
    $this->curl_request = curl_init();
  }

  //Form Validator for the form builder. Will only allow submission of form based on different criteria e.g. records found on remote server
  public function pull_validator($remote_url, $remote_username, $remote_password, $remote_template, $remote_criteria, $remote_value, $remote_post)
  {
    try {
      $remote_results = null;
      $validation_result = false;

      [$results, $error, $http_status] = $this->pull_using_curl($remote_url, $remote_value, $remote_username, $remote_password, $remote_post);

      if (empty($error)) {
        // error_log("Updates Manager -> Pull Info: Remote Results Found for " . $remote_value . ": " . $results);

        $error = null;
        sfContext::getInstance()->getUser()->setAttribute('dataset', $results);
        $values = json_decode($results);

        if ($remote_criteria == "records") {
          //If count is = 0, fail
          if ($values->{'count'} == 0) {
            $error = "Sorry. Could not find your records on the server. Please contact support if you need assistance. " . sfConfig::get('app_support_contacts');
            $validation_result = false;

            if (sizeof($values->{'results'}) > 0) {
              $values->{'records'} = json_decode(json_encode($values->{'results'}), true);

              $validation_result = true;
              error_log("Updates Manager -> Pull Info: New Format Found");
            } elseif ($values->{'Status'} == "Ok") {
              $validation_result = true;
              error_log("Updates Manager -> Pull Info: Car Search Format Found");
            }
          } elseif ($values->{'count'} > 0) {
            $validation_result = true;
          } else {
            $error = "Sorry. Could not find your records on the server. Please contact support if you need assistance. " . sfConfig::get('app_support_contacts');
            $validation_result = false;

            if (sizeof($values->{'results'}) > 0) {
              $values->{'records'} = json_decode(json_encode($values->{'results'}), true);
              $validation_result = true;
              error_log("Updates Manager -> Pull Info: New Format Found");
            } elseif ($values->{'Status'} == "Ok") {
              $validation_result = true;
              error_log("Updates Manager -> Pull Info: Car Search Format Found");
            }
          }
        } else if ($remote_criteria == "norecords") {
          //If count is greater than 0, then pass
          if ($values->{'count'} > 0) {
            $error = "Existing records found on server";
            $validation_result = false;
          } else {
            $validation_result = true;
          }
        } else if ($remote_criteria == "value") {
          $values = json_decode($results, true);
          if (is_array($values)) {
            if ($values['success']) {
              $validation_result = true;
            } else {
              $validation_result = false;
              $error = $values['message'];
            }
          } else {
            //If count is greater than 0, then pass
            if ($remote_value != $results) {
              $error = "No matching records found on server";
              $validation_result = false;
            } else {
              $validation_result = true;
            }
          }
        }
        if ($remote_criteria == "records" || $remote_criteria == 'norecords') {
          foreach ($values->{'records'} as $record) {
            if (is_array($record)) {
              foreach ($record as $key => $value) {
                if (!is_array($value)) {
                  //search template
                  $pos = strpos($remote_template, "{" . $key . "}");
                  if ($pos === false) {
                    continue;
                  } else {
                    //parse
                    $remote_template = str_replace('{' . $key . '}', $value, $remote_template);
                  }
                } else {
                  $result_value = "";
                  foreach ($value as $lkey => $lvalue) {
                    if (is_object($lvalue)) {
                      //$result_value = $result_value.$lvalue;
                    } else {
                      $result_value = $result_value . $lvalue;
                    }
                  }
                  $remote_template = str_replace('{' . $key . '}', $result_value, $remote_template);
                }
              }
            } else {
              //error_log("Remote_URL: ".$record);
            }
          }
        }

        $remote_results = html_entity_decode($remote_template);
        error_log("Updates Manager -> Pull Info: Results found: " . $remote_results);
        if (!empty($remote_results)) {
          return ['test' => $validation_result, 'error' => $remote_results];
        } else {
          return ['test' => $validation_result, 'error' => $error];
        }
      } else {
        error_log("Updates Manager -> Pull Error: " . $error);
        return array('test' => false, 'error' => "Could not validate details at this time. Try again later.");
      }
    } catch (Exception $ex) {
      error_log("Updates Manager -> Pull Error: " . $ex->getMessage());
      return array('test' => false, 'error' => "Could not validate details at this time. Try again later.");
    }
  }

  private function pull_using_curl($remote_url, $remote_value, $remote_username, $remote_password, $remote_post)
  {
    $pos = strpos($remote_url, '$value');
    if ($pos === false) {
      //error_log('Updates Manager -> Pull Error: No value ($value) found in remote url');
    } else {
      $remote_url = str_replace('$value', curl_escape($this->curl_request, $remote_value), $remote_url);
      error_log('Updates Manager -> Pull Info: Initiating pull: ' . $remote_url);
    }

    $user = sfContext::getInstance()->getUser();

    if ($user->isAuthenticated()) {
      $username = $user->getUsername();

      $remote_url = $this->appendQueryParam($remote_url, 'username', $username);
    }
    $remote_url = str_replace('$value', curl_escape($this->curl_request, $remote_value), $remote_url);
    error_log('Updates Manager -> Pull Info: Initiating pull: ' . $remote_url);
    curl_setopt($this->curl_request, CURLOPT_URL, $remote_url);
    curl_setopt($this->curl_request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->curl_request, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($this->curl_request, CURLOPT_HTTPHEADER, array('Expect:'));
    if (!empty($remote_username) && !empty($remote_password)) {
      curl_setopt($this->curl_request, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      curl_setopt($this->curl_request, CURLOPT_USERPWD, "$remote_username:$remote_password");
    } else {
      error_log('Updates Manager -> Pull Info: No HTTP Authentication');
    }

    if ($remote_post != "") {
      if ($this->find_string('$value', $remote_post)) {
        $remote_post = str_replace('$value', curl_escape($this->curl_request, $remote_value), $remote_post);
      }

      if ($this->find_string('$sf_username', $remote_post)) {
        if ($_SESSION['userid']) {
          $q = Doctrine_Query::create()
            ->from("SfGuardUser a")
            ->where("a.id = ?", $_SESSION['userid']);
          $user = $q->fetchOne();

          if ($user) {
            $remote_post = str_replace('$sf_username', $user->getUsername(), $remote_post);
          }
        } else {
          $remote_post = str_replace('$sf_username', sfContext::getInstance()->getUser()->getUsername(), $remote_post);
        }
      }

      if ($this->find_string('$invoice_number', $remote_post)) {
        $remote_post = str_replace('$invoice_number', "", $remote_post);
      }

      $post_fields = array();
      $fields = explode('&', $remote_post);

      foreach ($fields as $field) {
        $key_values = explode("=", $field);
        $post_fields[$key_values[0]] = $key_values[1];
      }

      curl_setopt($this->curl_request, CURLOPT_POSTFIELDS, $post_fields);
      // error_log('Updates Manager -> Remote Post Found: ' . implode('&', $post_fields));
    }

    $results = curl_exec($this->curl_request);

    $results = substr($results, strpos($results, '{"Status'), strlen($results));

    $error = curl_error($this->curl_request);

    $http_status = curl_getinfo($this->curl_request, CURLINFO_HTTP_CODE);

    return [$results, $error, $http_status];
  }

  //Form Validator for the form builder. Will only allow submission of form based on different criteria e.g. records found on remote server
  public function pull_raw_results($remote_url, $remote_username, $remote_password, $remote_value)
  {
    try {
      $remote_results = null;
      $validation_result = false;

      $pos = strpos($remote_url, '$value');

      if ($pos === false) {
        //error_log('Updates Manager -> Pull Error: No value ($value) found in remote url');
      } else {
        $remote_url = str_replace('$value', curl_escape($this->curl_request, $remote_value), $remote_url);
        error_log('Updates Manager -> Pull Info: Initiating pull: ' . $remote_url);
      }

      curl_setopt($this->curl_request, CURLOPT_URL, $remote_url);
      curl_setopt($this->curl_request, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($this->curl_request, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($this->curl_request, CURLOPT_HTTPHEADER, array('Expect:'));

      //If username and password are set, use http authentication
      if (!empty($remote_username) && !empty($remote_password)) {
        curl_setopt($this->curl_request, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($this->curl_request, CURLOPT_USERPWD, "$remote_username:$remote_password");
      } else {
        error_log('Updates Manager -> Pull Info: No HTTP Authentication');
      }

      $results = curl_exec($this->curl_request);

      $error = curl_error($this->curl_request);

      $http_status = curl_getinfo($this->curl_request, CURLINFO_HTTP_CODE);


      curl_close($this->curl_request);

      if (empty($error)) {
        error_log("Updates Manager -> Permit Update Pull Success: " . $results);
        $values = json_decode($results, true);

        return $values;
      } else {
        error_log("Updates Manager -> Permit Update Pull Error: " . $error);
        return false;
      }
    } catch (Exception $ex) {
      error_log("Updates Manager -> Pull Error: " . $ex);
    }
  }

  //Pulling updates from a remote database. This occurs during form submission and is not linked to a permit nor are the results stored locally (yet)
  public function pull_update($remote_url, $remote_username, $remote_password, $remote_template, $remote_criteria, $remote_value)
  {
    try {
      $remote_results = null;

      $pos = strpos($remote_url, '$value');

      if ($pos === false) {
        //error_log('Updates Manager -> Pull Error: No value ($value) found in remote url');
      } else {
        $remote_url = str_replace('$value', curl_escape($this->curl_request, $remote_value), $remote_url);
        error_log('Updates Manager -> Pull Info: Initiating pull: -----> ' . $remote_url);
      }

      curl_setopt($this->curl_request, CURLOPT_URL, $remote_url);
      curl_setopt($this->curl_request, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($this->curl_request, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($this->curl_request, CURLOPT_HTTPHEADER, array('Expect:'));

      //If username and password are set, use http authentication
      if (!empty($remote_username) && !empty($remote_password)) {
        curl_setopt($this->curl_request, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($this->curl_request, CURLOPT_USERPWD, "$remote_username:$remote_password");
      } else {
        error_log('Updates Manager -> Pull Info: No HTTP Authentication');
      }

      $results = curl_exec($this->curl_request);
      $error = curl_error($this->curl_request);

      $http_status = curl_getinfo($this->curl_request, CURLINFO_HTTP_CODE);

      if (empty($error)) {
        // error_log("Updates Manager -> Pull Info: Remote Results Found for " . $remote_value . ": " . $results);

        $values = json_decode($results);
        if ($remote_criteria == "records") {
          //If count is = 0, fail
          if ($values->{'count'} == 0) {
            $error = "No records found on server";

            if (sizeof($values->{'results'}) > 0) {
              $values->{'records'} = json_decode(json_encode($values->{'results'}), true);

              $error = "";
              error_log("Updates Manager -> Pull Info: New Format Found");
            }
          }
        } else if ($remote_criteria == "norecords") {
          //If count is greater than 0, then pass
          if ($values->{'count'} > 0) {
            $error = "Existing records found on server";
          }
        } else if ($remote_criteria == "value") {
          //If count is greater than 0, then pass
          if ($remote_value != $results) {
            $error = "No matching records found on server";
          }
        }

        foreach ($values->{'records'} as $record) {
          foreach ($record as $key => $value) {
            if (!is_array($value)) {
              //search template
              $pos = strpos($remote_template, "{" . $key . "}");
              if ($pos === false) {
                continue;
              } else {
                //parse
                $remote_template = str_replace('{' . $key . '}', $value, $remote_template);
              }
            } else {
              $result_value = "";
              foreach ($value as $lkey => $lvalue) {
                if (is_object($lvalue)) {
                  //$result_value = $result_value.$lvalue;
                } else {
                  $result_value = $result_value . $lvalue;
                }
              }
              $remote_template = str_replace('{' . $key . '}', $result_value, $remote_template);
            }
          }
        }

        if ($results != '{"count":0,"records":[]}') {
          $remote_results = html_entity_decode($remote_template);
        } else {
          $remote_results = "";
        }

        error_log("Updates Manager -> Pull Info: Results found: " . $remote_results);
      } else {
        // error_log("Updates Manager -> Pull Error: " . $error);
      }

      curl_close($this->curl_request);

      return $remote_results;
    } catch (Exception $ex) {
      error_log("Updates Manager to note -> Pull Error: " . $ex->getMessage());
    }
  }

  //Pulling updates from a remote database. No templating
  public function pull_update_raw($remote_url, $remote_username, $remote_password, $remote_template, $remote_criteria, $remote_value, $remote_post, $client_username)
  {
    try {
      ini_set('max_execution_time', 600);
      $remote_results = null;

      $pos = strpos($remote_url, '$value');

      if ($pos === false) {
        //error_log('Updates Manager -> Pull Error: No value ($value) found in remote url');
      } else {
        $remote_url = str_replace('$value', curl_escape($this->curl_request, $remote_value), $remote_url);
        error_log('Updates Manager -> Pull Info 2: Initiating pull: ' . $remote_url);
      }

      if ($remote_post != "") {
        if ($this->find_string('$value', $remote_post)) {
          $remote_post = str_replace('$value', curl_escape($this->curl_request, $remote_value), $remote_post);
        }

        if ($this->find_string('$sf_username', $remote_post)) {
          $remote_post = str_replace('$sf_username', $client_username, $remote_post);
        }

        if ($this->find_string('$invoice_number', $remote_post)) {
          $remote_post = str_replace('$invoice_number', "", $remote_post);
        }

        $post_fields = array();
        $fields = explode('&', $remote_post);

        foreach ($fields as $field) {
          $key_values = explode("=", $field);
          $post_fields[trim($key_values[0])] = trim($key_values[1]);
        }

        //$process = curl_init("http://154.70.39.109:82/insurance/_linker1.php"); //url to access
        $process = curl_init($remote_url); //url to access
        curl_setopt($process, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $results = curl_exec($process);

        $results = substr($results, strpos($results, '{"Status'), strlen($results));

        error_log('Updates Manager -> Remote Post 2 Found: ' . implode('&', $post_fields));

        error_log('Updates Manager -> Pull Info 2 Results: ' . $results . '.');

        $error = curl_error($this->curl_request);

        if (empty($error)) {
          return $results;
        } else {
          return "error";
          error_log("Updates Manager -> Pull Error 2: " . $error);
        }
      } else {
        curl_setopt($this->curl_request, CURLOPT_URL, $remote_url);
        curl_setopt($this->curl_request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl_request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl_request, CURLOPT_HTTPHEADER, array('Expect:'));

        //If username and password are set, use http authentication
        if (!empty($remote_username) && !empty($remote_password)) {
          curl_setopt($this->curl_request, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
          curl_setopt($this->curl_request, CURLOPT_USERPWD, "$remote_username:$remote_password");
        } else {
          error_log('Updates Manager -> Pull Info 2: No HTTP Authentication');
        }

        $results = curl_exec($this->curl_request);

        error_log('Updates Manager -> Pull Info 2 Results: ' . $results . '.');

        $error = curl_error($this->curl_request);

        if (empty($error)) {
          return $results;
        } else {
          return "error";
          error_log("Updates Manager -> Pull Error 2: " . $error);
        }
      }

      curl_close($this->curl_request);

      return $results;
    } catch (Exception $ex) {
      error_log("Updates Manager -> Pull Error 2: " . $ex);
    }
  }

  //Check if a permit needs to push data to a remote database
  public function needs_push($permit_id)
  {
    //Retrieve the permit object
    $q = Doctrine_Query::create()
      ->from("SavedPermit a")
      ->where("a.id = ?", $permit_id);
    $permit = $q->fetchOne();

    if ($permit) {
      //Retrieve the permit template which has all the remote update configurations
      $q = Doctrine_Query::create()
        ->from("Permits a")
        ->where("a.id = ?", $permit->getTypeId());
      $permit_template = $q->fetchOne();

      if ($permit_template) {
        if ($permit_template->getRemoteUrl()) {
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  //Check if a permit needs to push data to a remote database
  public function needs_archive_push($permit_id)
  {
    //Retrieve the permit object
    $q = Doctrine_Query::create()
      ->from("SavedPermitArchive a")
      ->where("a.id = ?", $permit_id);
    $permit = $q->fetchOne();

    if ($permit) {
      //Retrieve the permit template which has all the remote update configurations
      $q = Doctrine_Query::create()
        ->from("Permits a")
        ->where("a.id = ?", $permit->getTypeId());
      $permit_template = $q->fetchOne();

      if ($permit_template) {
        if ($permit_template->getRemoteUrl()) {
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  //Pushing updates to a remote database. This occurs during permit generation. The results are stored locally.
  public function push_update($permit_id)
  {
    try {
      $templateparser = new TemplateParser();

      //Retrieve the permit object
      $q = Doctrine_Query::create()
        ->from("SavedPermit a")
        ->where("a.id = ?", $permit_id);
      $permit = $q->fetchOne();

      if ($permit) {
        //Retrieve the permit template which has all the remote update configurations
        $q = Doctrine_Query::create()
          ->from("Permits a")
          ->where("a.id = ?", $permit->getTypeId());
        $permit_template = $q->fetchOne();

        if ($permit_template) {
          if ($permit_template->getRemoteUrl()) {
            $remote_url = $permit_template->getRemoteUrl();
            $remote_fields = $permit_template->getRemoteField();
            $remote_username = $permit_template->getRemoteUsername();
            $remote_password = $permit_template->getRemotePassword();

            //parse the url and field data through a template parser
            $remote_url = $templateparser->parseURL($permit->getFormEntry()->getId(), $remote_url);

            $remote_fields = $templateparser->parseRemote($permit->getFormEntry()->getId(), $permit->getFormEntry()->getFormId(), $permit->getFormEntry()->getEntryId(), $permit->getId(), $remote_fields);

            //Handle quotes (')

            //Either #1: url_encode the fields to prevent post errors
            //$remote_fields = urlencode($remote_fields);

            //Or #2: directly replace quotes with nothing
            //$remote_fields = str_replace("'", "", $remote_fields);

            // Custom Parsers
            // 1. Map specific dropdown fields to values
            if (sfConfig::get('app_kra_parser') == "enabled") {
              $remote_fields = $this->parseKRA($remote_fields);
            }

            error_log("Updates Manager -> Push Info: Remote URL Parsed for " . $permit->getFormEntry()->getApplicationId() . " : " . $remote_url);

            error_log("Updates Manager -> Push Info: Remote Fields Parsed for " . $permit->getFormEntry()->getApplicationId() . " : " . $remote_fields);

            $fields = explode($remote_fields, '=');
            $field_count = count($fields);

            curl_setopt($this->curl_request, CURLOPT_URL, $remote_url);
            curl_setopt($this->curl_request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl_request, CURLOPT_FOLLOWLOCATION, true);

            if (!empty($remote_username) && !empty($remote_password)) {
              curl_setopt($this->curl_request, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
              curl_setopt($this->curl_request, CURLOPT_USERPWD, "$remote_username:$remote_password");
            }
            curl_setopt($this->curl_request, CURLOPT_ENCODING, "");

            if ($permit_template->getRemoteRequestType() == 'get') {
              //Send requests as get
            } else {
              //Send requests as post
              curl_setopt($this->curl_request, CURLOPT_POST, $field_count);
              curl_setopt($this->curl_request, CURLOPT_POSTFIELDS, $remote_fields);
            }

            $results = curl_exec($this->curl_request);

            error_log("Updates Manager -> Push Info: Remote update results for " . $permit->getFormEntry()->getApplicationId() . ": " . $results);

            if ($error = curl_error($this->curl_request)) {
              error_log("Updates Manager -> Push Error: Remote update error for " . $permit->getFormEntry()->getApplicationId() . ": " . $error);
            } else {
              $prefix_folder = dirname(__FILE__) . "/vendor/form_builder/";
              require_once ($prefix_folder . 'includes/init.php');

              require_once ($prefix_folder . '../../../config/form_builder_config.php');
              require_once ($prefix_folder . 'includes/db-core.php');
              require_once ($prefix_folder . 'includes/helper-functions.php');

              $dbh = mf_connect_db();
              $mf_settings = mf_get_settings($dbh);

              $sql = "UPDATE saved_permit SET remote_result = '" . addslashes($results) . "' WHERE id = " . $permit->getId();
              $params = array();
              $sth = mf_do_query($sql, $params, $dbh);
            }

            curl_close($this->curl_request);

          } else {
            error_log("Updates Manager -> Push Error: No Remote URL Found");
          }
        } else {
          error_log("Updates Manager -> Push Error: Permit Template " . $permit->getTypeId() . " does not exist");
        }
      } else {
        error_log("Updates Manager -> Push Error: Permit ID " . $permit_id . " does not exist");
      }
    } catch (Exception $ex) {
      error_log("Updates Manager -> Push Error: " . $ex);
    }
  }

  //Pushing updates to a remote database. This occurs during permit generation. The results are stored locally.
  public function push_archive_update($permit_id)
  {
    try {
      $permit_manager = new PermitManager();
      $templateparser = new TemplateParser();

      //Retrieve the permit object
      $q = Doctrine_Query::create()
        ->from("SavedPermitArchive a")
        ->where("a.id = ?", $permit_id);
      $permit = $q->fetchOne();

      if ($permit) {
        //Retrieve the permit template which has all the remote update configurations
        $q = Doctrine_Query::create()
          ->from("Permits a")
          ->where("a.id = ?", $permit->getTypeId());
        $permit_template = $q->fetchOne();

        if ($permit_template) {
          if ($permit_template->getRemoteUrl()) {
            $remote_url = $permit_template->getRemoteUrl();
            $remote_fields = $permit_template->getRemoteField();
            $remote_username = $permit_template->getRemoteUsername();
            $remote_password = $permit_template->getRemotePassword();

            //parse the url and field data through a template parser
            $remote_url = $templateparser->parseRemoteArchive($permit->getApplication()->getId(), $permit->getApplication()->getFormId(), $permit->getApplication()->getEntryId(), $permit->getId(), $remote_url);

            $remote_fields = $templateparser->parseRemoteArchive($permit->getApplication()->getId(), $permit->getApplication()->getFormId(), $permit->getApplication()->getEntryId(), $permit->getId(), $remote_fields);

            error_log("Updates Manager -> Push Info: Remote URL Parsed for " . $permit->getApplication()->getApplicationId() . " : " . $remote_url);

            error_log("Updates Manager -> Push Info: Remote Fields Parsed for " . $permit->getApplication()->getApplicationId() . " : " . $remote_fields);

            $fields = explode($remote_fields, '=');
            $field_count = count($fields);

            curl_setopt($this->curl_request, CURLOPT_URL, $remote_url);
            curl_setopt($this->curl_request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl_request, CURLOPT_FOLLOWLOCATION, true);

            if (!empty($remote_username) && !empty($remote_password)) {
              curl_setopt($this->curl_request, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
              curl_setopt($this->curl_request, CURLOPT_USERPWD, "$remote_username:$remote_password");
            }
            curl_setopt($this->curl_request, CURLOPT_ENCODING, "");

            if ($permit_template->getRemoteRequestType() == 'get') {
              //Send requests as get
            } else {
              //Send requests as post
              curl_setopt($this->curl_request, CURLOPT_POST, $field_count);
              curl_setopt($this->curl_request, CURLOPT_POSTFIELDS, $remote_fields);
            }

            $results = curl_exec($this->curl_request);

            error_log("Updates Manager -> Push Info: Remote update results for " . $permit->getFormEntry()->getApplicationId() . ": " . $results);

            if ($error = curl_error($this->curl_request)) {
              error_log("Updates Manager -> Push Error: Remote update error for " . $permit->getFormEntry()->getApplicationId() . ": " . $error);
            } else {
              $dbconn = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
              mysql_select_db(sfConfig::get('app_mysql_db'), $dbconn);

              $sql = "UPDATE saved_permit SET remote_result = '" . mysqli_real_escape_string($results) . "' WHERE id = " . $permit->getId();
              mysql_query($sql, $dbconn);
            }

            curl_close($this->curl_request);

          } else {
            error_log("Updates Manager -> Push Error: No Remote URL Found");
          }
        } else {
          error_log("Updates Manager -> Push Error: Permit Template " . $permit->getTypeId() . " does not exist");
        }
      } else {
        error_log("Updates Manager -> Push Error: Permit ID " . $permit_id . " does not exist");
      }
    } catch (Exception $ex) {
      error_log("Updates Manager -> Push Error: " . $ex);
    }
  }

  //Check if a permit needs to pull data from a remote database
  public function needs_pull($permit_id)
  {
    //Retrieve the permit object
    $q = Doctrine_Query::create()
      ->from("SavedPermit a")
      ->where("a.id = ?", $permit_id);
    $permit = $q->fetchOne();

    if ($permit) {
      $q = Doctrine_Query::create()
        ->from("ApFormElements a")
        ->where("a.form_id = ?", $permit->getFormEntry()->getFormId())
        ->andWhere("a.element_remote_post <> ?", "");
      $remote_validators = $q->count();
      if ($remote_validators > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function parseKRA($remote_fields)
  {
    $remote_fields = json_encode($remote_fields);
    $table_name = sfConfig::get('app_kra_table');
    $option_id = sfConfig::get('app_kra_value_field');
    $option_text = sfConfig::get('app_kra_text_field');

    $db_connection = mysql_connect(sfConfig::get('app_mysql_host'), sfConfig::get('app_mysql_user'), sfConfig::get('app_mysql_pass'));
    mysql_select_db(sfConfig::get('app_mysql_db'), $db_connection);

    $sql = "SELECT * FROM " . $table_name;
    $results = mysql_query($sql, $db_connection);

    while ($row = mysql_fetch_assoc($results)) {
      if ($this->find_string($row[$option_id], $remote_fields)) {
        $remote_fields = str_replace($row[$option_text], $row[$option_id], $remote_fields);
      }
    }

    return json_decode($remote_fields);
  }

  public function find_string($needle, $haystack)
  {
    $pos = strpos($haystack, $needle);

    if ($pos === false) {
      return false;
    } else {
      return true;
    }
  }

  function appendQueryParam($url, $key, $value)
  {
    $urlComponents = parse_url($url);

    if (isset($urlComponents['query'])) {
      parse_str($urlComponents['query'], $queryParams);
    } else {
      $queryParams = [];
    }

    $queryParams[$key] = $value;
    $urlComponents['query'] = http_build_query($queryParams);

    return $this->buildUrl($urlComponents);
  }

  function buildUrl($urlComponents)
  {
    $url = '';

    if (isset($urlComponents['scheme'])) {
      $url .= $urlComponents['scheme'] . '://';
    }

    if (isset($urlComponents['host'])) {
      $url .= $urlComponents['host'];
    }

    if (isset($urlComponents['port'])) {
      $url .= ':' . $urlComponents['port'];
    }

    if (isset($urlComponents['path'])) {
      $url .= $urlComponents['path'];
    }

    if (isset($urlComponents['query'])) {
      $url .= '?' . $urlComponents['query'];
    }

    if (isset($urlComponents['fragment'])) {
      $url .= '#' . $urlComponents['fragment'];
    }

    return $url;
  }

}
