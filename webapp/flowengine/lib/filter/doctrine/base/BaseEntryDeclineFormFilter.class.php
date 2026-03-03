<?php

/**
 * EntryDecline filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseEntryDeclineFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'entry_id'    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormEntry'), 'add_empty' => true)),
      'description' => new sfWidgetFormFilterInput(),
      'created_at'  => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'  => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'resolved'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'edit_fields' => new sfWidgetFormFilterInput(),
      'declined_by' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'entry_id'    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('FormEntry'), 'column' => 'id')),
      'description' => new sfValidatorPass(array('required' => false)),
      'created_at'  => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'  => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'resolved'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'edit_fields' => new sfValidatorPass(array('required' => false)),
      'declined_by' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('entry_decline_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntryDecline';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'entry_id'    => 'ForeignKey',
      'description' => 'Text',
      'created_at'  => 'Date',
      'updated_at'  => 'Date',
      'resolved'    => 'Number',
      'edit_fields' => 'Text',
      'declined_by' => 'Number',
    );
  }
}
