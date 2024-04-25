<?php

/**
 * ApFormPayments form base class.
 *
 * @method ApFormPayments getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFormPaymentsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'afp_id'                => new sfWidgetFormInputHidden(),
      'form_id'               => new sfWidgetFormInputText(),
      'record_id'             => new sfWidgetFormInputText(),
      'payment_id'            => new sfWidgetFormInputText(),
      'date_created'          => new sfWidgetFormDateTime(),
      'payment_date'          => new sfWidgetFormDateTime(),
      'payment_status'        => new sfWidgetFormInputText(),
      'payment_fullname'      => new sfWidgetFormInputText(),
      'payment_amount'        => new sfWidgetFormInputText(),
      'payment_currency'      => new sfWidgetFormInputText(),
      'payment_test_mode'     => new sfWidgetFormInputText(),
      'payment_merchant_type' => new sfWidgetFormInputText(),
      'status'                => new sfWidgetFormInputText(),
      'billing_street'        => new sfWidgetFormInputText(),
      'billing_city'          => new sfWidgetFormInputText(),
      'billing_state'         => new sfWidgetFormInputText(),
      'billing_zipcode'       => new sfWidgetFormInputText(),
      'billing_country'       => new sfWidgetFormInputText(),
      'same_shipping_address' => new sfWidgetFormInputText(),
      'shipping_street'       => new sfWidgetFormInputText(),
      'shipping_city'         => new sfWidgetFormInputText(),
      'shipping_state'        => new sfWidgetFormInputText(),
      'shipping_zipcode'      => new sfWidgetFormInputText(),
      'shipping_country'      => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'afp_id'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('afp_id')), 'empty_value' => $this->getObject()->get('afp_id'), 'required' => false)),
      'form_id'               => new sfValidatorInteger(),
      'record_id'             => new sfValidatorInteger(),
      'payment_id'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'date_created'          => new sfValidatorDateTime(array('required' => false)),
      'payment_date'          => new sfValidatorDateTime(array('required' => false)),
      'payment_status'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'payment_fullname'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'payment_amount'        => new sfValidatorNumber(array('required' => false)),
      'payment_currency'      => new sfValidatorString(array('max_length' => 3, 'required' => false)),
      'payment_test_mode'     => new sfValidatorInteger(array('required' => false)),
      'payment_merchant_type' => new sfValidatorString(array('max_length' => 25, 'required' => false)),
      'status'                => new sfValidatorInteger(array('required' => false)),
      'billing_street'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'billing_city'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'billing_state'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'billing_zipcode'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'billing_country'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'same_shipping_address' => new sfValidatorInteger(array('required' => false)),
      'shipping_street'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'shipping_city'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'shipping_state'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'shipping_zipcode'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'shipping_country'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_form_payments[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFormPayments';
  }

}
