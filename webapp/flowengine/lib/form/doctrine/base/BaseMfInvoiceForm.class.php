<?php

/**
 * MfInvoice form base class.
 *
 * @method MfInvoice getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseMfInvoiceForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'app_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'invoice_number'  => new sfWidgetFormInputText(),
      'paid'            => new sfWidgetFormInputText(),
      'created_at'      => new sfWidgetFormDateTime(),
      'updated_at'      => new sfWidgetFormDateTime(),
      'expires_at'      => new sfWidgetFormDateTime(),
      'mda_code'        => new sfWidgetFormInputText(),
      'service_code'    => new sfWidgetFormInputText(),
      'branch'          => new sfWidgetFormInputText(),
      'due_date'        => new sfWidgetFormInputText(),
      'payer_id'        => new sfWidgetFormInputText(),
      'payer_name'      => new sfWidgetFormInputText(),
      'total_amount'    => new sfWidgetFormInputText(),
      'currency'        => new sfWidgetFormInputText(),
      'doc_ref_number'  => new sfWidgetFormInputText(),
      'template_id'     => new sfWidgetFormInputText(),
      'document_key'    => new sfWidgetFormInputText(),
      'remote_validate' => new sfWidgetFormInputText(),
      'pdf_path'        => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'app_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id', 'required' => false)),
      'invoice_number'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'paid'            => new sfValidatorInteger(array('required' => false)),
      'created_at'      => new sfValidatorDateTime(),
      'updated_at'      => new sfValidatorDateTime(),
      'expires_at'      => new sfValidatorDateTime(array('required' => false)),
      'mda_code'        => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'service_code'    => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'branch'          => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'due_date'        => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'payer_id'        => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'payer_name'      => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'total_amount'    => new sfValidatorNumber(array('required' => false)),
      'currency'        => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'doc_ref_number'  => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'template_id'     => new sfValidatorInteger(array('required' => false)),
      'document_key'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'remote_validate' => new sfValidatorInteger(array('required' => false)),
      'pdf_path'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('mf_invoice[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MfInvoice';
  }

}
