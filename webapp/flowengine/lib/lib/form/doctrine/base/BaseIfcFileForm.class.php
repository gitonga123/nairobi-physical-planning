<?php

/**
 * Activity form base class.
 *
 * @method Activity getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseIfcFileForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'application'          => new sfWidgetFormInputText(),
      'message'    => new sfWidgetFormInputText(),
      'status'           => new sfWidgetFormTextarea(),
      'time_stamp' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'application'          => new sfValidatorString(array('required' => true)),
      'message'    => new sfValidatorString(array('required' => false)),
      'status'           => new sfValidatorString(array('required' => true)),
      'time_stamp' => new sfValidatorString(array('required' => true)),
    ));

    $this->widgetSchema->setNameFormat('ifc_file[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'IfcFile';
  }

}
