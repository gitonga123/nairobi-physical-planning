<?php

/**
 * ApReportFilters form base class.
 *
 * @method ApReportFilters getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApReportFiltersForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'arf_id'           => new sfWidgetFormInputHidden(),
      'form_id'          => new sfWidgetFormInputText(),
      'chart_id'         => new sfWidgetFormInputText(),
      'element_name'     => new sfWidgetFormInputText(),
      'filter_condition' => new sfWidgetFormInputText(),
      'filter_keyword'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'arf_id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('arf_id')), 'empty_value' => $this->getObject()->get('arf_id'), 'required' => false)),
      'form_id'          => new sfValidatorInteger(),
      'chart_id'         => new sfValidatorInteger(),
      'element_name'     => new sfValidatorString(array('max_length' => 50, 'required' => false)),
      'filter_condition' => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'filter_keyword'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_report_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApReportFilters';
  }

}
