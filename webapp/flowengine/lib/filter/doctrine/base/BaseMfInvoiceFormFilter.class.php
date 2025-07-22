<?php

/**
 * MfInvoice filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseMfInvoiceFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'app_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'invoice_number'  => new sfWidgetFormFilterInput(),
      'paid'            => new sfWidgetFormFilterInput(),
      'created_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'expires_at'      => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'mda_code'        => new sfWidgetFormFilterInput(),
      'service_code'    => new sfWidgetFormFilterInput(),
      'branch'          => new sfWidgetFormFilterInput(),
      'due_date'        => new sfWidgetFormFilterInput(),
      'payer_id'        => new sfWidgetFormFilterInput(),
      'payer_name'      => new sfWidgetFormFilterInput(),
      'total_amount'    => new sfWidgetFormFilterInput(),
      'currency'        => new sfWidgetFormFilterInput(),
      'doc_ref_number'  => new sfWidgetFormFilterInput(),
      'template_id'     => new sfWidgetFormFilterInput(),
      'document_key'    => new sfWidgetFormFilterInput(),
      'remote_validate' => new sfWidgetFormFilterInput(),
      'pdf_path'        => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'app_id'          => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id')),
      'invoice_number'  => new sfValidatorPass(array('required' => false)),
      'paid'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'expires_at'      => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'mda_code'        => new sfValidatorPass(array('required' => false)),
      'service_code'    => new sfValidatorPass(array('required' => false)),
      'branch'          => new sfValidatorPass(array('required' => false)),
      'due_date'        => new sfValidatorPass(array('required' => false)),
      'payer_id'        => new sfValidatorPass(array('required' => false)),
      'payer_name'      => new sfValidatorPass(array('required' => false)),
      'total_amount'    => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'currency'        => new sfValidatorPass(array('required' => false)),
      'doc_ref_number'  => new sfValidatorPass(array('required' => false)),
      'template_id'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'document_key'    => new sfValidatorPass(array('required' => false)),
      'remote_validate' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'pdf_path'        => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('mf_invoice_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MfInvoice';
  }

  public function getFields()
  {
    return array(
      'id'              => 'Number',
      'app_id'          => 'ForeignKey',
      'invoice_number'  => 'Text',
      'paid'            => 'Number',
      'created_at'      => 'Date',
      'updated_at'      => 'Date',
      'expires_at'      => 'Date',
      'mda_code'        => 'Text',
      'service_code'    => 'Text',
      'branch'          => 'Text',
      'due_date'        => 'Text',
      'payer_id'        => 'Text',
      'payer_name'      => 'Text',
      'total_amount'    => 'Number',
      'currency'        => 'Text',
      'doc_ref_number'  => 'Text',
      'template_id'     => 'Number',
      'document_key'    => 'Text',
      'remote_validate' => 'Number',
      'pdf_path'        => 'Text',
    );
  }
}
