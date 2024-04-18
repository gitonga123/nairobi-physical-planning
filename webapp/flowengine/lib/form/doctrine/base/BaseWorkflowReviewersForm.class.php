<?php

/**
 * WorkflowReviewers form base class.
 *
 * @method WorkflowReviewers getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseWorkflowReviewersForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'workflow_id' => new sfWidgetFormInputText(),
      'reviewer_id' => new sfWidgetFormInputText(),
      'task_type'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'workflow_id' => new sfValidatorInteger(array('required' => false)),
      'reviewer_id' => new sfValidatorInteger(array('required' => false)),
      'task_type'   => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('workflow_reviewers[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'WorkflowReviewers';
  }

}
