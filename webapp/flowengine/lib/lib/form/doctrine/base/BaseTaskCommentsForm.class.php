<?php

/**
 * TaskComments form base class.
 *
 * @method TaskComments getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskCommentsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'commentcontent' => new sfWidgetFormTextarea(),
      'date_created'   => new sfWidgetFormTextarea(),
      'reviewer_id'    => new sfWidgetFormInputText(),
      'task_id'        => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'commentcontent' => new sfValidatorString(),
      'date_created'   => new sfValidatorString(),
      'reviewer_id'    => new sfValidatorInteger(array('required' => false)),
      'task_id'        => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('task_comments[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskComments';
  }

}
