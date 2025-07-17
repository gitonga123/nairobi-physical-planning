<?php

/**
 * Communications form base class.
 *
 * @method Communications getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCommunicationsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'architect_id'     => new sfWidgetFormInputText(),
      'reviewer_id'      => new sfWidgetFormInputText(),
      'application_id'   => new sfWidgetFormInputText(),
      'messageread'      => new sfWidgetFormInputText(),
      'content'          => new sfWidgetFormTextarea(),
      'action_timestamp' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'architect_id'     => new sfValidatorInteger(array('required' => false)),
      'reviewer_id'      => new sfValidatorInteger(array('required' => false)),
      'application_id'   => new sfValidatorInteger(array('required' => false)),
      'messageread'      => new sfValidatorInteger(array('required' => false)),
      'content'          => new sfValidatorString(array('required' => false)),
      'action_timestamp' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('communications[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Communications';
  }

}
