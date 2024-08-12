<?php

/**
 * ApTableRelation form base class.
 *
 * @method ApTableRelation getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApTableRelationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'form_id'          => new sfWidgetFormInputText(),
      'element_id'       => new sfWidgetFormInputText(),
      'tbl_name'         => new sfWidgetFormInputText(),
      'tbl_value_fld'    => new sfWidgetFormInputText(),
      'table_status_fld' => new sfWidgetFormInputText(),
      'exclude_status'   => new sfWidgetFormInputText(),
      'as_numeric'       => new sfWidgetFormInputText(),
      'as_include'       => new sfWidgetFormInputText(),
      'as_unique'        => new sfWidgetFormInputText(),
      'max_value'        => new sfWidgetFormInputText(),
      'min_value'        => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'          => new sfValidatorInteger(array('required' => false)),
      'element_id'       => new sfValidatorInteger(array('required' => false)),
      'tbl_name'         => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'tbl_value_fld'    => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'table_status_fld' => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'exclude_status'   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'as_numeric'       => new sfValidatorInteger(array('required' => false)),
      'as_include'       => new sfValidatorInteger(array('required' => false)),
      'as_unique'        => new sfValidatorInteger(array('required' => false)),
      'max_value'        => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'min_value'        => new sfValidatorString(array('max_length' => 250, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_table_relation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApTableRelation';
  }

}
