<?php

class ContactForm extends BaseForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'name'   => new sfWidgetFormInputText(),
      'email'   => new sfWidgetFormInputText(),
      'mobile'   => new sfWidgetFormInputText(),
      'subject'   => new sfWidgetFormInputText(),
      'message'   => new sfWidgetFormTextarea()
    ));
    $this->setValidators(array(
      'name'       => new sfValidatorString(array(
        'required' => true,
        'max_length' => 100
      )),
      'email'       => new sfValidatorEmail(array(
        'required' => true
      )),
      'mobile'       => new sfValidatorString(array(
        'required' => true
      )),
      'subject'       => new sfValidatorString(array(
        'required' => true,
        'max_length' => 100
      )),
      'message'       => new sfValidatorString(array(
        'required' => true,
        'max_length' => 250
      )),
    ));
    $this->widgetSchema->setNameFormat('contact[%s]');
  }
}