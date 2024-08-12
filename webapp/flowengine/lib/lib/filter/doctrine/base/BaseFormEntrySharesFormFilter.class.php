<?php

/**
 * FormEntryShares filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormEntrySharesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'senderid'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'receiverid'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'formentryid' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'senderid'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'receiverid'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'formentryid' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('form_entry_shares_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormEntryShares';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'senderid'    => 'Number',
      'receiverid'  => 'Number',
      'formentryid' => 'Number',
    );
  }
}
