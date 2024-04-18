<?php

/**
 * ApGridColumns filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApGridColumnsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'element_name' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'form_id'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_id'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'position'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'element_name' => new sfValidatorPass(array('required' => false)),
      'form_id'      => new sfValidatorPass(array('required' => false)),
      'chart_id'     => new sfValidatorPass(array('required' => false)),
      'position'     => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_grid_columns_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApGridColumns';
  }

  public function getFields()
  {
    return array(
      'agc_id'       => 'Number',
      'element_name' => 'Text',
      'form_id'      => 'Text',
      'chart_id'     => 'Text',
      'position'     => 'Text',
    );
  }
}
