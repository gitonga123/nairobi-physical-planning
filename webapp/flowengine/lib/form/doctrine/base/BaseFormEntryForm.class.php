<?php

/**
 * FormEntry form base class.
 *
 * @method FormEntry getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormEntryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                    => new sfWidgetFormInputHidden(),
      'form_id'               => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ApForms'), 'add_empty' => true)),
      'entry_id'              => new sfWidgetFormInputText(),
      'user_id'               => new sfWidgetFormInputText(),
      'circulation_id'        => new sfWidgetFormInputText(),
      'approved'              => new sfWidgetFormInputText(),
      'application_id'        => new sfWidgetFormInputText(),
      'declined'              => new sfWidgetFormInputText(),
      'deleted_status'        => new sfWidgetFormInputText(),
      'previous_submission'   => new sfWidgetFormInputText(),
      'parent_submission'     => new sfWidgetFormInputText(),
      'date_of_submission'    => new sfWidgetFormInputText(),
      'date_of_response'      => new sfWidgetFormInputText(),
      'date_of_issue'         => new sfWidgetFormInputText(),
      'observation'           => new sfWidgetFormTextarea(),
      'assessment_inprogress' => new sfWidgetFormInputText(),
      'pdf_path'              => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'               => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ApForms'), 'column' => 'form_id', 'required' => false)),
      'entry_id'              => new sfValidatorInteger(array('required' => false)),
      'user_id'               => new sfValidatorInteger(array('required' => false)),
      'circulation_id'        => new sfValidatorInteger(array('required' => false)),
      'approved'              => new sfValidatorInteger(array('required' => false)),
      'application_id'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'declined'              => new sfValidatorInteger(array('required' => false)),
      'deleted_status'        => new sfValidatorInteger(array('required' => false)),
      'previous_submission'   => new sfValidatorInteger(),
      'parent_submission'     => new sfValidatorInteger(),
      'date_of_submission'    => new sfValidatorString(array('max_length' => 250)),
      'date_of_response'      => new sfValidatorString(array('max_length' => 250)),
      'date_of_issue'         => new sfValidatorString(array('max_length' => 250)),
      'observation'           => new sfValidatorString(),
      'assessment_inprogress' => new sfValidatorInteger(array('required' => false)),
      'pdf_path'              => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('form_entry[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormEntry';
  }

}
