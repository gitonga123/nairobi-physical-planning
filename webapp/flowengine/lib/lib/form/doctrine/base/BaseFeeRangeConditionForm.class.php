<?php
abstract class BaseFeeRangeConditionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'fee_range_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FeeRange'), 'add_empty' => false)),
	  'condition_field' => new sfWidgetFormChoice(array('choices' => array())),
      'condition_operator'      => new sfWidgetFormSelect(array('choices' => array(1 => 'Equals' , 2 => 'Is less than' , 3 => 'Is greater than' , 4 => 'Is less than or equal to' , 5 => 'Is greater than or equal to' , 6 => 'Is not equal to' , 7 => 'Is like'))),
      'condition_value'           => new sfWidgetFormTextarea(),
      'created_by' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'fee_range_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('FeeRange'), 'required' => false)),
	  'condition_field'	  => new sfValidatorInteger(array('required' => false)),
      'condition_operator'      => new sfValidatorChoice(array('choices' => array(1, 2, 3, 4, 5, 6, 7))),
      'condition_value'      => new sfValidatorString(array('max_length' => 65536, 'required' => false)),
      'created_by' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('fee_range_condition[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FeeRangeCondition';
  }

}