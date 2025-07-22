<?php

/**
 * ApSettings filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApSettingsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'smtp_enable'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'smtp_host'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'smtp_port'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'smtp_auth'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'smtp_username'            => new sfWidgetFormFilterInput(),
      'smtp_password'            => new sfWidgetFormFilterInput(),
      'smtp_secure'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'upload_dir'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'data_dir'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'upload_dir_web'           => new sfWidgetFormFilterInput(),
      'data_dir_web'             => new sfWidgetFormFilterInput(),
      'organisation_name'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'organisation_email'       => new sfWidgetFormFilterInput(),
      'organisation_description' => new sfWidgetFormFilterInput(),
      'form_manager_max_rows'    => new sfWidgetFormFilterInput(),
      'admin_image_url'          => new sfWidgetFormFilterInput(),
      'disable_machform_link'    => new sfWidgetFormFilterInput(),
      'machform_version'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'admin_theme'              => new sfWidgetFormFilterInput(),
      'organisation_help'        => new sfWidgetFormFilterInput(),
      'organisation_sidebar'     => new sfWidgetFormFilterInput(),
      'default_form_theme_id'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'first_run'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'smtp_enable'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'smtp_host'                => new sfValidatorPass(array('required' => false)),
      'smtp_port'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'smtp_auth'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'smtp_username'            => new sfValidatorPass(array('required' => false)),
      'smtp_password'            => new sfValidatorPass(array('required' => false)),
      'smtp_secure'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'upload_dir'               => new sfValidatorPass(array('required' => false)),
      'data_dir'                 => new sfValidatorPass(array('required' => false)),
      'upload_dir_web'           => new sfValidatorPass(array('required' => false)),
      'data_dir_web'             => new sfValidatorPass(array('required' => false)),
      'organisation_name'        => new sfValidatorPass(array('required' => false)),
      'organisation_email'       => new sfValidatorPass(array('required' => false)),
      'organisation_description' => new sfValidatorPass(array('required' => false)),
      'form_manager_max_rows'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'admin_image_url'          => new sfValidatorPass(array('required' => false)),
      'disable_machform_link'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'machform_version'         => new sfValidatorPass(array('required' => false)),
      'admin_theme'              => new sfValidatorPass(array('required' => false)),
      'organisation_help'        => new sfValidatorPass(array('required' => false)),
      'organisation_sidebar'     => new sfValidatorPass(array('required' => false)),
      'default_form_theme_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'first_run'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_settings_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApSettings';
  }

  public function getFields()
  {
    return array(
      'id'                       => 'Number',
      'smtp_enable'              => 'Number',
      'smtp_host'                => 'Text',
      'smtp_port'                => 'Number',
      'smtp_auth'                => 'Number',
      'smtp_username'            => 'Text',
      'smtp_password'            => 'Text',
      'smtp_secure'              => 'Number',
      'upload_dir'               => 'Text',
      'data_dir'                 => 'Text',
      'upload_dir_web'           => 'Text',
      'data_dir_web'             => 'Text',
      'organisation_name'        => 'Text',
      'organisation_email'       => 'Text',
      'organisation_description' => 'Text',
      'form_manager_max_rows'    => 'Number',
      'admin_image_url'          => 'Text',
      'disable_machform_link'    => 'Number',
      'machform_version'         => 'Text',
      'admin_theme'              => 'Text',
      'organisation_help'        => 'Text',
      'organisation_sidebar'     => 'Text',
      'default_form_theme_id'    => 'Number',
      'first_run'                => 'Number',
    );
  }
}
