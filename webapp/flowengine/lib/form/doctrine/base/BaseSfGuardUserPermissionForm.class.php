<?php

/**
 * SfGuardUserPermission form base class.
 *
 * @method SfGuardUserPermission getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BaseSfGuardUserPermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'       => new sfWidgetFormInputHidden(),
      'permission_id' => new sfWidgetFormInputHidden(),
      'created_at'    => new sfWidgetFormDateTime(),
      'updated_at'    => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'user_id'       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('user_id')), 'empty_value' => $this->getObject()->get('user_id'), 'required' => false)),
      'permission_id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('permission_id')), 'empty_value' => $this->getObject()->get('permission_id'), 'required' => false)),
      'created_at'    => new sfValidatorDateTime(),
      'updated_at'    => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfGuardUserPermission';
  }

}
