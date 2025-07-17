<?php

/**
 * SubMenuButtons filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSubMenuButtonsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'sub_menu_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'button_id'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'order_no'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'backend'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'sub_menu_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'button_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'order_no'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'backend'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('sub_menu_buttons_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubMenuButtons';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'sub_menu_id' => 'Number',
      'button_id'   => 'Number',
      'order_no'    => 'Number',
      'backend'     => 'Number',
    );
  }
}
