<?php

require_once __DIR__.'/../vendor/friendsofsymfony1/symfony1/lib/autoload/sfCoreAutoload.class.php';
require_once __DIR__.'/../vendor/autoload.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $vendor_dir = sfConfig::get('sf_lib_dir').'/vendor'.PATH_SEPARATOR;
    $composer_autoload_dir = dirname(__FILE__).'/../../vendor' . PATH_SEPARATOR;
    set_include_path($vendor_dir . $composer_autoload_dir . get_include_path());
    
    $this->enablePlugins(
      array(
        'sfDoctrinePlugin', 
        'sfDoctrineGuardPlugin',
        'mfUserProfilePlugin',
        'sfDoctrineApplyPlugin'
      )
    );

    require_once('form_builder_config.php');
  }

  public function configureDoctrine(Doctrine_Manager $manager)
  {
    $manager->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER, true);
  }
}
