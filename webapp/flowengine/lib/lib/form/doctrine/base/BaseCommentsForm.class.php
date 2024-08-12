<?php

/**
 * Comments form base class.
 *
 * @method Comments getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCommentsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'circulation_id' => new sfWidgetFormInputText(),
      'slot_id'        => new sfWidgetFormInputText(),
      'form_id'        => new sfWidgetFormInputText(),
      'field_id'       => new sfWidgetFormInputText(),
      'comment'        => new sfWidgetFormTextarea(),
      'resolved'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'circulation_id' => new sfValidatorInteger(),
      'slot_id'        => new sfValidatorInteger(),
      'form_id'        => new sfValidatorInteger(),
      'field_id'       => new sfValidatorInteger(),
      'comment'        => new sfValidatorString(),
      'resolved'       => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('comments[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Comments';
  }

}
