<?php

/**
 * FormEntryLinks filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormEntryLinksFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'formentryid'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'form_id'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'entry_id'           => new sfWidgetFormFilterInput(),
      'user_id'            => new sfWidgetFormFilterInput(),
      'date_of_submission' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'formentryid'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entry_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'user_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'date_of_submission' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('form_entry_links_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormEntryLinks';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'formentryid'        => 'Number',
      'form_id'            => 'Number',
      'entry_id'           => 'Number',
      'user_id'            => 'Number',
      'date_of_submission' => 'Text',
    );
  }
}
