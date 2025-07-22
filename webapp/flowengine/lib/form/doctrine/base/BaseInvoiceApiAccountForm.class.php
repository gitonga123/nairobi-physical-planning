<?php

/**
 * InvoiceApiAccount form base class.
 *
 * @method InvoiceApiAccount getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseInvoiceApiAccountForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'api_key'    => new sfWidgetFormInputText(),
      'api_secret' => new sfWidgetFormInputText(),
      'mda_name'   => new sfWidgetFormInputText(),
      'mda_branch' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'api_key'    => new sfValidatorString(),
      'api_secret' => new sfValidatorString(),
      'mda_name'   => new sfValidatorString(),
      'mda_branch' => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('invoice_api_account[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'InvoiceApiAccount';
  }

}
