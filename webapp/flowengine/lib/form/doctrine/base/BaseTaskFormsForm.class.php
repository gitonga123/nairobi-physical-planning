<?php

/**
 * TaskForms form base class.
 *
 * @method TaskForms getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskFormsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'task_id'    => new sfWidgetFormInputText(),
      'form_id'    => new sfWidgetFormInputText(),
      'entry_id'   => new sfWidgetFormInputText(),
      'created_on' => new sfWidgetFormInputText(),
      'updated_on' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'task_id'    => new sfValidatorInteger(),
      'form_id'    => new sfValidatorInteger(),
      'entry_id'   => new sfValidatorInteger(),
      'created_on' => new sfValidatorString(array('max_length' => 250)),
      'updated_on' => new sfValidatorString(array('max_length' => 250)),
    ));

    $this->widgetSchema->setNameFormat('task_forms[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskForms';
  }

}
