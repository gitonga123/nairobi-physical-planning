<?php

/**
 * ApUsers form base class.
 *
 * @method ApUsers getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApUsersForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'         => new sfWidgetFormInputHidden(),
      'user_email'      => new sfWidgetFormInputText(),
      'user_password'   => new sfWidgetFormInputText(),
      'user_fullname'   => new sfWidgetFormInputText(),
      'priv_administer' => new sfWidgetFormInputText(),
      'priv_new_forms'  => new sfWidgetFormInputText(),
      'priv_new_themes' => new sfWidgetFormInputText(),
      'last_login_date' => new sfWidgetFormDateTime(),
      'last_ip_address' => new sfWidgetFormInputText(),
      'cookie_hash'     => new sfWidgetFormInputText(),
      'status'          => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'user_id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('user_id')), 'empty_value' => $this->getObject()->get('user_id'), 'required' => false)),
      'user_email'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'user_password'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'user_fullname'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'priv_administer' => new sfValidatorInteger(array('required' => false)),
      'priv_new_forms'  => new sfValidatorInteger(array('required' => false)),
      'priv_new_themes' => new sfValidatorInteger(array('required' => false)),
      'last_login_date' => new sfValidatorDateTime(array('required' => false)),
      'last_ip_address' => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'cookie_hash'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'status'          => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_users[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApUsers';
  }

}
