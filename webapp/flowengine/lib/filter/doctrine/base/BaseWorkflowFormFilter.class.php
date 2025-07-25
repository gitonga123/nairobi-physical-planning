<?php

/**
 * Workflow filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseWorkflowFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'workflow_title' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'workflow_type'  => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'workflow_title' => new sfValidatorPass(array('required' => false)),
      'workflow_type'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('workflow_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Workflow';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'workflow_title' => 'Text',
      'workflow_type'  => 'Number',
    );
  }
}
