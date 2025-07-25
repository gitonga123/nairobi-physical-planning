<?php

/**
 * ApElementPrices form base class.
 *
 * @method ApElementPrices getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApElementPricesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'aep_id'     => new sfWidgetFormInputHidden(),
      'form_id'    => new sfWidgetFormInputText(),
      'element_id' => new sfWidgetFormInputText(),
      'option_id'  => new sfWidgetFormInputText(),
      'price'      => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'aep_id'     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('aep_id')), 'empty_value' => $this->getObject()->get('aep_id'), 'required' => false)),
      'form_id'    => new sfValidatorInteger(),
      'element_id' => new sfValidatorInteger(),
      'option_id'  => new sfValidatorInteger(array('required' => false)),
      'price'      => new sfValidatorNumber(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_element_prices[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApElementPrices';
  }

}
