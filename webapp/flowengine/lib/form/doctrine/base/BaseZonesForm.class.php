<?php

abstract class BaseZonesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
	  'zone_id' => new sfWidgetFormInputText,
      'name'     => new sfWidgetFormInputText(),
      'sub_county'     => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
	  'zone_id' => new sfValidatorNumber(array('required' => false)),
      'name' => new sfValidatorString(array('max_length' => 255,'required' => false)),
      'sub_county'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('zones[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Zones';
  }

}
