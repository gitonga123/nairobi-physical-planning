<?php

/**
 * ReportFilters form base class.
 *
 * @method ReportFilters getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseReportFiltersForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'report_id'  => new sfWidgetFormInputText(),
      'element_id' => new sfWidgetFormInputText(),
      'exclusive'  => new sfWidgetFormInputText(),
      'value'      => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'report_id'  => new sfValidatorInteger(array('required' => false)),
      'element_id' => new sfValidatorInteger(array('required' => false)),
      'exclusive'  => new sfValidatorInteger(array('required' => false)),
      'value'      => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('report_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReportFilters';
  }

}
