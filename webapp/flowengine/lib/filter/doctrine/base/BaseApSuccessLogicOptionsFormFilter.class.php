<?php

/**
 * ApSuccessLogicOptions filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApSuccessLogicOptionsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'rule_all_any'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'success_type'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'success_message' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'redirect_url'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'rule_all_any'    => new sfValidatorPass(array('required' => false)),
      'success_type'    => new sfValidatorPass(array('required' => false)),
      'success_message' => new sfValidatorPass(array('required' => false)),
      'redirect_url'    => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_success_logic_options_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApSuccessLogicOptions';
  }

  public function getFields()
  {
    return array(
      'slo_id'          => 'Number',
      'form_id'         => 'Number',
      'rule_id'         => 'Number',
      'rule_all_any'    => 'Text',
      'success_type'    => 'Text',
      'success_message' => 'Text',
      'redirect_url'    => 'Text',
    );
  }
}
