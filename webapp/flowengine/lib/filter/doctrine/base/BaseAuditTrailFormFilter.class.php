<?php

/**
 * AuditTrail filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseAuditTrailFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'          => new sfWidgetFormFilterInput(),
      'form_entry_id'    => new sfWidgetFormFilterInput(),
      'action'           => new sfWidgetFormFilterInput(),
      'action_timestamp' => new sfWidgetFormFilterInput(),
      'object_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'object_name'      => new sfWidgetFormFilterInput(),
      'before_values'    => new sfWidgetFormFilterInput(),
      'after_values'     => new sfWidgetFormFilterInput(),
      'ipaddress'        => new sfWidgetFormFilterInput(),
      'http_agent'       => new sfWidgetFormFilterInput(),
      'user_location'    => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'user_id'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_entry_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'action'           => new sfValidatorPass(array('required' => false)),
      'action_timestamp' => new sfValidatorPass(array('required' => false)),
      'object_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'object_name'      => new sfValidatorPass(array('required' => false)),
      'before_values'    => new sfValidatorPass(array('required' => false)),
      'after_values'     => new sfValidatorPass(array('required' => false)),
      'ipaddress'        => new sfValidatorPass(array('required' => false)),
      'http_agent'       => new sfValidatorPass(array('required' => false)),
      'user_location'    => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('audit_trail_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AuditTrail';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'user_id'          => 'Number',
      'form_entry_id'    => 'Number',
      'action'           => 'Text',
      'action_timestamp' => 'Text',
      'object_id'        => 'Number',
      'object_name'      => 'Text',
      'before_values'    => 'Text',
      'after_values'     => 'Text',
      'ipaddress'        => 'Text',
      'http_agent'       => 'Text',
      'user_location'    => 'Text',
    );
  }
}
