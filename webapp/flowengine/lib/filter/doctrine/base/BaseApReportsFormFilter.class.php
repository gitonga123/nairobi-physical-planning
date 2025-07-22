<?php

/**
 * ApReports filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApReportsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'report_access_key' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'report_access_key' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_reports_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApReports';
  }

  public function getFields()
  {
    return array(
      'form_id'           => 'Number',
      'report_access_key' => 'Text',
    );
  }
}
