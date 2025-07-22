<?php

/**
 * MfGuardUserPermission form base class.
 *
 * @method MfGuardUserPermission getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseMfGuardUserPermissionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'       => new sfWidgetFormInputHidden(),
      'permission_id' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'user_id'       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('user_id')), 'empty_value' => $this->getObject()->get('user_id'), 'required' => false)),
      'permission_id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('permission_id')), 'empty_value' => $this->getObject()->get('permission_id'), 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('mf_guard_user_permission[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MfGuardUserPermission';
  }

}
