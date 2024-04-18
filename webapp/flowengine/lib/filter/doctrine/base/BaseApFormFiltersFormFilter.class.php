<?php

/**
 * ApFormFilters filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFormFiltersFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'element_name'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'filter_condition'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'filter_keyword'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_id'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'incomplete_entries' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'form_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_name'       => new sfValidatorPass(array('required' => false)),
      'filter_condition'   => new sfValidatorPass(array('required' => false)),
      'filter_keyword'     => new sfValidatorPass(array('required' => false)),
      'user_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'incomplete_entries' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_form_filters_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFormFilters';
  }

  public function getFields()
  {
    return array(
      'aff_id'             => 'Number',
      'form_id'            => 'Number',
      'element_name'       => 'Text',
      'filter_condition'   => 'Text',
      'filter_keyword'     => 'Text',
      'user_id'            => 'Number',
      'incomplete_entries' => 'Number',
    );
  }
}
