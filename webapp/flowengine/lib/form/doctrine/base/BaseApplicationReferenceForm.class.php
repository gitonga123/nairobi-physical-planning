<?php

/**
 * ApplicationReference form base class.
 *
 * @method ApplicationReference getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApplicationReferenceForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'stage_id'       => new sfWidgetFormInputText(),
      'application_id' => new sfWidgetFormInputText(),
      'approved_by'    => new sfWidgetFormInputText(),
      'start_date'     => new sfWidgetFormTextarea(),
      'end_date'       => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'stage_id'       => new sfValidatorInteger(array('required' => false)),
      'application_id' => new sfValidatorInteger(array('required' => false)),
      'approved_by'    => new sfValidatorInteger(array('required' => false)),
      'start_date'     => new sfValidatorString(),
      'end_date'       => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('application_reference[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApplicationReference';
  }

}
