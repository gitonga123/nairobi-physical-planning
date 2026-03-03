<?php

/**
 * ApplicationNumbers filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApplicationNumbersFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'stage_id'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'application_number' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'form_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'stage_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'application_number' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('application_numbers_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApplicationNumbers';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'form_id'            => 'Number',
      'stage_id'           => 'Number',
      'application_number' => 'Text',
    );
  }
}
