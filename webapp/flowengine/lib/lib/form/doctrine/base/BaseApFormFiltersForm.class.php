<?php

/**
 * ApFormFilters form base class.
 *
 * @method ApFormFilters getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFormFiltersForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'aff_id'             => new sfWidgetFormInputHidden(),
      'form_id'            => new sfWidgetFormInputText(),
      'element_name'       => new sfWidgetFormInputText(),
      'filter_condition'   => new sfWidgetFormInputText(),
      'filter_keyword'     => new sfWidgetFormInputText(),
      'user_id'            => new sfWidgetFormInputText(),
      'incomplete_entries' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'aff_id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('aff_id')), 'empty_value' => $this->getObject()->get('aff_id'), 'required' => false)),
      'form_id'            => new sfValidatorInteger(),
      'element_name'       => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'filter_condition'   => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'filter_keyword'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'user_id'            => new sfValidatorInteger(array('required' => false)),
      'incomplete_entries' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_form_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFormFilters';
  }

}
