<?php

/**
 * PermitCheckerConfig form base class.
 *
 * @method PermitCheckerConfig getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BasePermitCheckerConfigForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'permit_template_id' => new sfWidgetFormInputText(),
      'reference_object'   => new sfWidgetFormInputText(),
      'label_to_show'      => new sfWidgetFormInputText(),
      'value_to_show'      => new sfWidgetFormInputText(),
      'sequence_no'        => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'permit_template_id' => new sfValidatorInteger(array('required' => false)),
      'reference_object'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'label_to_show'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'value_to_show'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'sequence_no'        => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('permit_checker_config[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PermitCheckerConfig';
  }

}
