<?php

/**
 * ApSuccessLogicConditions form base class.
 *
 * @method ApSuccessLogicConditions getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApSuccessLogicConditionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'slc_id'         => new sfWidgetFormInputHidden(),
      'form_id'        => new sfWidgetFormInputHidden(),
      'target_rule_id' => new sfWidgetFormInputHidden(),
      'element_name'   => new sfWidgetFormInputText(),
      'rule_condition' => new sfWidgetFormInputText(),
      'rule_keyword'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'slc_id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('slc_id')), 'empty_value' => $this->getObject()->get('slc_id'), 'required' => false)),
      'form_id'        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'target_rule_id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('target_rule_id')), 'empty_value' => $this->getObject()->get('target_rule_id'), 'required' => false)),
      'element_name'   => new sfValidatorString(array('max_length' => 50)),
      'rule_condition' => new sfValidatorString(array('max_length' => 15)),
      'rule_keyword'   => new sfValidatorString(array('max_length' => 255)),
    ));

    $this->widgetSchema->setNameFormat('ap_success_logic_conditions[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApSuccessLogicConditions';
  }

}
