<?php

/**
 * ConditionsOfApproval form base class.
 *
 * @method ConditionsOfApproval getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseConditionsOfApprovalForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'entry_id'          => new sfWidgetFormInputHidden(),
      'permit_id'   => new sfWidgetFormDoctrineChoice(array('model' => 'Permits')),
      'department_id'   => new sfWidgetFormDoctrineChoice(array('model' => 'Department')),
      'short_name'  => new sfWidgetFormInputText(),
      'description' => new sfWidgetFormTextarea(),
      'created_at'  => new sfWidgetFormInputHidden(),
      'updated_at'  => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'permit_id'   => new sfValidatorDoctrineChoice(array('model' => 'Permits','required' => true)),
      'short_name'  => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'description' => new sfValidatorString(array('required' => false)),
      'created_at'  => new sfValidatorDateTime(array('required' => false)),
      'updated_at'  => new sfValidatorDateTime(array('required' => false)),
	  'entry_id' => new sfValidatorDoctrineChoice(array('model' => 'FormEntry','required' => false)),
	  'department_id' => new sfValidatorDoctrineChoice(array('model' => 'Department')),
    ));

    $this->widgetSchema->setNameFormat('conditions_of_approval[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ConditionsOfApproval';
  }

}
