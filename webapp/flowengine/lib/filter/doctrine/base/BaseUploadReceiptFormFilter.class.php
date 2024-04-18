<?php

/**
 * UploadReceipt filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseUploadReceiptFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'invoice_id' => new sfWidgetFormFilterInput(),
      'form_id'    => new sfWidgetFormFilterInput(),
      'entry_id'   => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'invoice_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entry_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('upload_receipt_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'UploadReceipt';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'invoice_id' => 'Number',
      'form_id'    => 'Number',
      'entry_id'   => 'Number',
    );
  }
}
