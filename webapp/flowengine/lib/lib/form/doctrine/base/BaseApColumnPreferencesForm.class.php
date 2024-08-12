<?php

/**
 * ApColumnPreferences form base class.
 *
 * @method ApColumnPreferences getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApColumnPreferencesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'acp_id'             => new sfWidgetFormInputHidden(),
      'form_id'            => new sfWidgetFormInputText(),
      'element_name'       => new sfWidgetFormInputText(),
      'position'           => new sfWidgetFormInputText(),
      'incomplete_entries' => new sfWidgetFormInputText(),
      'user_id'            => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'acp_id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('acp_id')), 'empty_value' => $this->getObject()->get('acp_id'), 'required' => false)),
      'form_id'            => new sfValidatorInteger(array('required' => false)),
      'element_name'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'position'           => new sfValidatorInteger(array('required' => false)),
      'incomplete_entries' => new sfValidatorInteger(array('required' => false)),
      'user_id'            => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_column_preferences[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApColumnPreferences';
  }

}
