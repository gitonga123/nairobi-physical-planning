<?php

/**
 * ApWorkflowLogicElements form base class.
 *
 * @method ApWorkflowLogicElements getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApWorkflowLogicElementsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'        => new sfWidgetFormInputHidden(),
      'element_id'     => new sfWidgetFormInputHidden(),
      'rule_show_hide' => new sfWidgetFormInputText(),
      'rule_all_any'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'form_id'        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'element_id'     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('element_id')), 'empty_value' => $this->getObject()->get('element_id'), 'required' => false)),
      'rule_show_hide' => new sfValidatorString(array('max_length' => 4, 'required' => false)),
      'rule_all_any'   => new sfValidatorString(array('max_length' => 3, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_workflow_logic_elements[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApWorkflowLogicElements';
  }

}
