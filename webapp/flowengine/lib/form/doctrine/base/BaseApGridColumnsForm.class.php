<?php

/**
 * ApGridColumns form base class.
 *
 * @method ApGridColumns getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApGridColumnsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'agc_id'       => new sfWidgetFormInputHidden(),
      'element_name' => new sfWidgetFormInputText(),
      'form_id'      => new sfWidgetFormInputText(),
      'chart_id'     => new sfWidgetFormInputText(),
      'position'     => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'agc_id'       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('agc_id')), 'empty_value' => $this->getObject()->get('agc_id'), 'required' => false)),
      'element_name' => new sfValidatorString(array('max_length' => 255)),
      'form_id'      => new sfValidatorString(array('max_length' => 255)),
      'chart_id'     => new sfValidatorString(array('max_length' => 255)),
      'position'     => new sfValidatorString(array('max_length' => 255)),
    ));

    $this->widgetSchema->setNameFormat('ap_grid_columns[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApGridColumns';
  }

}
