<?php

/**
 * ApDropdownFilters form base class.
 *
 * @method ApDropdownFilters getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApDropdownFiltersForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'form_id'     => new sfWidgetFormInputText(),
      'element_id'  => new sfWidgetFormInputText(),
      'link_id'     => new sfWidgetFormInputText(),
      'option_id'   => new sfWidgetFormInputText(),
      'lioption_id' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'     => new sfValidatorInteger(),
      'element_id'  => new sfValidatorInteger(),
      'link_id'     => new sfValidatorInteger(),
      'option_id'   => new sfValidatorInteger(),
      'lioption_id' => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('ap_dropdown_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApDropdownFilters';
  }

}
