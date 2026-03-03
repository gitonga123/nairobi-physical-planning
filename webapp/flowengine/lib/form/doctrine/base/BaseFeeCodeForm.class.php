<?php

abstract class BaseFeeCodeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'service_id'     => new sfWidgetFormInputText(),
      'service_name'     => new sfWidgetFormInputText(),
      'fixed'  => new sfWidgetFormChoice(array('choices' => array('No','Yes'))),
      'amount'       => new sfWidgetFormInputText(),
      'zone'    => new sfWidgetFormDoctrineChoice(
            array(
                'model' => 'Zones',
            )
        ),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'service_id' => new sfValidatorInteger(array('required' => false)),
      'service_name'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'fixed'  => new sfValidatorBoolean(),
      'amount'       => new sfValidatorNumber(array('required' => false)),
      'zone'    => new sfValidatorDoctrineChoice(array('model' => 'Zones')),
    ));

    $this->widgetSchema->setNameFormat('fee_code[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FeeCode';
  }

}
