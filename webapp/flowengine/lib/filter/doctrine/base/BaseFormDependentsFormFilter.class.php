<?php

/**
 * FormDependents filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormDependentsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'           => new sfWidgetFormFilterInput(),
      'element_id'        => new sfWidgetFormFilterInput(),
      'element_value'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'dependent_form_id' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'form_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_value'     => new sfValidatorPass(array('required' => false)),
      'dependent_form_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('form_dependents_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormDependents';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'form_id'           => 'Number',
      'element_id'        => 'Number',
      'element_value'     => 'Text',
      'dependent_form_id' => 'Number',
    );
  }
}
