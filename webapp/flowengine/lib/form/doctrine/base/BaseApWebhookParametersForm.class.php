<?php

/**
 * ApWebhookParameters form base class.
 *
 * @method ApWebhookParameters getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApWebhookParametersForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'awp_id'      => new sfWidgetFormInputHidden(),
      'form_id'     => new sfWidgetFormInputHidden(),
      'rule_id'     => new sfWidgetFormInputHidden(),
      'param_name'  => new sfWidgetFormTextarea(),
      'param_value' => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'awp_id'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('awp_id')), 'empty_value' => $this->getObject()->get('awp_id'), 'required' => false)),
      'form_id'     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'rule_id'     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('rule_id')), 'empty_value' => $this->getObject()->get('rule_id'), 'required' => false)),
      'param_name'  => new sfValidatorString(),
      'param_value' => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('ap_webhook_parameters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApWebhookParameters';
  }

}
