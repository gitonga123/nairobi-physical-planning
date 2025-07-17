<?php

/**
 * TaskQueue filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskQueueFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'current_task_id' => new sfWidgetFormFilterInput(),
      'next_task_id'    => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'current_task_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'next_task_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('task_queue_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskQueue';
  }

  public function getFields()
  {
    return array(
      'id'              => 'Number',
      'current_task_id' => 'Number',
      'next_task_id'    => 'Number',
    );
  }
}
