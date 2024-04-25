<?php

abstract class BaseAgendaColumnsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'form_id'    => new sfWidgetFormInputText(),
      'element_id'           => new sfWidgetFormInputText(),
      'position'           => new sfWidgetFormInputText(),
      'entry_column'           => new sfWidgetFormInputText(),
      'stage'           => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'           => new sfValidatorString(array('required' => false)),
      'element_id'           => new sfValidatorString(array('required' => false)),
      'position'           => new sfValidatorString(array('required' => false)),
      'entry_column'           => new sfValidatorString(array('required' => false)),
      'stage'           => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('agenda_columns[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgendaColumns';
  }

}
