<?php

class BackendRecoverForm extends BaseForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'password'    => new sfWidgetFormInputPassword()
    ));
    $this->setValidators(array(
        'password' => new sfValidatorString()
    ));
    $this->widgetSchema->setNameFormat('recovery[%s]');
  }
}