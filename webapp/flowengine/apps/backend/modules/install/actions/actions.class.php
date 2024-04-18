<?php

/**
 * install actions.
 *
 * @package    permitflow
 * @subpackage install
 * @author     Your name here
 * @version    SVN: $Id$
 */
class installActions extends sfActions
{
  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    //If there are site settings then redirect to homepage
    $q = Doctrine_Query::create()
       ->from("ApSettings a");
    $site_settings = $q->fetchOne();
    if($site_settings)
    {
        $this->redirect("/backend.php");
    }

    $this->setLayout("layout-install");
  }

  /**
   * Executes update action
   *
   * @param sfRequest $request A request object
   */
  public function executeUpdate(sfWebRequest $request)
  {
    $settings = new ApSettings();
    $settings->setOrganisationName($request->getPostParameter("organisationname"));
    $settings->setOrganisationEmail($request->getPostParameter("organisationemail"));
    $settings->setUploadDir("/vagrant/web/asset_data");
    $settings->setDataDir("/vagrant/web/asset_data");
    $settings->save();

    $locale = new ExtLocales();

    $locale->setLocaleIdentifier($request->getPostParameter("locale"));

    if($request->getPostParameter("locale") == "ar_AR")
    {
      $locale->setLocalTitle("Arabic");
    }
    elseif($request->getPostParameter("locale") == "fr_FR")
    {
      $locale->setLocalTitle("French");
    }
    else
    {
      $locale->setLocalTitle("English");
    }

    if($request->getPostParameter("locale") == "ar_AR")
    {
      $locale->setTextAlign(0);
    }

    $locale->setIsDefault(1);
    $locale->save();

    $hash = password_hash($request->getPostParameter('admin_password'), PASSWORD_BCRYPT);

    $cf_user = new CfUser();
    $cf_user->setStrfirstname("System");
    $cf_user->setStrlastname("Administrator");
    $cf_user->setStremail($request->getPostParameter('admin_email'));
    $cf_user->setStruserid("admin");
    $cf_user->setStrpassword($hash);
    $cf_user->setBdeleted(0);
    $cf_user->setTslastaction(0);
    $cf_user->setStrstreet("");
    $cf_user->setStrcountry("");
    $cf_user->setStrzipcode("");
    $cf_user->setStrcity("");
    $cf_user->setStrphoneMain1("");
    $cf_user->setStrphoneMain2("");
    $cf_user->setStrphoneMobile("");
    $cf_user->setStrfax("");
    $cf_user->setStrorganisation("");
    $cf_user->setStrdepartment("");
    $cf_user->setStrcostcenter("");
    $cf_user->setUserdefined1Value("");
    $cf_user->setUserdefined2Value("");
    $cf_user->setNsubstitutetimevalue(0);
    $cf_user->setStrsubstitutetimeunit(0);
    $cf_user->setBusegeneralsubstituteconfig(0);
    $cf_user->setBusegeneralemailconfig(0);
    $cf_user->setEnableEmail(0);
    $cf_user->setEnableChat(0);
    $cf_user->setAboutMe("");
    $cf_user->setProfilePic("");
    $cf_user->setAddress("");
    $cf_user->setTwitter("");
    $cf_user->setFacebook("");
    $cf_user->setYoutube("");
    $cf_user->setLinkedin("");
    $cf_user->setPinterest("");
    $cf_user->setInstagram("");
    $cf_user->setStrtoken("");
    $cf_user->setStrtemppassword("");
    $cf_user->save();

    $this->redirect("/backend.php");

    $this->setLayout("layout-login");
  }
}
