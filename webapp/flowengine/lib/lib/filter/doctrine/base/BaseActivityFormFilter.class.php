<?php

/**
 * Activity filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseActivityFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'          => new sfWidgetFormFilterInput(),
      'form_entry_id'    => new sfWidgetFormFilterInput(),
      'action'           => new sfWidgetFormFilterInput(),
      'action_timestamp' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'user_id'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_entry_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'action'           => new sfValidatorPass(array('required' => false)),
      'action_timestamp' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Activity';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'user_id'          => 'Number',
      'form_entry_id'    => 'Number',
      'action'           => 'Text',
      'action_timestamp' => 'Text',
    );
  }
}
