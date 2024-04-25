<?php

/**
 * ApSettings form base class.
 *
 * @method ApSettings getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApSettingsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                       => new sfWidgetFormInputHidden(),
      'smtp_enable'              => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    0 => "Disable",
                    1 => "Enable"
                )
            )
        ),
      'smtp_host'                => new sfWidgetFormInputText(),
      'smtp_port'                => new sfWidgetFormInputText(),
      'smtp_auth'                => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    0 => "Disable",
                    1 => "Enable"
                )
            )
        ),
      'smtp_username'            => new sfWidgetFormInputText(),
      'smtp_password'            => new sfWidgetFormInputText(),
      'smtp_secure'              => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    'tls' => "tls",
                    'ssl' => "ssl"
                )
            )
        ),
      'upload_dir'               => new sfWidgetFormInputText(),
      'data_dir'                 => new sfWidgetFormInputText(),
      'upload_dir_web'           => new sfWidgetFormInputText(),
      'data_dir_web'             => new sfWidgetFormInputText(),
      'organisation_name'        => new sfWidgetFormInputText(),
      'organisation_email'       => new sfWidgetFormInputText(),
      'organisation_description' => new sfWidgetFormTextarea(),
      'form_manager_max_rows'    => new sfWidgetFormInputText(),
      'admin_image_url'          => new sfWidgetFormInputFile(),
      'disable_machform_link'    => new sfWidgetFormInputText(),
      'machform_version'         => new sfWidgetFormInputText(),
      'admin_theme'              => new sfWidgetFormInputText(),
      'organisation_help'        => new sfWidgetFormTextarea(),
      'organisation_sidebar'     => new sfWidgetFormTextarea(),
      'default_form_theme_id'    => new sfWidgetFormInputText(),
      'first_run'                => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'smtp_enable'              => new sfValidatorInteger(array('required' => false)),
      'smtp_host'                => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'smtp_port'                => new sfValidatorInteger(array('required' => false)),
      'smtp_auth'                => new sfValidatorInteger(array('required' => false)),
      'smtp_username'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'smtp_password'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'smtp_secure'              => new sfValidatorChoice(array('choices' => array('tls','ssl'),'required' => false)),
      'upload_dir'               => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'data_dir'                 => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'upload_dir_web'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'data_dir_web'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'organisation_name'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'organisation_email'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'organisation_description' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'form_manager_max_rows'    => new sfValidatorInteger(array('required' => false)),
      'admin_image_url'                 => new sfValidatorFile(array(
          'required'   => false,
          'path'       => dirname(__FILE__).'/../../../../web/asset_data',
          'mime_types' => 'web_images',
      )),
      'disable_machform_link'    => new sfValidatorInteger(array('required' => false)),
      'machform_version'         => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'admin_theme'              => new sfValidatorString(array('max_length' => 11, 'required' => false)),
      'organisation_help'        => new sfValidatorString(array('required' => false)),
      'organisation_sidebar'     => new sfValidatorString(array('required' => false)),
      'default_form_theme_id'    => new sfValidatorInteger(array('required' => false)),
      'first_run'                => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_settings[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApSettings';
  }

}
