<?php

/**
 * SubMenuButtons form base class.
 *
 * @method SubMenuButtons getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSubMenuButtonsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'sub_menu_id' => new sfWidgetFormInputText(),
      'button_id'   => new sfWidgetFormInputText(),
      'order_no'    => new sfWidgetFormInputText(),
      'backend'     => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'sub_menu_id' => new sfValidatorInteger(),
      'button_id'   => new sfValidatorInteger(),
      'order_no'    => new sfValidatorInteger(),
      'backend'     => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('sub_menu_buttons[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubMenuButtons';
  }

}
