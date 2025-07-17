<?php

class BackendTransferForm extends BaseForm
{
    public function configure()
    {
        $this->setWidgets(array(
            'email'    => new sfWidgetFormInputText()
        ));
        $this->setValidators(array(
            'email' => new sfValidatorEmail()
        ));
        $this->widgetSchema->setNameFormat('transfer[%s]');
    }
}