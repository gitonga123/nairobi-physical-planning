<?php

/**
 * SavedPermit filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSavedPermitFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'type_id'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'application_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'date_of_issue'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_of_expiry'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_by'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'last_updated'       => new sfWidgetFormFilterInput(),
      'permit_id'          => new sfWidgetFormFilterInput(),
      'document_key'       => new sfWidgetFormFilterInput(),
      'remote_result'      => new sfWidgetFormFilterInput(),
      'permit_status'      => new sfWidgetFormFilterInput(),
      'remote_update_uuid' => new sfWidgetFormFilterInput(),
      'pdf_path'           => new sfWidgetFormFilterInput(),
      'sent'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'expiry_trigger'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'type_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'application_id'     => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id')),
      'date_of_issue'      => new sfValidatorPass(array('required' => false)),
      'date_of_expiry'     => new sfValidatorPass(array('required' => false)),
      'created_by'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'last_updated'       => new sfValidatorPass(array('required' => false)),
      'permit_id'          => new sfValidatorPass(array('required' => false)),
      'document_key'       => new sfValidatorPass(array('required' => false)),
      'remote_result'      => new sfValidatorPass(array('required' => false)),
      'permit_status'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'remote_update_uuid' => new sfValidatorPass(array('required' => false)),
      'pdf_path'           => new sfValidatorPass(array('required' => false)),
      'sent'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'expiry_trigger'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('saved_permit_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SavedPermit';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'type_id'            => 'Number',
      'application_id'     => 'ForeignKey',
      'date_of_issue'      => 'Text',
      'date_of_expiry'     => 'Text',
      'created_by'         => 'Number',
      'last_updated'       => 'Text',
      'permit_id'          => 'Text',
      'document_key'       => 'Text',
      'remote_result'      => 'Text',
      'permit_status'      => 'Number',
      'remote_update_uuid' => 'Text',
      'pdf_path'           => 'Text',
      'sent'               => 'Number',
      'expiry_trigger'     => 'Number',
    );
  }
}
