<?php

/**
 * Users actions.
 *
 * Reviewers Management Service.
 *
 * @package    backend
 * @subpackage users
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
class usersActions extends sfActions
{
    public function executeBatch(sfWebRequest $request)
    {
        if ($request->getPostParameter('delete')) {
            $q = Doctrine_Query::create()
                ->from("CfUser a")
                ->where('a.nid = ?', $request->getPostParameter('delete'));
            $item = $q->fetchOne();
            if ($item) {
                $item->delete();
            }
        }
    }

    /**
     * Executes 'Checkuser' action
     *
     * Ajax used to check existence of username
     *
     * @param sfRequest $request A request object
     */
    public function executeCheckuser(sfWebRequest $request)
    {
        // add new user
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where('a.Struserid = ?', $request->getPostParameter('username'));
        $existinguser = $q->execute();
        if (sizeof($existinguser) > 0) {
            echo '<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Username is already in use!</strong></div><script language="javascript">document.getElementById("submitbutton").disabled = true;</script>';
            exit;
        } else {
            echo '<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Username is available!</strong></div><script language="javascript">document.getElementById("submitbutton").disabled = false;</script>';
            exit;
        }
    }

    /**
     * Executes 'Reset' action
     *
     * Ajax used to check existence of username
     *
     * @param sfRequest $request A request object
     */
    public function executeReset(sfWebRequest $request)
    {
        // add new user
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where('a.stremail = ?', $request->getParameter('email'));
        $existinguser = $q->fetchOne();
        if ($existinguser) {
            $random_pass = rand(10000, 1000000);
            $random_code = rand(10000, 1000000000);

            $temp_pass = password_hash($random_pass, PASSWORD_BCRYPT);
            $temp_code = md5($random_code);

            $existinguser->setStrtemppassword($temp_pass);
            $existinguser->setStrtoken($temp_code);

            $existinguser->save();

            //Send account recovery email
            $body = "
                Hi {$existinguser->getStrfirstname()} {$existinguser->getStrlastname()}, <br>
                <br>
                You have requested to reset your account password. Use the link below to reset it now: <br>
                <br>
                Temporary Password: {$random_pass}
                <br>
                ---- <br>
                http://" . $_SERVER['HTTP_HOST'] . "/backend.php/login/recover/code/{$temp_code} <br>
                ---- <br>
                <br>
                Thanks,<br>
                " . sfConfig::get('app_organisation_name') . ".<br>
            ";

            error_log("Reset reset url");

            error_log("http://" . $_SERVER['HTTP_HOST'] . "/backend.php/login/recover/code/{$temp_code}");

            $mailnotifications = new mailnotifications();
            $mailnotifications->sendemail(sfConfig::get('app_organisation_email'), $existinguser->getStremail(), "Password Reset", $body);

            $this->success = true;
        } else {
            $this->success = false;
        }
    }

    /**
     * Executes 'Checkemail' action
     *
     * Ajax used to check existence of email
     *
     * @param sfRequest $request A request object
     */
    public function executeCheckemail(sfWebRequest $request)
    {
        // add new user
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where('a.Stremail = ?', $request->getPostParameter('email'));
        $existinguser = $q->execute();
        if (sizeof($existinguser) > 0) {
            echo '<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Email is already in use!</strong></div>';
            exit;
        } else {
            echo '<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Email is available!</strong></div>';
            exit;
        }
    }

    /**
     * Executes 'Checkuser' action
     *
     * Ajax used to check existence of username
     *
     * @param sfRequest $request A request object
     */
    public function executeCheckusermin(sfWebRequest $request)
    {
        // add new user
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where('a.Struserid = ?', $request->getPostParameter('username'));
        $existinguser = $q->execute();
        if (sizeof($existinguser) > 0) {
            echo 'fail';
            exit;
        } else {
            echo 'pass';
            exit;
        }
    }
    /**
     * Executes 'Checkemail' action
     *
     * Ajax used to check existence of email
     *
     * @param sfRequest $request A request object
     */
    public function executeCheckemailmin(sfWebRequest $request)
    {
        // add new user
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where('a.Stremail = ?', $request->getPostParameter('email'));
        $existinguser = $q->execute();
        if (sizeof($existinguser) > 0) {
            echo 'fail';
            exit;
        } else {
            echo 'pass';
            exit;
        }
    }
    /**
     * Executes 'Showuser' action
     *
     * Displays list of reviewers within the selected department
     *
     * @param sfRequest $request A request object
     */
    public function executeShowuser(sfWebRequest $request)
    {
        $this->department  = $request->getParameter("dept");

        if ($request->getParameter("atoggle") != "") {
            $user = Doctrine_Core::getTable("CfUser")->find($request->getParameter("atoggle"));
            if ($user) {

                if ($user->getNaccesslevel() == "0") {
                    $user->setNaccesslevel("1");
                } else {
                    $user->setNaccesslevel("0");
                }
                $user->save();
            }
        }

        $userids = $request->getPostParameter('user', array());
        $departments = $request->getPostParameter('department', array());
        try {
            $x = 0;
            foreach ($userids as $userid) {
                $user = Doctrine_Core::getTable("cfUser")->find($userid);
                if ($user) {
                    $user->setStrdepartment($departments[$x]);
                    $user->save();
                }
                $x++;
            }
        } catch (exception $ex) {
        }
    }

    /**
     * Executes 'Edituser' action
     *
     * Edit reviewer details
     *
     * @param sfRequest $request A request object
     */
    public function executeEdituser(sfWebRequest $request)
    {
        if ($_SESSION['SESSION_CUTEFLOW_USERID'] == $request->getParameter('userid')) {
            //$this->forward('users','myaccount');
        }

        if ($request->getParameter('userid')) {
            $this->userid = $request->getParameter('userid');
        } else {
            $this->userid = "-1";
        }
    }

    /**
     * Executes 'Imitateuser' action
     *
     * Change current login session to selected user
     *
     * @param sfRequest $request A request object
     */
    public function executeImitateuser(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', $request->getParameter('userid'));
        $user = $q->fetchOne();
        if ($user) {
            $this->getUser()->setAttribute('userid', $user->getNid());
            $this->getUser()->setAttribute('username', $user->getStruserid());

            //Backwards compatibility. As long old cuteflow modules still exist.
            $_SESSION['SESSION_CUTEFLOW_USERID'] = $user->getNid();
            $_SESSION['SESSION_CUTEFLOW_USERNAME'] = $user->getStruserid();
        }
        $this->redirect("/backend.php/dashboard");
    }

    /**
     * Executes 'Myaccount' action
     *
     * Allows currently logged in reviewer to manage their account details
     *
     * @param sfRequest $request A request object
     */
    public function executeMyaccount(sfWebRequest $request)
    {
        if ($request->getParameter('userid')) {
            $this->userid = $request->getParameter('userid');
        } else {
            $this->userid = "-1";
        }
    }

    /**
     * Executes 'Viewuser' action
     *
     * Displays full reviewer details
     *
     * @param sfRequest $request A request object
     */
    public function executeViewuser(sfWebRequest $request)
    {
        $this->currentpage = $request->getParameter('currentpage', 1);
        $this->completedpage = $request->getParameter('completepage', 1);
        $this->cancelpage = $request->getParameter('cancelpage', 1);
        $this->auditpage = $request->getParameter('auditpage', 1);

        $this->forward404Unless($reviewer = Doctrine_Core::getTable('CfUser')->find(array($request->getParameter('userid'))), sprintf('The selected reviewer of id (%s) does not exist.', $request->getParameter('userid')));

        $this->reviewer = $reviewer;

        //Update user record if post params are found
        if ($request->getPostParameter("first_name")) {
            //Audit 
            Audit::audit("", "Updated user details for reviewer #" . $this->reviewer->getNid());

            $this->reviewer->setStrfirstname($request->getPostParameter("first_name"));
            $this->reviewer->setStrlastname($request->getPostParameter("last_name"));
            $this->reviewer->setStruserid($request->getPostParameter("id_number"));
            $this->reviewer->setStremail($request->getPostParameter("email"));
            $this->reviewer->setStrphoneMain1($request->getPostParameter("phone_number"));
            $this->reviewer->save();
        }

        if ($request->getPostParameter("new_password") && ($request->getPostParameter("new_password") == $request->getPostParameter("confirm_password"))) {
            //Audit 
            Audit::audit("", "Updated password details for reviewer #" . $this->reviewer->getNid());

            $this->reviewer->setStrpassword(password_hash($request->getPostParameter("new_password"), PASSWORD_BCRYPT));
            $this->reviewer->save();
        }

        if ($request->getPostParameter("country")) {
            //Audit 
            Audit::audit("", "Updated other details for reviewer #" . $this->reviewer->getNid());

            $this->reviewer->setStrcountry($request->getPostParameter("country"));
            $this->reviewer->setStrcity($request->getPostParameter("city"));
            $this->reviewer->setUserdefined1Value($request->getPostParameter("designation"));
            $this->reviewer->setUserdefined2Value($request->getPostParameter("mannumber"));
            $this->reviewer->save();
        }

        if ($request->getPostParameter("department")) {
            //Audit 
            Audit::audit("", "Updated department details for reviewer #" . $this->reviewer->getNid());

            $this->reviewer->setStrdepartment($request->getPostParameter("department"));
            $this->reviewer->save();
        }

        if ($request->getPostParameter("groups")) {
            //Audit 
            Audit::audit("", "Updated group details for reviewer #" . $this->reviewer->getNid());

            $q = Doctrine_Query::Create()
                ->from('mfGuardUserGroup a')
                ->where('a.user_id = ?', $this->reviewer->getNid());
            $usergroups = $q->execute();
            if ($usergroups) {
                foreach ($usergroups as $usergroup) {
                    $usergroup->delete();
                }
            }

            $groups = $request->getPostParameter("groups");
            foreach ($groups as $group) {
                $usergroup = new MfGuardUserGroup();
                $usergroup->setUserId($this->reviewer->getNid());
                $usergroup->setGroupId($group);
                $usergroup->save();
            }
        }

        //Current tasks
        $this->q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.owner_user_id = ?", $this->reviewer->getNid())
            ->andWhere("a.status = ? OR a.status = ?", array(1, 2));
        $this->current_paginator = new sfDoctrinePager('Task', 5);
        $this->current_paginator->setQuery($this->q);
        $this->current_paginator->setPage($this->currentpage);
        $this->current_paginator->init();

        //Completed tasks
        $this->q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.owner_user_id = ?", $this->reviewer->getNid())
            ->andWhere("a.status = ?", 25);
        $this->completed_paginator = new sfDoctrinePager('Task', 5);
        $this->completed_paginator->setQuery($this->q);
        $this->completed_paginator->setPage($this->completedpage);
        $this->completed_paginator->init();

        //Cancelled tasks
        $this->q = Doctrine_Query::create()
            ->from("Task a")
            ->where("a.owner_user_id = ?", $this->reviewer->getNid())
            ->andWhere("a.status = ?", 35);
        $this->cancel_paginator = new sfDoctrinePager('Task', 5);
        $this->cancel_paginator->setQuery($this->q);
        $this->cancel_paginator->setPage($this->cancelpage);
        $this->cancel_paginator->init();

        //Audit log
        $this->q = Doctrine_Query::create()
            ->from("AuditTrail a")
            ->where("a.user_id = ?", $this->reviewer->getNid())
            ->orderBy("a.id DESC");
        $this->audit_paginator = new sfDoctrinePager('AuditTrail', 5);
        $this->audit_paginator->setQuery($this->q);
        $this->audit_paginator->setPage($this->auditpage);
        $this->audit_paginator->init();
    }

    public function executeUpdateuser(sfWebRequest $request)
    {
        $userid = $request->getPostParameter('userid');
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $userid);
        $reviewer = $q->fetchOne();

        if ($reviewer) {
            $reviewer->setStrfirstname($request->getPostParameter('first_name'));
            $reviewer->setStrlastname($request->getPostParameter('last_name'));
            $reviewer->setStrdepartment($request->getPostParameter('department'));
            $reviewer->setStrcity($request->getPostParameter('city'));
            $reviewer->setStrcountry($request->getPostParameter('country'));
            $reviewer->setUserdefined1Value($request->getPostParameter('userdefined1_value'));
            $reviewer->setUserdefined2Value($request->getPostParameter('userdefined2_value'));
            $reviewer->save();
            echo "STATUS: SUCCESS";
        } else {
            echo "STATUS: FAILED";
        }
        exit;
    }


    public function executeUpdateemail(sfWebRequest $request)
    {
        $userid = $request->getPostParameter('userid');
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $userid);
        $reviewer = $q->fetchOne();

        if ($reviewer) {
            $reviewer->setStremail($request->getPostParameter('email_address'));
            $reviewer->save();
            echo "STATUS: SUCCESS";
        } else {
            echo "STATUS: FAILED";
        }
        exit;
    }


    public function executeUpdatephone(sfWebRequest $request)
    {
        $userid = $request->getPostParameter('userid');
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $userid);
        $reviewer = $q->fetchOne();

        if ($reviewer) {
            $reviewer->setStrphoneMain1($request->getPostParameter('phone_number'));
            $reviewer->save();
            echo "STATUS: SUCCESS";
        } else {
            echo "STATUS: FAILED";
        }
        exit;
    }

    public function executeUpdategroup(sfWebRequest $request)
    {
        $userid = $request->getPostParameter('userid');
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $userid);
        $reviewer = $q->fetchOne();

        if ($reviewer) {
            if ($_POST['groups']) {
                $q = Doctrine_Query::Create()
                    ->from('mfGuardUserGroup a')
                    ->where('a.user_id = ?', $reviewer->getNid());
                $usergroups = $q->execute();
                if ($usergroups) {
                    foreach ($usergroups as $usergroup) {
                        $usergroup->delete();
                    }
                }

                $groups = $_POST['groups'];
                foreach ($groups as $group) {
                    $usergroup = new MfGuardUserGroup();
                    $usergroup->setUserId($reviewer->getNid());
                    $usergroup->setGroupId($group);
                    $usergroup->save();
                }
            }
            echo "STATUS: SUCCESS";
        } else {
            echo "STATUS: FAILED";
        }
        exit;
    }

    public function executeUpdatepicture(sfWebRequest $request)
    {
        $userid = $request->getPostParameter('userid');
        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $userid);
        $reviewer = $q->fetchOne();

        $q = Doctrine_Query::create()
            ->from("ApSettings a")
            ->where("a.id = 1")
            ->orderBy("a.id DESC");
        $aplogo = $q->fetchOne();

        if ($reviewer) {
            $prefix_folder = $aplogo->getUploadDir() . "/";
            $allowedExts = array("gif", "jpeg", "jpg", "png");
            $temp = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);

            if ((($_FILES["file"]["type"] == "image/gif")
                    || ($_FILES["file"]["type"] == "image/jpeg")
                    || ($_FILES["file"]["type"] == "image/jpg")
                    || ($_FILES["file"]["type"] == "image/pjpeg")
                    || ($_FILES["file"]["type"] == "image/x-png")
                    || ($_FILES["file"]["type"] == "image/png"))
                && ($_FILES["file"]["size"] < 200000)
                && in_array($extension, $allowedExts)
            ) {
                if ($_FILES["file"]["error"] > 0) {
                    echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
                } else {
                    $new_filename = md5(date("Y-m-d g:I:s")) . $_FILES["file"]["name"];
                    echo "Upload: " . $_FILES["file"]["name"] . "<br>";
                    echo "Type: " . $_FILES["file"]["type"] . "<br>";
                    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
                    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";
                    if (file_exists($prefix_folder . $new_filename)) {
                        echo $_FILES["file"]["name"] . " already exists. ";
                    } else {
                        move_uploaded_file(
                            $_FILES["file"]["tmp_name"],
                            $prefix_folder . $new_filename
                        );
                        echo "Stored in: " . $prefix_folder . $new_filename;
                    }
                }
                $reviewer->setProfilePic($new_filename);
            } else {
                echo "Invalid file";
            }
            $reviewer->save();
            echo "STATUS: SUCCESS";
        } else {
            echo "STATUS: FAILED";
        }
        $this->redirect("/backend.php/users/viewuser/userid/" . $reviewer->getNid());
    }


    /**
     * Executes 'Writeuser' action
     *
     * Saves reviewer details to the database
     *
     * @param sfRequest $request A request object
     */
    public function executeWriteuser(sfWebRequest $request)
    {
        //OTB add check if user exist
        $q = Doctrine_Query::create()
            ->from('CfUser u')
            ->where('u.stremail =? or u.struserid =?', array($request->getParameter('strEMail'), $request->getParameter('UserName')));
        $user_count = $q->count();
        if (!$user_count) {
            $reviewer = new CfUser();
            $reviewer->setStrLastName($_REQUEST['strLastName']);
            $reviewer->setStrfirstname($_REQUEST['strFirstName']);
            $reviewer->setStremail($_REQUEST['strEMail']);
            $reviewer->setStruserid($_REQUEST['UserName']);
            $reviewer->setStrpassword(password_hash($_REQUEST['Password1'], PASSWORD_BCRYPT));
            $reviewer->setStrstreet($_REQUEST['IN_street']);
            $reviewer->setStrcountry($_REQUEST['IN_country']);
            $reviewer->setStrzipcode($_REQUEST['IN_zipcode']);
            $reviewer->setStrcity($_REQUEST['IN_city']);
            $reviewer->setStrphoneMain1($_REQUEST['IN_phone_main1']);
            $reviewer->setStrphoneMain2($_REQUEST['IN_phone_main2']);
            $reviewer->setStrphoneMobile($_REQUEST['IN_phone_mobile']);
            $reviewer->setStrdepartment($_REQUEST['IN_department']);
            $reviewer->save();

            $audit = new Audit();
            $audit->saveAudit("", "<a href=\"/backend.php/users/edituser?userid=" . $reviewer->getNid() . "&language=en\">added a new user</a>");

            //update user groups
            if ($_POST['groups']) {
                $groups = $_POST['groups'];
                foreach ($groups as $group) {
                    $usergroup = new MfGuardUserGroup();
                    $usergroup->setUserId($reviewer->getNid());
                    $usergroup->setGroupId($group);
                    $usergroup->save();
                }
            }

            echo "Success";
        } else {
            echo "Failed";
        }
        exit();
        //$this->setLayout(false);
    }

    /**
     * Executes 'Changepassword' action
     *
     * Update the user password
     *
     * @param sfRequest $request A request object
     */
    public function executeChangepassword(sfWebRequest $request)
    {
        $user_id = $request->getPostParameter("user_id");
        $password = $request->getPostParameter("new_password");
        $confirm_password = $request->getPostParameter("confirm_password");

        $q = Doctrine_Query::create()
            ->from("CfUser a")
            ->where("a.nid = ?", $user_id);
        $reviewer = $q->fetchOne();

        if ($reviewer && ($password == $confirm_password)) {
            $reviewer->setStrpassword(password_hash($password, PASSWORD_BCRYPT));
            $reviewer->save();

            $this->getUser()->setFlash('notice', "Successfully changed this reviewer's the password");
        } else {
            $this->getUser()->setFlash('error', "Could not change your password");
        }
        $this->redirect("/backend.php/users/viewuser/userid/" . $reviewer->getNid());
    }



    /**
     * Executes 'Index' action
     *
     * Displays list of reviewer departments
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $this->filter = "";
        $this->filterstatus = "";
        $this->department_filter = $request->getParameter("department_filter", false);

        $current_reviewer = Functions::current_user();

        if ($request->getPostParameter('search')) {
            $this->filter = $request->getPostParameter('search');
        }


        
        if ($this->department_filter == false && empty($this->filter)) {
            if ($this->getUser()->mfHasCredential("access_reviewers")) {
                //OTB ADD
                $agency = new AgencyManager();
                $agency_department = $agency->getAgencyDepartments($this->getUser()->getAttribute('userid'));
                $q = Doctrine_Query::create()
                    ->from("Department a")
                    ->whereIn('a.id', $agency_department)
                    ->orderBy("a.department_name ASC");
                $this->departments = $q->execute();
            } else {
                $q = Doctrine_Query::create()
                    ->from("Department a")
                    ->where("a.department_name = ?", $current_reviewer->getStrdepartment())
                    ->orderBy("a.department_name ASC");
                $this->departments = $q->execute();
            }
        } else {
            $q = Doctrine_Query::create()
                ->from("Department a")
                ->where("a.id = ?", $this->department_filter);
            $this->department = $q->fetchOne();
            if ($request->getParameter('filter')) {
                $this->filter = $request->getParameter('filter');
            }

            if ($this->filter) {
                if ($request->getParameter('filterstatus') != "") {
                    $this->filterstatus = $request->getParameter('filterstatus');

                    $q = Doctrine_Query::create()
                        ->from('CfUser a')
                        ->where('a.bdeleted = ?', $this->filterstatus)
                        ->andWhere('a.strfirstname LIKE ? OR a.stremail  LIKE ? OR a.struserid LIKE ?', array("%" . $this->filter . "%", "%" . $this->filter . "%", "%" . $this->filter . "%"))
                        ->orderBy('a.strfirstname ASC');
                } else {
                    $q = Doctrine_Query::create()
                        ->from('CfUser a')
                        ->where('a.bdeleted = 0')
                        ->andWhere('a.strfirstname LIKE ? OR a.stremail  LIKE ? OR a.struserid LIKE ?', array($this->filter . "%", "%" . $this->filter . "%", "%" . $this->filter . "%"))
                        ->orderBy('a.strfirstname ASC');
                }
            } else {
                if ($request->getParameter('filterstatus') != "") {
                    $this->filterstatus = $request->getParameter('filterstatus');
                    $q = Doctrine_Query::create()
                        ->from('CfUser a')
                        ->where('a.bdeleted = ?', $this->filterstatus)
                        ->andWhere('a.strdepartment = ?', $this->department_filter)
                        ->orderBy('a.strfirstname ASC');
                } else {
                    $q = Doctrine_Query::create()
                        ->from('CfUser a')
                        ->where('a.bdeleted = 0')
                        ->andWhere('a.strdepartment = ?', $this->department_filter)
                        ->orderBy('a.strfirstname ASC');
                }
            }

            $this->fromdate = "";
            $this->fromtime = "";
            $this->todate = "";
            $this->totime = "";

            $this->pager = new sfDoctrinePager('CfUser', 10);
            $this->pager->setQuery($q);
            $this->pager->setPage($request->getParameter('page', 1));
            $this->pager->init();
        }
    }




    /**
     * Executes 'Index' action
     *
     * Displays list of reviewer departments
     *
     * @param sfRequest $request A request object
     */
    public function executeSettingsindex(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.bdeleted = 0')
            ->orderBy('a.nid DESC');
        $this->reviewers = $q->execute();
    }

    /**
     * Executes 'Restore' action
     *
     * Restores an existing reviewer
     *
     * @param sfRequest $request A request object
     */
    public function executeRestore(sfWebRequest $request)
    {
        if ($this->getUser()->mfHasCredential("managereviewers")) {
            $q = Doctrine_Query::create()
                ->from('CfUser a')
                ->where('a.nid = ?', $request->getParameter('id'));
            $user = $q->fetchOne();
            if ($user) {
                $user->setBdeleted("0");
                $user->save();
            }

            //Audit 
            Audit::audit("", "Restored user account for reviewer #" . $user->getNid());

            $this->getUser()->setFlash('notice', 'Successfully restored the reviewer.');
            $audit = new Audit();
            $audit->saveAudit("", "Restored a reviewer: " . $user->getStrfirstname() . " " . $user->getStrlastname() . " (" . $user->getStremail() . ")");

            $this->redirect("/backend.php/users/index");
        } else {
            $this->getUser()->setFlash('notice', 'Could not restore the reviewer');
            $this->redirect("/backend.php/users/viewuser/userid/" . $request->getParameter('id'));
        }
    }

    /**
     * Executes 'Delete' action
     *
     * Deletes an existing reviewer
     *
     * @param sfRequest $request A request object
     */
    public function executeDelete(sfWebRequest $request)
    {
        if ($this->getUser()->mfHasCredential("managereviewers")) {
            $q = Doctrine_Query::create()
                ->from('CfUser a')
                ->where('a.nid = ?', $request->getParameter('id'));
            $user = $q->fetchOne();
            if ($user) {
                $user->setBdeleted("1");
                $user->save();
            }

            //Audit 
            Audit::audit("", "Deleted user account for reviewer #" . $user->getNid());

            $this->getUser()->setFlash('notice', 'Successfully deleted the reviewer.');
            $audit = new Audit();
            $audit->saveAudit("", "Delete a reviewer: " . $user->getStrfirstname() . " " . $user->getStrlastname() . " (" . $user->getStremail() . ")");

            $this->redirect("/backend.php/users/index");
        } else {
            $this->getUser()->setFlash('notice', 'Could not delete the reviewer');
            $this->redirect("/backend.php/users/viewuser/userid/" . $request->getParameter('id'));
        }
    }

    public function executeDeletecompletely(sfWebRequest $request)
    {
        if ($this->getUser()->mfHasCredential("managereviewers_delete")) {
            $q = Doctrine_Query::create()
                ->from('CfUser a')
                ->where('a.nid = ?', $request->getParameter('id'));
            $user = $q->fetchOne();

            $department = $request->getParameter('department');

            if ($user) {
                $user->delete();
                Audit::audit("", "Deleted user account for reviewer #" . $user->getNid());

                $this->getUser()->setFlash('notice', 'Successfully deleted the reviewer.');
                $audit = new Audit();
                $audit->saveAudit("", "Delete a reviewer: " . $user->getStrfirstname() . " " . $user->getStrlastname() . " (" . $user->getStremail() . ")");

                
                if ($department) {
                $this->redirect("/backend.php/users/index/department_filter/" . $department);
               
            }
            $this->redirect("/backend.php/users/index");
            }
        } else {
            $this->getUser()->setFlash('notice', 'Could not delete the reviewer');
            $this->redirect("/backend.php/users/viewuser/userid/" . $request->getParameter('id'));
        }
    }

    /**
     * Executes 'Audit' action
     *
     * Display an audit trail
     *
     * @param sfRequest $request A request object
     */
    public function executeAudit(sfWebRequest $request)
    {
        $q = Doctrine_Query::create()
            ->from('CfUser a')
            ->where('a.nid = ?', $request->getParameter('id'));
        $this->reviewer = $q->fetchOne();

        if ($request->getPostParameter('fromdate')) {
            $this->fromdate = $request->getPostParameter("fromdate");
            $this->fromtime = $request->getPostParameter("fromtime");
            $this->todate = $request->getPostParameter("todate");
            $this->totime = $request->getPostParameter("totime");

            $this->q = Doctrine_Query::create()
                ->from("AuditTrail a")
                ->where("a.user_id = ?", $request->getParameter('id'))
                ->andWhere("a.action_timestamp BETWEEN ? AND ?", array($this->fromdate . " " . $this->fromtime, $this->todate . " " . $this->totime))
                ->orderBy("a.id DESC");
        } else {
            $this->q = Doctrine_Query::create()
                ->from("AuditTrail a")
                ->where("a.user_id = ?", $request->getParameter('id'))
                ->orderBy("a.id DESC");
        }

        $this->pager = new sfDoctrinePager('AuditTrail', 10);
        $this->pager->setQuery($this->q);
        $this->pager->setPage($request->getParameter("page", 1));
        $this->pager->init();
    }
}
