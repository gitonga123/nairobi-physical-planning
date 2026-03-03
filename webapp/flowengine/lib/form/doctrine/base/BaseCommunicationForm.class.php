<?php

/**
 * Communication form base class.
 *
 * @method Communication getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCommunicationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'sender'         => new sfWidgetFormInputText(),
      'receiver'       => new sfWidgetFormInputText(),
      'message'        => new sfWidgetFormTextarea(),
      'reply'          => new sfWidgetFormInputText(),
      'isread'         => new sfWidgetFormInputText(),
      'created_on'     => new sfWidgetFormInputText(),
      'application_id' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'sender'         => new sfValidatorInteger(),
      'receiver'       => new sfValidatorInteger(),
      'message'        => new sfValidatorString(array('required' => false)),
      'reply'          => new sfValidatorInteger(array('required' => false)),
      'isread'         => new sfValidatorInteger(array('required' => false)),
      'created_on'     => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'application_id' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('communication[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Communication';
  }

}
