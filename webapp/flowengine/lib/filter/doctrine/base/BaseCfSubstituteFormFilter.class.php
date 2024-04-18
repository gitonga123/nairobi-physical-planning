<?php

/**
 * CfSubstitute filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BaseCfSubstituteFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'substitute_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'position'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'user_id'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'substitute_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'position'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('cf_substitute_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CfSubstitute';
  }

  public function getFields()
  {
    return array(
      'id'            => 'Number',
      'user_id'       => 'Number',
      'substitute_id' => 'Number',
      'position'      => 'Number',
    );
  }
}
