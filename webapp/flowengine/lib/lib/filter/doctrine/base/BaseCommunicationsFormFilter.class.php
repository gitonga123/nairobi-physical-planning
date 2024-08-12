<?php

/**
 * Communications filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCommunicationsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'architect_id'     => new sfWidgetFormFilterInput(),
      'reviewer_id'      => new sfWidgetFormFilterInput(),
      'application_id'   => new sfWidgetFormFilterInput(),
      'messageread'      => new sfWidgetFormFilterInput(),
      'content'          => new sfWidgetFormFilterInput(),
      'action_timestamp' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'architect_id'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'reviewer_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'application_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'messageread'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'content'          => new sfValidatorPass(array('required' => false)),
      'action_timestamp' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('communications_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Communications';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'architect_id'     => 'Number',
      'reviewer_id'      => 'Number',
      'application_id'   => 'Number',
      'messageread'      => 'Number',
      'content'          => 'Text',
      'action_timestamp' => 'Text',
    );
  }
}
