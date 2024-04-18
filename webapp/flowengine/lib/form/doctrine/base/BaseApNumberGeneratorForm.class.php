<?php

/**
 * ApNumberGenerator form base class.
 *
 * @method ApNumberGenerator getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApNumberGeneratorForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'            => new sfWidgetFormInputHidden(),
      'application_number' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'form_id'            => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'application_number' => new sfValidatorString(array('max_length' => 255)),
    ));

    $this->widgetSchema->setNameFormat('ap_number_generator[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApNumberGenerator';
  }

}
