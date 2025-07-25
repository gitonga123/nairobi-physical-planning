<?php

/**
 * ApElementOptions form base class.
 *
 * @method ApElementOptions getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApElementOptionsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'aeo_id'            => new sfWidgetFormInputHidden(),
      'form_id'           => new sfWidgetFormInputText(),
      'element_id'        => new sfWidgetFormInputText(),
      'option_id'         => new sfWidgetFormInputText(),
      'position'          => new sfWidgetFormInputText(),
      'option_text'       => new sfWidgetFormTextarea(),
      'option_is_default' => new sfWidgetFormInputText(),
      'live'              => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'aeo_id'            => new sfValidatorChoice(array('choices' => array($this->getObject()->get('aeo_id')), 'empty_value' => $this->getObject()->get('aeo_id'), 'required' => false)),
      'form_id'           => new sfValidatorInteger(array('required' => false)),
      'element_id'        => new sfValidatorInteger(array('required' => false)),
      'option_id'         => new sfValidatorInteger(array('required' => false)),
      'position'          => new sfValidatorInteger(array('required' => false)),
      'option_text'       => new sfValidatorString(array('required' => false)),
      'option_is_default' => new sfValidatorInteger(array('required' => false)),
      'live'              => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_element_options[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApElementOptions';
  }

}
