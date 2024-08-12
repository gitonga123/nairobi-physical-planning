<?php

/**
 * ApPageLogic form base class.
 *
 * @method ApPageLogic getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApPageLogicForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'      => new sfWidgetFormInputHidden(),
      'page_id'      => new sfWidgetFormInputHidden(),
      'rule_all_any' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'form_id'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'page_id'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('page_id')), 'empty_value' => $this->getObject()->get('page_id'), 'required' => false)),
      'rule_all_any' => new sfValidatorString(array('max_length' => 3, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_page_logic[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApPageLogic';
  }

}
