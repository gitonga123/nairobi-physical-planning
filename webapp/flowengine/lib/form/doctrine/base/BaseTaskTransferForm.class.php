<?php

/**
 * TaskTransfer form base class.
 *
 * @method TaskTransfer getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskTransferForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'task_id'      => new sfWidgetFormInputText(),
      'from_user_id' => new sfWidgetFormInputText(),
      'to_user_id'   => new sfWidgetFormInputText(),
      'reason'       => new sfWidgetFormTextarea(),
      'date_created' => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'task_id'      => new sfValidatorInteger(array('required' => false)),
      'from_user_id' => new sfValidatorInteger(array('required' => false)),
      'to_user_id'   => new sfValidatorInteger(array('required' => false)),
      'reason'       => new sfValidatorString(),
      'date_created' => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('task_transfer[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskTransfer';
  }

}
