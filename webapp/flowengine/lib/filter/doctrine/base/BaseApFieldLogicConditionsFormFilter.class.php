<?php

/**
 * ApFieldLogicConditions filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFieldLogicConditionsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'target_element_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'element_name'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'rule_condition'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'rule_keyword'      => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'form_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'target_element_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_name'      => new sfValidatorPass(array('required' => false)),
      'rule_condition'    => new sfValidatorPass(array('required' => false)),
      'rule_keyword'      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_field_logic_conditions_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFieldLogicConditions';
  }

  public function getFields()
  {
    return array(
      'alc_id'            => 'Number',
      'form_id'           => 'Number',
      'target_element_id' => 'Number',
      'element_name'      => 'Text',
      'rule_condition'    => 'Text',
      'rule_keyword'      => 'Text',
    );
  }
}
