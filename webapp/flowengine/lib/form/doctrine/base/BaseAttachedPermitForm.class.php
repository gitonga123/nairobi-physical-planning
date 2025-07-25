<?php

/**
 * AttachedPermit form base class.
 *
 * @method AttachedPermit getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseAttachedPermitForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'application_id' => new sfWidgetFormInputText(),
      'form_id'        => new sfWidgetFormInputText(),
      'entry_id'       => new sfWidgetFormInputText(),
      'attachedby_id'  => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'application_id' => new sfValidatorInteger(),
      'form_id'        => new sfValidatorInteger(),
      'entry_id'       => new sfValidatorInteger(),
      'attachedby_id'  => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('attached_permit[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AttachedPermit';
  }

}
