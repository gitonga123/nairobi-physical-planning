<?php

class SignedPermitForm extends BaseForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'permit'   => new sfWidgetFormInputFile(array('label' => 'Signed Permit'))
    ));
    $this->setValidators(array(
      'permit'       => new sfValidatorFile(array(
        'required' => true,
        'path' => 'asset_signed',
        'mime_types' => array('application/pdf','image/jpeg')
       ))
    ));
    $this->widgetSchema->setNameFormat('permit[%s]');
  }
}