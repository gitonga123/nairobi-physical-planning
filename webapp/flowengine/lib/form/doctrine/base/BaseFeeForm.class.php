<?php

/**
 * Fee form base class.
 *
 * @method Fee getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFeeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'fee_category'     => new sfWidgetFormChoice(
        array(
          'choices' => Doctrine_Core::getTable('FeeCategory')->getCategories()
        ),
        array('class' => 'form-control')
      ),
      'fee_code'     => new sfWidgetFormInputText(array(),array('class' => 'form-control')),
      'description'  => new sfWidgetFormTextarea(array(),array('class' => 'form-control')),
      'amount'       => new sfWidgetFormInputText(array(),array('class' => 'form-control')),
      'invoiceid'    => new sfWidgetFormChoice(
        array(
          'choices' => Doctrine_Core::getTable('Invoicetemplates')->getInvoiceTemplates()
        ),
        array('class' => 'form-control')
      ),
      //OTB Start Patch - For Implementing Finance Bills
      'percentage' => new sfWidgetFormInput(array(),array('class' => 'form-control')),
      'fee_type'      => new sfWidgetFormSelect(array('choices' => array('fixed' => 'Fixed Amount', 'percentage' => 'Percentage of Base Field', 'range' => 'Finance Bill Calculation', 'formula' => 'Mathematical Formula')), array('class' => 'form-control')),
      'base_field' => new sfWidgetFormChoice(array('choices' => array()), array('class' => 'form-control')),
      'minimum_fee'      => new sfWidgetFormInputText(array(),array('class' => 'form-control')),
      //OTB End Patch - For Implementing Finance Bills
      'submission_fee' => new sfWidgetFormChoice(array('choices' => array('No','Yes')), array('class' => 'form-control'))
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'fee_category' => new sfValidatorInteger(array('required' => false)),
      'fee_code'     => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'description'  => new sfValidatorString(),
      'amount'       => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'invoiceid'    => new sfValidatorInteger(array('required' => false)),
      //OTB Start Patch - For Implementing Finance Bills
      'percentage' => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'fee_type'      => new sfValidatorChoice(array('choices' => array('fixed', 'percentage', 'range', 'range_percentage', 'formula'))),
      'base_field'    => new sfValidatorInteger(array('required' => false)),
      'minimum_fee'      => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'submission_fee'      => new sfValidatorBoolean(),
      //OTB End Patch - For Implementing Finance Bills
    ));

    $this->widgetSchema->setNameFormat('fee[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Fee';
  }
}
