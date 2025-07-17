<?php

/**
 * ApPageLogic filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApPageLogicFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'rule_all_any' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'rule_all_any' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_page_logic_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApPageLogic';
  }

  public function getFields()
  {
    return array(
      'form_id'      => 'Number',
      'page_id'      => 'Text',
      'rule_all_any' => 'Text',
    );
  }
}
