<?php

/**
 * Task form base class.
 *
 * @method Task getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'type'            => new sfWidgetFormInputText(),
      'creator_user_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'owner_user_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Owner'), 'add_empty' => true)),
      'sheet_id'        => new sfWidgetFormInputText(),
      'duration'        => new sfWidgetFormInputText(),
      'start_date'      => new sfWidgetFormTextarea(),
      'end_date'        => new sfWidgetFormTextarea(),
      'priority'        => new sfWidgetFormInputText(),
      'is_leader'       => new sfWidgetFormInputText(),
      'active'          => new sfWidgetFormInputText(),
      'status'          => new sfWidgetFormInputText(),
      'last_update'     => new sfWidgetFormTextarea(),
      'date_created'    => new sfWidgetFormTextarea(),
      'remarks'         => new sfWidgetFormTextarea(),
      'application_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'task_stage'      => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'type'            => new sfValidatorInteger(array('required' => false)),
      'creator_user_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'nid', 'required' => false)),
      'owner_user_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Owner'), 'column' => 'nid', 'required' => false)),
      'sheet_id'        => new sfValidatorInteger(array('required' => false)),
      'duration'        => new sfValidatorInteger(array('required' => false)),
      'start_date'      => new sfValidatorString(),
      'end_date'        => new sfValidatorString(),
      'priority'        => new sfValidatorInteger(array('required' => false)),
      'is_leader'       => new sfValidatorInteger(array('required' => false)),
      'active'          => new sfValidatorInteger(array('required' => false)),
      'status'          => new sfValidatorInteger(array('required' => false)),
      'last_update'     => new sfValidatorString(),
      'date_created'    => new sfValidatorString(),
      'remarks'         => new sfValidatorString(),
      'application_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id', 'required' => false)),
      'task_stage'      => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('task[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Task';
  }

}
