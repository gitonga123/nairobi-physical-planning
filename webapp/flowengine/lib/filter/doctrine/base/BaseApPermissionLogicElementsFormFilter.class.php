<?php

/**
 * ApPermissionLogicElements filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApPermissionLogicElementsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'rule_show_hide' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'rule_all_any'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'rule_show_hide' => new sfValidatorPass(array('required' => false)),
      'rule_all_any'   => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_permission_logic_elements_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApPermissionLogicElements';
  }

  public function getFields()
  {
    return array(
      'form_id'        => 'Number',
      'element_id'     => 'Number',
      'rule_show_hide' => 'Text',
      'rule_all_any'   => 'Text',
    );
  }
}
