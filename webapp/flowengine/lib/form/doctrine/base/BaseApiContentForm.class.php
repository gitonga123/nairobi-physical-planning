<?php

/**
 * ApiContent form base class
 */
abstract class BaseApiContentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'form_id'       => new sfWidgetFormInputText(),
      'api_use' => new sfWidgetFormInputText(),
      'content'       => new sfWidgetFormTextarea(),
      'merchant_id'       => new sfWidgetFormInputText(),
      'request_url'       => new sfWidgetFormInputText(),
      'api_use_diff'       => new sfWidgetFormInputText()
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'       => new sfValidatorInteger(),
      'api_use'       => new sfValidatorString(),
      'content'       => new sfValidatorString(),
      'merchant_id'       => new sfValidatorInteger(),
      'request_url'       => new sfValidatorString(),
      'api_use_diff'       => new sfValidatorString(['required' => false]),
    ));

    $this->widgetSchema->setNameFormat('api_content[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApiContent';
  }

}
