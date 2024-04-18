<?php

/**
 * ApFormPayments filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFormPaymentsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'record_id'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'payment_id'            => new sfWidgetFormFilterInput(),
      'date_created'          => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'payment_date'          => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'payment_status'        => new sfWidgetFormFilterInput(),
      'payment_fullname'      => new sfWidgetFormFilterInput(),
      'payment_amount'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'payment_currency'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'payment_test_mode'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'payment_merchant_type' => new sfWidgetFormFilterInput(),
      'status'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'billing_street'        => new sfWidgetFormFilterInput(),
      'billing_city'          => new sfWidgetFormFilterInput(),
      'billing_state'         => new sfWidgetFormFilterInput(),
      'billing_zipcode'       => new sfWidgetFormFilterInput(),
      'billing_country'       => new sfWidgetFormFilterInput(),
      'same_shipping_address' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'shipping_street'       => new sfWidgetFormFilterInput(),
      'shipping_city'         => new sfWidgetFormFilterInput(),
      'shipping_state'        => new sfWidgetFormFilterInput(),
      'shipping_zipcode'      => new sfWidgetFormFilterInput(),
      'shipping_country'      => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'form_id'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'record_id'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'payment_id'            => new sfValidatorPass(array('required' => false)),
      'date_created'          => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'payment_date'          => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'payment_status'        => new sfValidatorPass(array('required' => false)),
      'payment_fullname'      => new sfValidatorPass(array('required' => false)),
      'payment_amount'        => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'payment_currency'      => new sfValidatorPass(array('required' => false)),
      'payment_test_mode'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'payment_merchant_type' => new sfValidatorPass(array('required' => false)),
      'status'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'billing_street'        => new sfValidatorPass(array('required' => false)),
      'billing_city'          => new sfValidatorPass(array('required' => false)),
      'billing_state'         => new sfValidatorPass(array('required' => false)),
      'billing_zipcode'       => new sfValidatorPass(array('required' => false)),
      'billing_country'       => new sfValidatorPass(array('required' => false)),
      'same_shipping_address' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'shipping_street'       => new sfValidatorPass(array('required' => false)),
      'shipping_city'         => new sfValidatorPass(array('required' => false)),
      'shipping_state'        => new sfValidatorPass(array('required' => false)),
      'shipping_zipcode'      => new sfValidatorPass(array('required' => false)),
      'shipping_country'      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_form_payments_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFormPayments';
  }

  public function getFields()
  {
    return array(
      'afp_id'                => 'Number',
      'form_id'               => 'Number',
      'record_id'             => 'Number',
      'payment_id'            => 'Text',
      'date_created'          => 'Date',
      'payment_date'          => 'Date',
      'payment_status'        => 'Text',
      'payment_fullname'      => 'Text',
      'payment_amount'        => 'Number',
      'payment_currency'      => 'Text',
      'payment_test_mode'     => 'Number',
      'payment_merchant_type' => 'Text',
      'status'                => 'Number',
      'billing_street'        => 'Text',
      'billing_city'          => 'Text',
      'billing_state'         => 'Text',
      'billing_zipcode'       => 'Text',
      'billing_country'       => 'Text',
      'same_shipping_address' => 'Number',
      'shipping_street'       => 'Text',
      'shipping_city'         => 'Text',
      'shipping_state'        => 'Text',
      'shipping_zipcode'      => 'Text',
      'shipping_country'      => 'Text',
    );
  }
}
