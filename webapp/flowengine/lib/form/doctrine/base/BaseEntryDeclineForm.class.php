<?php

/**
 * EntryDecline form base class.
 *
 * @method EntryDecline getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseEntryDeclineForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'entry_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'description' => new sfWidgetFormTextarea(),
      'created_at'  => new sfWidgetFormDateTime(),
      'updated_at'  => new sfWidgetFormDateTime(),
      'resolved'    => new sfWidgetFormInputText(),
      'edit_fields' => new sfWidgetFormTextarea(),
      'declined_by' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'entry_id'    => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id', 'required' => false)),
      'description' => new sfValidatorString(array('required' => false)),
      'created_at'  => new sfValidatorDateTime(),
      'updated_at'  => new sfValidatorDateTime(),
      'resolved'    => new sfValidatorInteger(array('required' => false)),
      'edit_fields' => new sfValidatorString(array('required' => false)),
      'declined_by' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entry_decline[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntryDecline';
  }

}
