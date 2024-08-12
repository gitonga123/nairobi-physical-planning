<?php

/**
 * InvoiceApiAccount filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseInvoiceApiAccountFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'api_key'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'api_secret' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'mda_name'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'mda_branch' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'api_key'    => new sfValidatorPass(array('required' => false)),
      'api_secret' => new sfValidatorPass(array('required' => false)),
      'mda_name'   => new sfValidatorPass(array('required' => false)),
      'mda_branch' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('invoice_api_account_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'InvoiceApiAccount';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'api_key'    => 'Text',
      'api_secret' => 'Text',
      'mda_name'   => 'Text',
      'mda_branch' => 'Text',
    );
  }
}
