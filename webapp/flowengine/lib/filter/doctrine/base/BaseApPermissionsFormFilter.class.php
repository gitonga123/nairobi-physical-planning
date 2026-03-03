<?php

/**
 * ApPermissions filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApPermissionsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'edit_form'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'edit_report'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'edit_entries' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'view_entries' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'edit_form'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'edit_report'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'edit_entries' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'view_entries' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_permissions_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApPermissions';
  }

  public function getFields()
  {
    return array(
      'form_id'      => 'Number',
      'user_id'      => 'Number',
      'edit_form'    => 'Number',
      'edit_report'  => 'Number',
      'edit_entries' => 'Number',
      'view_entries' => 'Number',
    );
  }
}
