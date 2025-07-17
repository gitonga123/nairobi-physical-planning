<?php

abstract class BaseAgendaForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'agenda_date'    => new sfWidgetFormInputText(),
      'subject'           => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'agenda_date'           => new sfValidatorString(array('required' => false)),
      'subject'           => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('agenda[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Agenda';
  }

}
