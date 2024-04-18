<?php

/**
 * Fee filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFeeFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'fee_category' => new sfWidgetFormFilterInput(),
      'fee_code'     => new sfWidgetFormFilterInput(),
      'description'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'amount'       => new sfWidgetFormFilterInput(),
      'invoiceid'    => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'fee_category' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'fee_code'     => new sfValidatorPass(array('required' => false)),
      'description'  => new sfValidatorPass(array('required' => false)),
      'amount'       => new sfValidatorPass(array('required' => false)),
      'invoiceid'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('fee_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Fee';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'fee_category' => 'Number',
      'fee_code'     => 'Text',
      'description'  => 'Text',
      'amount'       => 'Text',
      'invoiceid'    => 'Number',
    );
  }
}
