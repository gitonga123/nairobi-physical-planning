<?php

/**
 * ApWebhookParameters filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApWebhookParametersFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'param_name'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'param_value' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'param_name'  => new sfValidatorPass(array('required' => false)),
      'param_value' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_webhook_parameters_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApWebhookParameters';
  }

  public function getFields()
  {
    return array(
      'awp_id'      => 'Number',
      'form_id'     => 'Number',
      'rule_id'     => 'Number',
      'param_name'  => 'Text',
      'param_value' => 'Text',
    );
  }
}
