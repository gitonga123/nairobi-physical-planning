<?php

/**
 * Invoicetemplates filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseInvoicetemplatesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'title'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'applicationform'  => new sfWidgetFormFilterInput(),
      'applicationstage' => new sfWidgetFormFilterInput(),
      'content'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'max_duration'     => new sfWidgetFormFilterInput(),
      'due_duration'     => new sfWidgetFormFilterInput(),
      'invoice_number'   => new sfWidgetFormFilterInput(),
      'expiration_type'  => new sfWidgetFormFilterInput(),
      'payment_type'     => new sfWidgetFormFilterInput(),
      'qr_content'          => new sfWidgetFormFilterInput(),
      'receipt_content'          => new sfWidgetFormFilterInput(),
      'receipt_content_qr_code'          => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'title'            => new sfValidatorPass(array('required' => false)),
      'applicationform'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'applicationstage' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'content'          => new sfValidatorPass(array('required' => false)),
      'max_duration'     => new sfValidatorPass(array('required' => false)),
      'due_duration'     => new sfValidatorPass(array('required' => false)),
      'invoice_number'   => new sfValidatorPass(array('required' => false)),
      'expiration_type'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'payment_type'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'qr_content'          => new sfValidatorPass(array('required' => false)),
      'receipt_content'          => new sfValidatorPass(array('required' => false)),
      'receipt_content_qr_code'          => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('invoicetemplates_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Invoicetemplates';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'title'            => 'Text',
      'applicationform'  => 'Number',
      'applicationstage' => 'Number',
      'content'          => 'Text',
      'max_duration'     => 'Text',
      'due_duration'     => 'Text',
      'invoice_number'   => 'Text',
      'expiration_type'  => 'Number',
      'payment_type'     => 'Number',
      'qr_content'       => 'Text',
      'receipt_content'       => 'Text',
      'receipt_content_qr_code'       => 'Text',
    );
  }
}
