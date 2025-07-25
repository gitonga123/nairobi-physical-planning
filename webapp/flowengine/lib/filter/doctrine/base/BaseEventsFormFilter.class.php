<?php

/**
 * Events filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BaseEventsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'subject'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'content'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'location'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'is_application_related' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'related_application'    => new sfWidgetFormFilterInput(),
      'user_id'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'datesent'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'start_date'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'end_date'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'subject'                => new sfValidatorPass(array('required' => false)),
      'content'                => new sfValidatorPass(array('required' => false)),
      'location'               => new sfValidatorPass(array('required' => false)),
      'is_application_related' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'related_application'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'user_id'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'datesent'               => new sfValidatorPass(array('required' => false)),
      'start_date'             => new sfValidatorPass(array('required' => false)),
      'end_date'               => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('events_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Events';
  }

  public function getFields()
  {
    return array(
      'id'                     => 'Number',
      'subject'                => 'Text',
      'content'                => 'Text',
      'location'               => 'Text',
      'is_application_related' => 'Number',
      'related_application'    => 'Number',
      'user_id'                => 'Number',
      'datesent'               => 'Text',
      'start_date'             => 'Text',
      'end_date'               => 'Text',
    );
  }
}
