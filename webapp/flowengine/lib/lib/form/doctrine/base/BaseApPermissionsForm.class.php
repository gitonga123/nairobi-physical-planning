<?php

/**
 * ApPermissions form base class.
 *
 * @method ApPermissions getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApPermissionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'      => new sfWidgetFormInputHidden(),
      'user_id'      => new sfWidgetFormInputHidden(),
      'edit_form'    => new sfWidgetFormInputText(),
      'edit_report'  => new sfWidgetFormInputText(),
      'edit_entries' => new sfWidgetFormInputText(),
      'view_entries' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'form_id'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'user_id'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('user_id')), 'empty_value' => $this->getObject()->get('user_id'), 'required' => false)),
      'edit_form'    => new sfValidatorInteger(array('required' => false)),
      'edit_report'  => new sfValidatorInteger(array('required' => false)),
      'edit_entries' => new sfValidatorInteger(array('required' => false)),
      'view_entries' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_permissions[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApPermissions';
  }

}
