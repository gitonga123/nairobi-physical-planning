<?php

/**
 * ApplicationReference filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApplicationReferenceFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'stage_id'       => new sfWidgetFormFilterInput(),
      'application_id' => new sfWidgetFormFilterInput(),
      'approved_by'    => new sfWidgetFormFilterInput(),
      'start_date'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'end_date'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'stage_id'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'application_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'approved_by'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'start_date'     => new sfValidatorPass(array('required' => false)),
      'end_date'       => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('application_reference_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApplicationReference';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'stage_id'       => 'Number',
      'application_id' => 'Number',
      'approved_by'    => 'Number',
      'start_date'     => 'Text',
      'end_date'       => 'Text',
    );
  }
}
