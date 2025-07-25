<?php

/**
 * ReportFields form base class.
 *
 * @method ReportFields getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseReportFieldsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'report_id'    => new sfWidgetFormInputText(),
      'element'      => new sfWidgetFormTextarea(),
      'customheader' => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'report_id'    => new sfValidatorInteger(array('required' => false)),
      'element'      => new sfValidatorString(array('required' => false)),
      'customheader' => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('report_fields[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReportFields';
  }

}
