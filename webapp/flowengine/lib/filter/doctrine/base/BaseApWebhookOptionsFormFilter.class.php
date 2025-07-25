<?php

/**
 * ApWebhookOptions filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApWebhookOptionsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'rule_all_any'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'webhook_url'                   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'webhook_method'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'webhook_format'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'webhook_raw_data'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'http_username'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'http_password'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'custom_http_headers'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'rule_all_any'                  => new sfValidatorPass(array('required' => false)),
      'webhook_url'                   => new sfValidatorPass(array('required' => false)),
      'webhook_method'                => new sfValidatorPass(array('required' => false)),
      'webhook_format'                => new sfValidatorPass(array('required' => false)),
      'webhook_raw_data'              => new sfValidatorPass(array('required' => false)),
      'http_username'                 => new sfValidatorPass(array('required' => false)),
      'http_password'                 => new sfValidatorPass(array('required' => false)),
      'custom_http_headers'           => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_webhook_options_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApWebhookOptions';
  }

  public function getFields()
  {
    return array(
      'awo_id'                        => 'Number',
      'form_id'                       => 'Number',
      'rule_id'                       => 'Number',
      'rule_all_any'                  => 'Text',
      'webhook_url'                   => 'Text',
      'webhook_method'                => 'Text',
      'webhook_format'                => 'Text',
      'webhook_raw_data'              => 'Text',
      'enable_http_auth'              => 'Number',
      'http_username'                 => 'Text',
      'http_password'                 => 'Text',
      'enable_custom_http_headers'    => 'Number',
      'custom_http_headers'           => 'Text',
      'delay_notification_until_paid' => 'Number',
    );
  }
}
