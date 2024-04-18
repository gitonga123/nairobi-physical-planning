<?php

/**
 * ApColumnPreferences filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApColumnPreferencesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'            => new sfWidgetFormFilterInput(),
      'element_name'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'position'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'incomplete_entries' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_id'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'form_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_name'       => new sfValidatorPass(array('required' => false)),
      'position'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'incomplete_entries' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'user_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_column_preferences_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApColumnPreferences';
  }

  public function getFields()
  {
    return array(
      'acp_id'             => 'Number',
      'form_id'            => 'Number',
      'element_name'       => 'Text',
      'position'           => 'Number',
      'incomplete_entries' => 'Number',
      'user_id'            => 'Number',
    );
  }
}
