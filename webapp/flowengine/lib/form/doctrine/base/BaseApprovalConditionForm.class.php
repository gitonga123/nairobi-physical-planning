<?php

/**
 * ApprovalCondition form base class.
 *
 * @method ApprovalCondition getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApprovalConditionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'entry_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'condition_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ConditionsOfApproval'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'entry_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id', 'required' => false)),
      'condition_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ConditionsOfApproval'), 'column' => 'id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('approval_condition[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApprovalCondition';
  }

}
