<?php
abstract class BaseFeeRangeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'fee_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Fee'), 'add_empty' => false)),
      'name'           => new sfWidgetFormInputText(),
      'range_1'          => new sfWidgetFormInputText(),
      'range_2'    => new sfWidgetFormInputText(),
      'result_value'           => new sfWidgetFormTextarea(),
	  /*'condition_field' => new sfWidgetFormChoice(array('choices' => array())),
      'condition_operator'      => new sfWidgetFormSelect(array('choices' => array(1 => 'Equals' , 2 => 'Is less than' , 3 => 'Is greater than' , 4 => 'Is less than or equal to' , 5 => 'Is greater than or equal to' , 6 => 'Is not equal to' , 7 => 'Is like'))),
      'condition_value'           => new sfWidgetFormTextarea(),*/
      'value_type'      => new sfWidgetFormSelect(array('choices' => array('fixed' => 'Fixed Value' , 'formula' => 'Mathematical Formula'))),
      'condition_set_operator'           => new sfWidgetFormChoice(array('choices' => array(''=>'', 'and' => 'All conditions are met' , 'or' => 'Any of the conditions are met'))),
      'created_by' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'fee_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Fee'), 'required' => false)),
      'name'      => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'range_1'          => new sfValidatorString(array('max_length' => 65536, 'required' => false)),
      'range_2'    => new sfValidatorString(array('max_length' => 65536, 'required' => false)),
      'result_value'      => new sfValidatorString(array('max_length' => 65536, 'required' => false)),
	  /*'condition_field'	  => new CustomDynamicChoiceValidator(array('choices' => array(), 'required' => false)),
      'condition_operator'      => new sfValidatorChoice(array('choices' => array(1, 2, 3, 4, 5, 6, 7))),
      'condition_value'      => new sfValidatorString(array('max_length' => 65536, 'required' => false)),*/
      'value_type'      => new sfValidatorChoice(array('choices' => array('fixed', 'formula'))),
      'condition_set_operator'      => new sfValidatorChoice(array('choices' => array('and', 'or'), 'empty_value' =>true)),
      'created_by' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('fee_range[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FeeRange';
  }

}