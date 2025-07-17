<?php

class BackendSigninForm extends BaseForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'username'    => new sfWidgetFormInputText(),
      'password'   => new sfWidgetFormInputPassword()
    ));
    $this->setValidators(array(
        'username' => new sfValidatorString(),
        'password' => new sfValidatorString()
    ));
    $this->widgetSchema->setNameFormat('login[%s]');
  }
}