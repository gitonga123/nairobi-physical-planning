<?php

/**
 * ApFieldRelations filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFieldRelationsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'field1' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'field2' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'formid' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'status' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'field1' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'field2' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'formid' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'status' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_field_relations_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFieldRelations';
  }

  public function getFields()
  {
    return array(
      'id'     => 'Number',
      'field1' => 'Number',
      'field2' => 'Number',
      'formid' => 'Number',
      'status' => 'Number',
    );
  }
}
