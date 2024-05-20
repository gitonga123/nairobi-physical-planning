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
            )
        ),
      'fee_code'     => new sfWidgetFormInputText(),
      'description'  => new sfWidgetFormTextarea(),
      'amount'       => new sfWidgetFormInputText(),
      'invoiceid'    => new sfWidgetFormChoice(
            array(
                'choices' => Doctrine_Core::getTable('Invoicetemplates')->getInvoiceTemplates()
            )
        ),
	   //OTB Start Patch - For Implementing Finance Bills
      'percentage' => new sfWidgetFormInput(),
      'fee_type'      => new sfWidgetFormSelect(array('choices' => array('fixed' => 'Fixed Amount' , 'percentage' => 'Percentage of Base Field' ,'range' => 'Finance Bill Calculation' ,'formula' => 'Mathematical Formula'))),
	  'base_field' => new sfWidgetFormChoice(array('choices' => array())),
      'minimum_fee'      => new sfWidgetFormInputText(),
	   //OTB End Patch - For Implementing Finance Bills
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'fee_category' => new sfValidatorInteger(array('required' => false)),
      'fee_code'     => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'description'  => new sfValidatorString(),
      'amount'       => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'invoiceid'    => new sfValidatorInteger(array('required' => false)),
	   //OTB Start Patch - For Implementing Finance Bills
      'percentage' => new sfValidatorString(array('max_length' => 100,'required' => false)),
      'fee_type'      => new sfValidatorChoice(array('choices' => array('fixed', 'percentage','range','range_percentage','formula'))),
	  'base_field'	  => new sfValidatorInteger(array('required' => false)),
      'minimum_fee'      => new sfValidatorString(array('max_length' => 100, 'required' => false)),
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
