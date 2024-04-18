<?php

/**
 * SubMenuTasks form base class.
 *
 * @method SubMenuTasks getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSubMenuTasksForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'sub_menu_id' => new sfWidgetFormInputText(),
      'task_id'     => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'sub_menu_id' => new sfValidatorInteger(),
      'task_id'     => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('sub_menu_tasks[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubMenuTasks';
  }

}
