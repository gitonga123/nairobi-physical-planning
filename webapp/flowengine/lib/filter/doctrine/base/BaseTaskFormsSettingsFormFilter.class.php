<?php

/**
 * TaskFormsSettings filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskFormsSettingsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'task_type'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'task_department'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'task_application_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'task_comment_sheet'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_on'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'updated_on'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'task_type'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'task_department'     => new sfValidatorPass(array('required' => false)),
      'task_application_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'task_comment_sheet'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_on'          => new sfValidatorPass(array('required' => false)),
      'updated_on'          => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('task_forms_settings_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskFormsSettings';
  }

  public function getFields()
  {
    return array(
      'id'                  => 'Number',
      'task_type'           => 'Number',
      'task_department'     => 'Text',
      'task_application_id' => 'Number',
      'task_comment_sheet'  => 'Number',
      'created_on'          => 'Text',
      'updated_on'          => 'Text',
    );
  }
}
