<?php

/**
 * ApWebhookLogicConditions filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApWebhookLogicConditionsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'element_name'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'rule_condition' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'rule_keyword'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'element_name'   => new sfValidatorPass(array('required' => false)),
      'rule_condition' => new sfValidatorPass(array('required' => false)),
      'rule_keyword'   => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_webhook_logic_conditions_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApWebhookLogicConditions';
  }

  public function getFields()
  {
    return array(
      'wlc_id'         => 'Number',
      'form_id'        => 'Number',
      'target_rule_id' => 'Number',
      'element_name'   => 'Text',
      'rule_condition' => 'Text',
      'rule_keyword'   => 'Text',
    );
  }
}
