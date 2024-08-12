<?php

/**
 * ApSuccessLogicConditions filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApSuccessLogicConditionsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'element_name'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'rule_condition' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'rule_keyword'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'element_name'   => new sfValidatorPass(array('required' => false)),
      'rule_condition' => new sfValidatorPass(array('required' => false)),
      'rule_keyword'   => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_success_logic_conditions_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApSuccessLogicConditions';
  }

  public function getFields()
  {
    return array(
      'slc_id'         => 'Number',
      'form_id'        => 'Number',
      'target_rule_id' => 'Number',
      'element_name'   => 'Text',
      'rule_condition' => 'Text',
      'rule_keyword'   => 'Text',
    );
  }
}
