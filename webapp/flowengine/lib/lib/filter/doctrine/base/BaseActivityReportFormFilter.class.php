<?php

/**
 * ActivityReport filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseActivityReportFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'total' => new sfWidgetFormFilterInput(),
      'code'  => new sfWidgetFormFilterInput(),
      'type'  => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'total' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'code'  => new sfValidatorPass(array('required' => false)),
      'type'  => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_report_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityReport';
  }

  public function getFields()
  {
    return array(
      'id'    => 'Number',
      'total' => 'Number',
      'code'  => 'Text',
      'type'  => 'Text',
    );
  }
}
