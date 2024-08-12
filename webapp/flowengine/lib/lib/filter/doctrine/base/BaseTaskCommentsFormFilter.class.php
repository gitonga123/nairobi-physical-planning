<?php

/**
 * TaskComments filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskCommentsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'commentcontent' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_created'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'reviewer_id'    => new sfWidgetFormFilterInput(),
      'task_id'        => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'commentcontent' => new sfValidatorPass(array('required' => false)),
      'date_created'   => new sfValidatorPass(array('required' => false)),
      'reviewer_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'task_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('task_comments_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskComments';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'commentcontent' => 'Text',
      'date_created'   => 'Text',
      'reviewer_id'    => 'Number',
      'task_id'        => 'Number',
    );
  }
}
