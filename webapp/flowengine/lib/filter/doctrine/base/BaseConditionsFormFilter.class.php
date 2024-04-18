<?php

/**
 * Conditions filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseConditionsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'circulation_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'slot_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'form_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'field_id'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'condition_text' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'circulation_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'slot_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'field_id'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'condition_text' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('conditions_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Conditions';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'circulation_id' => 'Number',
      'slot_id'        => 'Number',
      'form_id'        => 'Number',
      'field_id'       => 'Number',
      'condition_text' => 'Text',
    );
  }
}
