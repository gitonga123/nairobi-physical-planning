<?php

/**
 * sfGuardUserProfile form base class.
 *
 * @method sfGuardUserProfile getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BasesfGuardUserProfileForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'user_id'    => new sfWidgetFormInputText(),
      'email'      => new sfWidgetFormInputText(),
      'fullname'   => new sfWidgetFormInputText(),
      'mobile'     => new sfWidgetFormInputText(),
      'validate'   => new sfWidgetFormInputText(),
      'registeras' => new sfWidgetFormInputText(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'user_id'    => new sfValidatorInteger(),
      'email'      => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'fullname'   => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'mobile'     => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'validate'   => new sfValidatorString(array('max_length' => 17, 'required' => false)),
      'registeras' => new sfValidatorInteger(),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_profile[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardUserProfile';
  }

}
