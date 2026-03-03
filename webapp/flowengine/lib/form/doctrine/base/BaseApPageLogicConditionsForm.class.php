<?php

/**
 * ApPageLogicConditions form base class.
 *
 * @method ApPageLogicConditions getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApPageLogicConditionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'apc_id'         => new sfWidgetFormInputHidden(),
      'form_id'        => new sfWidgetFormInputText(),
      'target_page_id' => new sfWidgetFormInputText(),
      'element_name'   => new sfWidgetFormInputText(),
      'rule_condition' => new sfWidgetFormInputText(),
      'rule_keyword'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'apc_id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('apc_id')), 'empty_value' => $this->getObject()->get('apc_id'), 'required' => false)),
      'form_id'        => new sfValidatorInteger(),
      'target_page_id' => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'element_name'   => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'rule_condition' => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'rule_keyword'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_page_logic_conditions[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApPageLogicConditions';
  }

}
