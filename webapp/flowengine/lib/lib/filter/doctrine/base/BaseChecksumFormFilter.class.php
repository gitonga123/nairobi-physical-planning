<?php

/**
 * Checksum filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseChecksumFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'entry_id' => new sfWidgetFormFilterInput(),
      'checksum' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'entry_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'checksum' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('checksum_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Checksum';
  }

  public function getFields()
  {
    return array(
      'id'       => 'Number',
      'entry_id' => 'Number',
      'checksum' => 'Text',
    );
  }
}
