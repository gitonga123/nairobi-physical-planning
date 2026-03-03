<?php

/**
 * UploadReceipt form base class.
 *
 * @method UploadReceipt getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseUploadReceiptForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'invoice_id' => new sfWidgetFormInputText(),
      'form_id'    => new sfWidgetFormInputText(),
      'entry_id'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'invoice_id' => new sfValidatorInteger(array('required' => false)),
      'form_id'    => new sfValidatorInteger(array('required' => false)),
      'entry_id'   => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('upload_receipt[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'UploadReceipt';
  }

}
