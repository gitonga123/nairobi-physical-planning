<?php

/**
 * Wards filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BaseWardsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'code'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'ward_name'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'subcounty_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'code'         => new sfValidatorPass(array('required' => false)),
      'ward_name'    => new sfValidatorPass(array('required' => false)),
      'subcounty_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('wards_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Wards';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'code'         => 'Text',
      'ward_name'    => 'Text',
      'subcounty_id' => 'Number',
    );
  }
}
