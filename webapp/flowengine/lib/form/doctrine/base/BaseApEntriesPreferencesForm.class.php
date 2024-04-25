<?php

/**
 * ApEntriesPreferences form base class.
 *
 * @method ApEntriesPreferences getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApEntriesPreferencesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                    => new sfWidgetFormInputHidden(),
      'form_id'               => new sfWidgetFormInputText(),
      'user_id'               => new sfWidgetFormInputText(),
      'entries_sort_by'       => new sfWidgetFormInputText(),
      'entries_enable_filter' => new sfWidgetFormInputText(),
      'entries_filter_type'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'               => new sfValidatorInteger(),
      'user_id'               => new sfValidatorInteger(),
      'entries_sort_by'       => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'entries_enable_filter' => new sfValidatorInteger(array('required' => false)),
      'entries_filter_type'   => new sfValidatorString(array('max_length' => 5, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_entries_preferences[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApEntriesPreferences';
  }

}
