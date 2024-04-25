<?php

/**
 * AgendaColumnPreferences form base class.
 *
 * @method AgendaColumnPreferences getObject() Returns the current form's model object
 *
 * @package    permit
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAgendaColumnPreferencesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'acp_id'       => new sfWidgetFormInputHidden(),
      'form_id'      => new sfWidgetFormInputText(),
      'element_name' => new sfWidgetFormInputText(),
      'position'     => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'acp_id'       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('acp_id')), 'empty_value' => $this->getObject()->get('acp_id'), 'required' => false)),
      'form_id'      => new sfValidatorInteger(array('required' => false)),
      'element_name' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'position'     => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('agenda_column_preferences[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AgendaColumnPreferences';
  }

}
