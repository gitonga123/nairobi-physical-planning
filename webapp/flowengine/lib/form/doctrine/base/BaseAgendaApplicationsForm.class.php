<?php

abstract class BaseAgendaApplicationsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'agenda_id'    => new sfWidgetFormInputText(),
      'form_id'           => new sfWidgetFormInputText(),
      'entry_id'           => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'agenda_id'           => new sfValidatorString(array('required' => false)),
      'form_id'           => new sfValidatorString(array('required' => false)),
      'entry_id'           => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('agenda_applications[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgendaApplications';
  }

}
