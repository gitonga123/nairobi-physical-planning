<?php

/**
 * TaskTransfer filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseTaskTransferFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'task_id'      => new sfWidgetFormFilterInput(),
      'from_user_id' => new sfWidgetFormFilterInput(),
      'to_user_id'   => new sfWidgetFormFilterInput(),
      'reason'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_created' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'task_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'from_user_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'to_user_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'reason'       => new sfValidatorPass(array('required' => false)),
      'date_created' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('task_transfer_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TaskTransfer';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'task_id'      => 'Number',
      'from_user_id' => 'Number',
      'to_user_id'   => 'Number',
      'reason'       => 'Text',
      'date_created' => 'Text',
    );
  }
}
