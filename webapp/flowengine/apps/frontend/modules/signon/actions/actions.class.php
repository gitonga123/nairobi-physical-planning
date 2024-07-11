<?php

/**
 * Single Signon actions.
 *
 * Handles the single sign-on actions
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

class signonActions extends sfActions
{
    public function executeLogin(sfWebRequest $request)
    {
        $this->setLayout("layoutmentorlogin");
        //Store referer before redirecting to sso
        $referer = $this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer();
        $this->getUser()->setAttribute('referer', $referer);

        $url = sfConfig::get('app_sso_secret') ? sfConfig::get('app_sso_authorize_url') . '?return_url=' . sfContext::getInstance()->getController()->genUrl('signon/signin', true) : sfContext::getInstance()->getController()->genUrl('sfGuardAuth/signin');
        return $this->redirect($url);
    }

    public function executeLogout(sfWebRequest $request)
    {
        //redirect to sso_homepage after logout if set
        if (sfConfig::get('app_sso_homepage')) {
            $this->getUser()->signOut();
            $url = sfConfig::get('app_sso_secret') ? sfConfig::get('app_sso_logout_url') . '?return_url=' . sfConfig::get('app_sso_homepage') : sfContext::getInstance()->getController()->genUrl('sfGuardAuth/signout');
        } else {
            $url = sfConfig::get('app_sso_secret') ? sfConfig::get('app_sso_logout_url') . '?return_url=' . sfContext::getInstance()->getController()->genUrl('logout/index', true) : sfContext::getInstance()->getController()->genUrl('sfGuardAuth/signout');
        }
        return $this->redirect($url);
    }

    public function executeRegister(sfWebRequest $request)
    {
        $url = sfConfig::get('app_sso_secret') ? sfConfig::get('app_sso_register_url') . '?return_url=' . sfContext::getInstance()->getController()->genUrl('signon/signin', true) : sfContext::getInstance()->getController()->genUrl('sfApply/apply');
        return $this->redirect($url);
    }

    public function executeSignin(sfWebRequest $request)
    {
        try {

            $code = $request->getParameter('code');

            if (empty($code) || is_null($code)) {
                throw new sfException('Something Went Wrong. Please try again later.', 403);
            }

            $is_valid = true;

            $stream = new Stream();
            $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/accounts/login/token/';
            $stream_response = $stream->sendRequest([
                'url' => $url,
                'method' => 'POST', // GET, POST, PUT, DELETE,
                'ssl' => 'none',
                'contentType' => 'json',
                'data' => [
                    'code' => $code
                ]
            ]);

            if ($stream_response->status !== 200) {
                throw new sfException('Something Went Wrong. Please try again later.', $stream_response->status);
            }


            $this->token = $stream_response->content['token'];

            if (empty($this->token) || is_null($this->token)) {
                throw new sfException('Something Went Wrong. Please try again later.', 500);
            }

            $this->cache = new sfFileCache([
                'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
            ]);

            $_SESSION['jambo_token'] = $this->token;

            $url = sfConfig::get('app_sso_jambo_url') . 'api/v1/accounts/user_info/';
            // fetch user information
            $stream_response = $stream->sendRequest([
                'url' => $url,
                'method' => 'GET', // GET, POST, PUT, DELETE,
                'ssl' => 'none',
                'contentType' => 'json',
                'data' => [],
                'headers' => array(
                    "Authorization" => "JWT " . $this->token,
                ),
            ]);
            $user_api_data = $stream_response->content;

            $id_number = $user_api_data['identity_number'];
            $email = $user_api_data['email'];
            $first_name = $user_api_data['first_name'];
            $last_name = $user_api_data['last_name'];
            $mobile_number = $user_api_data['mobile_number'];
            $username = $user_api_data['username'];
            $account_type = $user_api_data['account_type'];
            $fullname = $first_name . " " . $last_name;

            $this->sfGuardUser = Doctrine_Core::getTable('sfGuardUser')->createQuery('u')->where('username = ?', $username)->orderBy('u.id DESC')->fetchOne();
            $this->cache->set("jambo_token_{$username}", $this->token, 3600);

            if (!$this->sfGuardUser) {
                // create user
                $this->sfGuardUser = new sfGuardUser();
                $this->sfGuardUser->username = $username;
                $this->sfGuardUser->email_address = $email;
                $this->sfGuardUser->save();

                // create user profile
                $profile = new sfGuardUserProfile();
                $profile->setUserId($this->sfGuardUser->id);
                $profile->setFullname($fullname);
                $profile->setEmail($email);
                $profile->setMobile($username);
                $profile->setRegisteras(6);
                $profile->save();
            } else {
                $user_share = Doctrine_Core::getTable('sfGuardUserProfile')->findOneByUserId($this->sfGuardUser->id);
                error_log("Profile exists yes or no");
                error_log($user_share);
                if ($user_share) {
                    $user_share->setFullname($fullname);
                    $user_share->setEmail($email);
                    $user_share->setMobile($username);
                    $user_share->setRegisteras(6);
                    $user_share->save();
                } else {
                    $profile = new sfGuardUserProfile();
                    $profile->user_id = $this->sfGuardUser->id;
                    $profile->setFullname($fullname);
                    $profile->setEmail($email);
                    $profile->setMobile($username);
                    $profile->setRegisteras(6);
                    $profile->save();
                }
            }

            $this->getUser()->signin($this->sfGuardUser, false);

            if ($this->getUser()->getAttribute('referer')) {
                var_dump("1");

                $url = sfConfig::get('sso_jambo_web_url') . "/#/dashboard";
                var_dump($url);
                die;
                // return $this->redirect($this->getUser()->getAttribute('referer'));
                return $this->redirect($url);
            } else {
                $url = sfConfig::get('sso_jambo_web_url') . "/plan";
                var_dump("2");
                var_dump($url);
                die;
                return $this->redirect($url);
            }
        } catch (\Exception $error) {
            throw new sfException($error->getMessage(), $stream_response->status);
        }
    }

    public function executeSigninOriginal(sfWebRequest $request)
    {
        // get the data query param
        $data = $request->getParameter('data');
        $data = json_decode(base64_decode($data), true);
        $is_valid = true;

        // check our expected keys
        if ($data === null || !array_key_exists('email', $data) || !array_key_exists('id_number', $data) || !array_key_exists('at', $data) || !array_key_exists('signature', $data))
            $is_valid = false;

        // validate signature
        $signature = $data['signature'];
        unset($data['signature']);

        // make hash of the data
        //OTB patch - make sure u escape unescaped json slashes from the data
        $computed_signature = base64_encode(hash_hmac('sha256', json_encode($data, JSON_UNESCAPED_SLASHES), sfConfig::get('app_sso_secret'), true));

        if ($signature !== $computed_signature)
            $is_valid = false;

        if (!$is_valid) {
            $response = $this->getResponse();
            $response->setStatusCode(400); //@todo: throw a 400 bad request
            return $response;
        } else {
            $id_number = $data['id_number'];
            $email = $data['email'];
            $first_name = $data['first_name'];
            $middle_name = $data['middle_name'];
            $last_name = $data['last_name'];
            $mobile_number = $data['mobile_number'];
            $account_type = $data['account_type'];
            $fullname = "";

            if ($middle_name != "") {
                $fullname = $first_name . " " . $middle_name . " " . $last_name;
            } else {
                $fullname = $first_name . " " . $last_name;
            }

            // get or create user
            $this->sfGuardUser = Doctrine_Core::getTable('sfGuardUser')->createQuery('u')->where('username = ?', $id_number)->fetchOne();
            if (!$this->sfGuardUser) {
                // create user
                $this->sfGuardUser = new sfGuardUser();
                $this->sfGuardUser->username = $id_number;
                $this->sfGuardUser->email_address = $email;
                $this->sfGuardUser->save();

                // create user profile
                $profile = new sfGuardUserProfile();
                $profile->user_id = $this->sfGuardUser->id;
                $profile->fullname = $fullname;
                $profile->email = $email;
                $profile->mobile = $mobile_number;
                $profile->registeras = $account_type ? $account_type : 1;
                $profile->save();

                // Check if user categories are enabled. If they are, users cannot log into the system
                //  until their account is activated in the backend
                if (sfConfig::get('app_enable_categories') == "yes") {
                    //if(!$this->sfGuardUser->getIsActive()) {
                    //    return $this->redirect("/index.php/index/inactive?reg=1");
                    //}
                }
            } else {
                // Check if user categories are enabled. If they are, users cannot log into the system
                //  until their account is activated in the backend
                if (sfConfig::get('app_enable_categories') == "yes") {
                    //if(!$this->sfGuardUser->getIsActive()) {
                    //    return $this->redirect("/index.php/index/inactive?reg=0");
                    //}
                }

                $profile = Doctrine_Core::getTable('sfGuardUserProfile')->createQuery('u')->where('user_id = ?', $this->sfGuardUser->id)->fetchOne();
                if ($profile) {
                    $profile->fullname = $fullname;
                    $profile->email = $email;
                    $profile->mobile = $mobile_number;
                    $profile->registeras = $account_type ? $account_type : 1;
                    $profile->save();
                } else {
                    $profile = new sfGuardUserProfile();
                    $profile->user_id = $this->sfGuardUser->id;
                    $profile->fullname = $fullname;
                    $profile->email = $email;
                    $profile->mobile = $mobile_number;
                    $profile->registeras = $account_type ? $account_type : 1;
                    $profile->save();
                }
            }
            $this->getUser()->signin($this->sfGuardUser, false);

            // $q = Doctrine_Query::create()
            //     ->from('mfUserProfile a')
            //     ->where('a.user_id = ?', $this->sfGuardUser->id);
            // $profiles = $q->execute();

            // //If form_categories have been configured, redirect user to choose user category or enter additional details
            // if(sfConfig::get('app_enable_categories') == "yes" && sizeof($profiles) == 0)
            // {
            //     return $this->redirect("/index.php/frusers/category");
            // }

            //Redirect to referer if exists else redirect to homepage
            if ($this->getUser()->getAttribute('referer')) {
                return $this->redirect($this->getUser()->getAttribute('referer'));
            } else {
                return $this->redirect('@homepage');
            }
        }
    }
}
