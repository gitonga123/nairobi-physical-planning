<?php

/**
 * AppChange form base class.
 *
 * @method AppChange getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseAppChangeForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'stage_id'         => new sfWidgetFormInputText(),
      'form_id'          => new sfWidgetFormInputText(),
      'identifier_type'  => new sfWidgetFormInputText(),
      'app_identifier'   => new sfWidgetFormTextarea(),
      'identifier_start' => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'stage_id'         => new sfValidatorInteger(array('required' => false)),
      'form_id'          => new sfValidatorInteger(array('required' => false)),
      'identifier_type'  => new sfValidatorInteger(array('required' => false)),
      'app_identifier'   => new sfValidatorString(),
      'identifier_start' => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('app_change[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AppChange';
  }

}
