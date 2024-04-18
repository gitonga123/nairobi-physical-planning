<?php

/**
 * ReportFields filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseReportFieldsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'report_id'    => new sfWidgetFormFilterInput(),
      'element'      => new sfWidgetFormFilterInput(),
      'customheader' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'report_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element'      => new sfValidatorPass(array('required' => false)),
      'customheader' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('report_fields_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReportFields';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'report_id'    => 'Number',
      'element'      => 'Text',
      'customheader' => 'Text',
    );
  }
}
