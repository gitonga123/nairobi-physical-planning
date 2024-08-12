<?php

/**
 * ApDropdownFilters filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApDropdownFiltersFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'element_id'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'link_id'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'option_id'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'lioption_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'form_id'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_id'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'link_id'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'option_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'lioption_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_dropdown_filters_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApDropdownFilters';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'form_id'     => 'Number',
      'element_id'  => 'Number',
      'link_id'     => 'Number',
      'option_id'   => 'Number',
      'lioption_id' => 'Number',
    );
  }
}
