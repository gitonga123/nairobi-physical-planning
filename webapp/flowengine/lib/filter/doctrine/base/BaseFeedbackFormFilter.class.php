<?php

/**
 * Feedback filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BaseFeedbackFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'subject'    => new sfWidgetFormFilterInput(),
      'type'       => new sfWidgetFormFilterInput(),
      'names'      => new sfWidgetFormFilterInput(),
      'email'      => new sfWidgetFormFilterInput(),
      'message'    => new sfWidgetFormFilterInput(),
      'created_on' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
    ));

    $this->setValidators(array(
      'subject'    => new sfValidatorPass(array('required' => false)),
      'type'       => new sfValidatorPass(array('required' => false)),
      'names'      => new sfValidatorPass(array('required' => false)),
      'email'      => new sfValidatorPass(array('required' => false)),
      'message'    => new sfValidatorPass(array('required' => false)),
      'created_on' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
    ));

    $this->widgetSchema->setNameFormat('feedback_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Feedback';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'subject'    => 'Text',
      'type'       => 'Text',
      'names'      => 'Text',
      'email'      => 'Text',
      'message'    => 'Text',
      'created_on' => 'Date',
    );
  }
}
