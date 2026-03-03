<?php

/**
 * Task filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'type'            => new sfWidgetFormFilterInput(),
      'creator_user_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'owner_user_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Owner'), 'add_empty' => true)),
      'sheet_id'        => new sfWidgetFormFilterInput(),
      'duration'        => new sfWidgetFormFilterInput(),
      'start_date'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'end_date'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'priority'        => new sfWidgetFormFilterInput(),
      'is_leader'       => new sfWidgetFormFilterInput(),
      'active'          => new sfWidgetFormFilterInput(),
      'status'          => new sfWidgetFormFilterInput(),
      'last_update'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_created'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'remarks'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'application_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'task_stage'      => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'type'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'creator_user_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Creator'), 'column' => 'nid')),
      'owner_user_id'   => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Owner'), 'column' => 'nid')),
      'sheet_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'duration'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'start_date'      => new sfValidatorPass(array('required' => false)),
      'end_date'        => new sfValidatorPass(array('required' => false)),
      'priority'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'is_leader'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'active'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'status'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'last_update'     => new sfValidatorPass(array('required' => false)),
      'date_created'    => new sfValidatorPass(array('required' => false)),
      'remarks'         => new sfValidatorPass(array('required' => false)),
      'application_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id')),
      'task_stage'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('task_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Task';
  }

  public function getFields()
  {
    return array(
      'id'              => 'Number',
      'type'            => 'Number',
      'creator_user_id' => 'ForeignKey',
      'owner_user_id'   => 'ForeignKey',
      'sheet_id'        => 'Number',
      'duration'        => 'Number',
      'start_date'      => 'Text',
      'end_date'        => 'Text',
      'priority'        => 'Number',
      'is_leader'       => 'Number',
      'active'          => 'Number',
      'status'          => 'Number',
      'last_update'     => 'Text',
      'date_created'    => 'Text',
      'remarks'         => 'Text',
      'application_id'  => 'ForeignKey',
      'task_stage'      => 'Number',
    );
  }
}
