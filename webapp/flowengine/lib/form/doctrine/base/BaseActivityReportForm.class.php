<?php

/**
 * ActivityReport form base class.
 *
 * @method ActivityReport getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseActivityReportForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'    => new sfWidgetFormInputHidden(),
      'total' => new sfWidgetFormInputText(),
      'code'  => new sfWidgetFormTextarea(),
      'type'  => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'total' => new sfValidatorInteger(array('required' => false)),
      'code'  => new sfValidatorString(array('required' => false)),
      'type'  => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('activity_report[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ActivityReport';
  }

}
