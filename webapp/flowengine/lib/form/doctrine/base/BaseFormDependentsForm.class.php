<?php

/**
 * FormDependents form base class.
 *
 * @method FormDependents getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormDependentsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'form_id'           => new sfWidgetFormInputText(),
      'element_id'        => new sfWidgetFormInputText(),
      'element_value'     => new sfWidgetFormTextarea(),
      'dependent_form_id' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'           => new sfValidatorInteger(array('required' => false)),
      'element_id'        => new sfValidatorInteger(array('required' => false)),
      'element_value'     => new sfValidatorString(),
      'dependent_form_id' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('form_dependents[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormDependents';
  }

}
