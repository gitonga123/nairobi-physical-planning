<?php

/**
 * SubMenuTasks filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSubMenuTasksFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'sub_menu_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'task_id'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'sub_menu_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'task_id'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('sub_menu_tasks_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubMenuTasks';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'sub_menu_id' => 'Number',
      'task_id'     => 'Number',
    );
  }
}
