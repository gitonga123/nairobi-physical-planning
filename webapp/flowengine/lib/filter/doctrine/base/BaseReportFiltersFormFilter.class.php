<?php

/**
 * ReportFilters filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseReportFiltersFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'report_id'  => new sfWidgetFormFilterInput(),
      'element_id' => new sfWidgetFormFilterInput(),
      'exclusive'  => new sfWidgetFormFilterInput(),
      'value'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'report_id'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'exclusive'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'value'      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('report_filters_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReportFilters';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'report_id'  => 'Number',
      'element_id' => 'Number',
      'exclusive'  => 'Number',
      'value'      => 'Text',
    );
  }
}
