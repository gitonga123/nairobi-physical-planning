<?php

/**
 * TaskFormsSettings form base class.
 *
 * @method TaskFormsSettings getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskFormsSettingsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'task_type'           => new sfWidgetFormInputText(),
      'task_department'     => new sfWidgetFormInputText(),
      'task_application_id' => new sfWidgetFormInputText(),
      'task_comment_sheet'  => new sfWidgetFormInputText(),
      'created_on'          => new sfWidgetFormInputText(),
      'updated_on'          => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'task_type'           => new sfValidatorInteger(),
      'task_department'     => new sfValidatorString(array('max_length' => 250)),
      'task_application_id' => new sfValidatorInteger(),
      'task_comment_sheet'  => new sfValidatorInteger(),
      'created_on'          => new sfValidatorString(array('max_length' => 250)),
      'updated_on'          => new sfValidatorString(array('max_length' => 250)),
    ));

    $this->widgetSchema->setNameFormat('task_forms_settings[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskFormsSettings';
  }

}
