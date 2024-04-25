<?php

/**
 * SavedPermitArchive form base class.
 *
 * @method SavedPermitArchive getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSavedPermitArchiveForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'type_id'            => new sfWidgetFormInputText(),
      'application_id'     => new sfWidgetFormInputText(),
      'permit'             => new sfWidgetFormTextarea(),
      'date_of_issue'      => new sfWidgetFormInputText(),
      'date_of_expiry'     => new sfWidgetFormInputText(),
      'created_by'         => new sfWidgetFormInputText(),
      'last_updated'       => new sfWidgetFormInputText(),
      'permit_id'          => new sfWidgetFormInputText(),
      'document_key'       => new sfWidgetFormInputText(),
      'remote_result'      => new sfWidgetFormTextarea(),
      'permit_status'      => new sfWidgetFormInputText(),
      'remote_update_uuid' => new sfWidgetFormTextarea(),
      'pdf_path'           => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'type_id'            => new sfValidatorInteger(),
      'application_id'     => new sfValidatorInteger(),
      'permit'             => new sfValidatorString(),
      'date_of_issue'      => new sfValidatorString(array('max_length' => 250)),
      'date_of_expiry'     => new sfValidatorString(array('max_length' => 250)),
      'created_by'         => new sfValidatorInteger(),
      'last_updated'       => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'permit_id'          => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'document_key'       => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'remote_result'      => new sfValidatorString(array('required' => false)),
      'permit_status'      => new sfValidatorInteger(array('required' => false)),
      'remote_update_uuid' => new sfValidatorString(array('required' => false)),
      'pdf_path'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('saved_permit_archive[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SavedPermitArchive';
  }

}
