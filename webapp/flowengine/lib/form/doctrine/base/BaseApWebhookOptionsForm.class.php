<?php

/**
 * ApWebhookOptions form base class.
 *
 * @method ApWebhookOptions getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApWebhookOptionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'awo_id'                        => new sfWidgetFormInputHidden(),
      'form_id'                       => new sfWidgetFormInputHidden(),
      'rule_id'                       => new sfWidgetFormInputHidden(),
      'rule_all_any'                  => new sfWidgetFormInputText(),
      'webhook_url'                   => new sfWidgetFormTextarea(),
      'webhook_method'                => new sfWidgetFormInputText(),
      'webhook_format'                => new sfWidgetFormInputText(),
      'webhook_raw_data'              => new sfWidgetFormTextarea(),
      'enable_http_auth'              => new sfWidgetFormInputHidden(),
      'http_username'                 => new sfWidgetFormInputText(),
      'http_password'                 => new sfWidgetFormInputText(),
      'enable_custom_http_headers'    => new sfWidgetFormInputHidden(),
      'custom_http_headers'           => new sfWidgetFormTextarea(),
      'delay_notification_until_paid' => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'awo_id'                        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('awo_id')), 'empty_value' => $this->getObject()->get('awo_id'), 'required' => false)),
      'form_id'                       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'rule_id'                       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('rule_id')), 'empty_value' => $this->getObject()->get('rule_id'), 'required' => false)),
      'rule_all_any'                  => new sfValidatorString(array('max_length' => 3)),
      'webhook_url'                   => new sfValidatorString(),
      'webhook_method'                => new sfValidatorString(array('max_length' => 4)),
      'webhook_format'                => new sfValidatorString(array('max_length' => 10)),
      'webhook_raw_data'              => new sfValidatorString(),
      'enable_http_auth'              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('enable_http_auth')), 'empty_value' => $this->getObject()->get('enable_http_auth'), 'required' => false)),
      'http_username'                 => new sfValidatorString(array('max_length' => 255)),
      'http_password'                 => new sfValidatorString(array('max_length' => 255)),
      'enable_custom_http_headers'    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('enable_custom_http_headers')), 'empty_value' => $this->getObject()->get('enable_custom_http_headers'), 'required' => false)),
      'custom_http_headers'           => new sfValidatorString(),
      'delay_notification_until_paid' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('delay_notification_until_paid')), 'empty_value' => $this->getObject()->get('delay_notification_until_paid'), 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_webhook_options[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApWebhookOptions';
  }

}
