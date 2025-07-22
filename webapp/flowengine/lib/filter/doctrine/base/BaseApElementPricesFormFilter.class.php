<?php

/**
 * ApElementPrices filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApElementPricesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'element_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'option_id'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'price'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'form_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'option_id'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'price'      => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_element_prices_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApElementPrices';
  }

  public function getFields()
  {
    return array(
      'aep_id'     => 'Number',
      'form_id'    => 'Number',
      'element_id' => 'Number',
      'option_id'  => 'Number',
      'price'      => 'Number',
    );
  }
}
