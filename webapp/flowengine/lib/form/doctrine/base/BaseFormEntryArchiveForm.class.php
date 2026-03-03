<?php

/**
 * FormEntryArchive form base class.
 *
 * @method FormEntryArchive getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormEntryArchiveForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'form_id'             => new sfWidgetFormInputText(),
      'entry_id'            => new sfWidgetFormInputText(),
      'user_id'             => new sfWidgetFormInputText(),
      'circulation_id'      => new sfWidgetFormInputText(),
      'approved'            => new sfWidgetFormInputText(),
      'application_id'      => new sfWidgetFormInputText(),
      'declined'            => new sfWidgetFormInputText(),
      'deleted_status'      => new sfWidgetFormInputText(),
      'saved_permit'        => new sfWidgetFormTextarea(),
      'previous_submission' => new sfWidgetFormInputText(),
      'parent_submission'   => new sfWidgetFormInputText(),
      'date_of_submission'  => new sfWidgetFormInputText(),
      'date_of_response'    => new sfWidgetFormInputText(),
      'date_of_issue'       => new sfWidgetFormInputText(),
      'observation'         => new sfWidgetFormTextarea(),
      'pdf_path'            => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'             => new sfValidatorInteger(array('required' => false)),
      'entry_id'            => new sfValidatorInteger(array('required' => false)),
      'user_id'             => new sfValidatorInteger(array('required' => false)),
      'circulation_id'      => new sfValidatorInteger(array('required' => false)),
      'approved'            => new sfValidatorInteger(array('required' => false)),
      'application_id'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'declined'            => new sfValidatorInteger(array('required' => false)),
      'deleted_status'      => new sfValidatorInteger(array('required' => false)),
      'saved_permit'        => new sfValidatorString(array('required' => false)),
      'previous_submission' => new sfValidatorInteger(),
      'parent_submission'   => new sfValidatorInteger(),
      'date_of_submission'  => new sfValidatorString(array('max_length' => 250)),
      'date_of_response'    => new sfValidatorString(array('max_length' => 250)),
      'date_of_issue'       => new sfValidatorString(array('max_length' => 250)),
      'observation'         => new sfValidatorString(),
      'pdf_path'            => new sfValidatorString(array('max_length' => 250)),
    ));

    $this->widgetSchema->setNameFormat('form_entry_archive[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormEntryArchive';
  }

}
