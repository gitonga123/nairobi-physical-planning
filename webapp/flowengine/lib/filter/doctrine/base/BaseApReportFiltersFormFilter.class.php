<?php

/**
 * ApReportFilters filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApReportFiltersFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_id'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'element_name'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'filter_condition' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'filter_keyword'   => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'form_id'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_id'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_name'     => new sfValidatorPass(array('required' => false)),
      'filter_condition' => new sfValidatorPass(array('required' => false)),
      'filter_keyword'   => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_report_filters_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApReportFilters';
  }

  public function getFields()
  {
    return array(
      'arf_id'           => 'Number',
      'form_id'          => 'Number',
      'chart_id'         => 'Number',
      'element_name'     => 'Text',
      'filter_condition' => 'Text',
      'filter_keyword'   => 'Text',
    );
  }
}
