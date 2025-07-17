<?php

/**
 * AttachedPermit filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseAttachedPermitFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'application_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'form_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'entry_id'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'attachedby_id'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'application_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entry_id'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'attachedby_id'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('attached_permit_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AttachedPermit';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'application_id' => 'Number',
      'form_id'        => 'Number',
      'entry_id'       => 'Number',
      'attachedby_id'  => 'Number',
    );
  }
}
