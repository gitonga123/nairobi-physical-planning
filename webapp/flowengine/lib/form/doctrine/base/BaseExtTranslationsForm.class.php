<?php

/**
 * ExtTranslations form base class.
 *
 * @method ExtTranslations getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseExtTranslationsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'locale'      => new sfWidgetFormInputText(),
      'table_class' => new sfWidgetFormInputText(),
      'field_name'  => new sfWidgetFormInputText(),
      'field_id'    => new sfWidgetFormInputText(),
      'trl_content' => new sfWidgetFormTextarea(),
      'option_id'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'locale'      => new sfValidatorString(array('max_length' => 50)),
      'table_class' => new sfValidatorString(array('max_length' => 250)),
      'field_name'  => new sfValidatorString(array('max_length' => 250)),
      'field_id'    => new sfValidatorInteger(),
      'trl_content' => new sfValidatorString(),
      'option_id'   => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ext_translations[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExtTranslations';
  }

}
