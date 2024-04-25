<?php

/**
 * WorkflowReviewers filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseWorkflowReviewersFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'workflow_id' => new sfWidgetFormFilterInput(),
      'reviewer_id' => new sfWidgetFormFilterInput(),
      'task_type'   => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'workflow_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'reviewer_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'task_type'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('workflow_reviewers_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'WorkflowReviewers';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'workflow_id' => 'Number',
      'reviewer_id' => 'Number',
      'task_type'   => 'Number',
    );
  }
}
