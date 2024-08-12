<?php

/**
 * TaskForms filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskFormsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'task_id'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'form_id'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'entry_id'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_on' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'updated_on' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'task_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entry_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_on' => new sfValidatorPass(array('required' => false)),
      'updated_on' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('task_forms_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskForms';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'task_id'    => 'Number',
      'form_id'    => 'Number',
      'entry_id'   => 'Number',
      'created_on' => 'Text',
      'updated_on' => 'Text',
    );
  }
}
