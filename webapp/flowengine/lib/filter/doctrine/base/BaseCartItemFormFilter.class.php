<?php

/**
 * CartItem filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCartItemFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'cart_id'    => new sfWidgetFormFilterInput(),
      'invoice_id' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'cart_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'invoice_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('cart_item_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CartItem';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'cart_id'    => 'Number',
      'invoice_id' => 'Number',
    );
  }
}
