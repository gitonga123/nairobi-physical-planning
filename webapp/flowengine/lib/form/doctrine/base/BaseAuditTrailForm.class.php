<?php

/**
 * AuditTrail form base class.
 *
 * @method AuditTrail getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseAuditTrailForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'user_id'          => new sfWidgetFormInputText(),
      'form_entry_id'    => new sfWidgetFormInputText(),
      'action'           => new sfWidgetFormTextarea(),
      'action_timestamp' => new sfWidgetFormInputText(),
      'object_id'        => new sfWidgetFormInputText(),
      'object_name'      => new sfWidgetFormInputText(),
      'before_values'    => new sfWidgetFormTextarea(),
      'after_values'     => new sfWidgetFormTextarea(),
      'ipaddress'        => new sfWidgetFormInputText(),
      'http_agent'       => new sfWidgetFormInputText(),
      'user_location'    => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'user_id'          => new sfValidatorInteger(array('required' => false)),
      'form_entry_id'    => new sfValidatorInteger(array('required' => false)),
      'action'           => new sfValidatorString(array('required' => false)),
      'action_timestamp' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'object_id'        => new sfValidatorInteger(),
      'object_name'      => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'before_values'    => new sfValidatorString(array('required' => false)),
      'after_values'     => new sfValidatorString(array('required' => false)),
      'ipaddress'        => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'http_agent'       => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'user_location'    => new sfValidatorString(array('max_length' => 250, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('audit_trail[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AuditTrail';
  }

}
