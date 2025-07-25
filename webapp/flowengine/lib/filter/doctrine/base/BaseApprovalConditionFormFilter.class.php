<?php

/**
 * ApprovalCondition filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApprovalConditionFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'entry_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'condition_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ConditionsOfApproval'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'entry_id'     => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id')),
      'condition_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ConditionsOfApproval'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('approval_condition_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApprovalCondition';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'entry_id'     => 'ForeignKey',
      'condition_id' => 'ForeignKey',
    );
  }
}
