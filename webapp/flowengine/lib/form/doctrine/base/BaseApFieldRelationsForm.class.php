<?php

/**
 * ApFieldRelations form base class.
 *
 * @method ApFieldRelations getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFieldRelationsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'     => new sfWidgetFormInputHidden(),
      'field1' => new sfWidgetFormInputText(),
      'field2' => new sfWidgetFormInputText(),
      'formid' => new sfWidgetFormInputText(),
      'status' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'field1' => new sfValidatorInteger(),
      'field2' => new sfValidatorInteger(),
      'formid' => new sfValidatorInteger(),
      'status' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_field_relations[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFieldRelations';
  }

}
