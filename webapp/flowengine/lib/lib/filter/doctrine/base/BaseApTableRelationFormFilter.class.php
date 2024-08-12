<?php

/**
 * ApTableRelation filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApTableRelationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'          => new sfWidgetFormFilterInput(),
      'element_id'       => new sfWidgetFormFilterInput(),
      'tbl_name'         => new sfWidgetFormFilterInput(),
      'tbl_value_fld'    => new sfWidgetFormFilterInput(),
      'table_status_fld' => new sfWidgetFormFilterInput(),
      'exclude_status'   => new sfWidgetFormFilterInput(),
      'as_numeric'       => new sfWidgetFormFilterInput(),
      'as_include'       => new sfWidgetFormFilterInput(),
      'as_unique'        => new sfWidgetFormFilterInput(),
      'max_value'        => new sfWidgetFormFilterInput(),
      'min_value'        => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'form_id'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_id'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'tbl_name'         => new sfValidatorPass(array('required' => false)),
      'tbl_value_fld'    => new sfValidatorPass(array('required' => false)),
      'table_status_fld' => new sfValidatorPass(array('required' => false)),
      'exclude_status'   => new sfValidatorPass(array('required' => false)),
      'as_numeric'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'as_include'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'as_unique'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'max_value'        => new sfValidatorPass(array('required' => false)),
      'min_value'        => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_table_relation_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApTableRelation';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'form_id'          => 'Number',
      'element_id'       => 'Number',
      'tbl_name'         => 'Text',
      'tbl_value_fld'    => 'Text',
      'table_status_fld' => 'Text',
      'exclude_status'   => 'Text',
      'as_numeric'       => 'Number',
      'as_include'       => 'Number',
      'as_unique'        => 'Number',
      'max_value'        => 'Text',
      'min_value'        => 'Text',
    );
  }
}
