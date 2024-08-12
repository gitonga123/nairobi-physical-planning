<?php

/**
 * FormEntryArchive filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormEntryArchiveFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'             => new sfWidgetFormFilterInput(),
      'entry_id'            => new sfWidgetFormFilterInput(),
      'user_id'             => new sfWidgetFormFilterInput(),
      'circulation_id'      => new sfWidgetFormFilterInput(),
      'approved'            => new sfWidgetFormFilterInput(),
      'application_id'      => new sfWidgetFormFilterInput(),
      'declined'            => new sfWidgetFormFilterInput(),
      'deleted_status'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'saved_permit'        => new sfWidgetFormFilterInput(),
      'previous_submission' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'parent_submission'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_of_submission'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_of_response'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_of_issue'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'observation'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'pdf_path'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'form_id'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entry_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'user_id'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'circulation_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'approved'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'application_id'      => new sfValidatorPass(array('required' => false)),
      'declined'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'deleted_status'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'saved_permit'        => new sfValidatorPass(array('required' => false)),
      'previous_submission' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'parent_submission'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'date_of_submission'  => new sfValidatorPass(array('required' => false)),
      'date_of_response'    => new sfValidatorPass(array('required' => false)),
      'date_of_issue'       => new sfValidatorPass(array('required' => false)),
      'observation'         => new sfValidatorPass(array('required' => false)),
      'pdf_path'            => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('form_entry_archive_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormEntryArchive';
  }

  public function getFields()
  {
    return array(
      'id'                  => 'Number',
      'form_id'             => 'Number',
      'entry_id'            => 'Number',
      'user_id'             => 'Number',
      'circulation_id'      => 'Number',
      'approved'            => 'Number',
      'application_id'      => 'Text',
      'declined'            => 'Number',
      'deleted_status'      => 'Number',
      'saved_permit'        => 'Text',
      'previous_submission' => 'Number',
      'parent_submission'   => 'Number',
      'date_of_submission'  => 'Text',
      'date_of_response'    => 'Text',
      'date_of_issue'       => 'Text',
      'observation'         => 'Text',
      'pdf_path'            => 'Text',
    );
  }
}
