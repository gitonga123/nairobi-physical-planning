<?php

/**
 * Department filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseDepartmentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'department_name' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'department_head' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'department_name' => new sfValidatorPass(array('required' => false)),
      'department_head' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('department_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Department';
  }

  public function getFields()
  {
    return array(
      'id'              => 'Number',
      'department_name' => 'Text',
      'department_head' => 'Number',
    );
  }
}
