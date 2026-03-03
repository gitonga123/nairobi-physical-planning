<?php

/**
 * ApEntriesPreferences filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApEntriesPreferencesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_id'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'entries_sort_by'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'entries_enable_filter' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'entries_filter_type'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'form_id'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'user_id'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entries_sort_by'       => new sfValidatorPass(array('required' => false)),
      'entries_enable_filter' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entries_filter_type'   => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_entries_preferences_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApEntriesPreferences';
  }

  public function getFields()
  {
    return array(
      'id'                    => 'Number',
      'form_id'               => 'Number',
      'user_id'               => 'Number',
      'entries_sort_by'       => 'Text',
      'entries_enable_filter' => 'Number',
      'entries_filter_type'   => 'Text',
    );
  }
}
