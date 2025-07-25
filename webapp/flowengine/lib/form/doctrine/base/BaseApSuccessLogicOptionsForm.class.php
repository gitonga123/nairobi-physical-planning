<?php

/**
 * ApSuccessLogicOptions form base class.
 *
 * @method ApSuccessLogicOptions getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApSuccessLogicOptionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'slo_id'          => new sfWidgetFormInputHidden(),
      'form_id'         => new sfWidgetFormInputHidden(),
      'rule_id'         => new sfWidgetFormInputHidden(),
      'rule_all_any'    => new sfWidgetFormInputText(),
      'success_type'    => new sfWidgetFormInputText(),
      'success_message' => new sfWidgetFormTextarea(),
      'redirect_url'    => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'slo_id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('slo_id')), 'empty_value' => $this->getObject()->get('slo_id'), 'required' => false)),
      'form_id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'rule_id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('rule_id')), 'empty_value' => $this->getObject()->get('rule_id'), 'required' => false)),
      'rule_all_any'    => new sfValidatorString(array('max_length' => 3)),
      'success_type'    => new sfValidatorString(array('max_length' => 11)),
      'success_message' => new sfValidatorString(),
      'redirect_url'    => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('ap_success_logic_options[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApSuccessLogicOptions';
  }

}
