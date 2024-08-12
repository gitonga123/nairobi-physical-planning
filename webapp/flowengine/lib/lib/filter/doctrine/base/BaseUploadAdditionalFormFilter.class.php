<?php

/**
 * UploadAdditional filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseUploadAdditionalFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'application_id' => new sfWidgetFormFilterInput(),
      'form_id'        => new sfWidgetFormFilterInput(),
      'entry_id'       => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'application_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entry_id'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('upload_additional_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'UploadAdditional';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'application_id' => 'Number',
      'form_id'        => 'Number',
      'entry_id'       => 'Number',
    );
  }
}
