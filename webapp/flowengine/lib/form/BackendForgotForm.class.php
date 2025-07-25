<?php

class BackendForgotForm extends BaseForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'email'    => new sfWidgetFormInputText()
    ));
    $this->setValidators(array(
        'email' => new sfValidatorEmail()
    ));
    $this->widgetSchema->setNameFormat('forgot[%s]');
  }
}