<?php

/**
 * ApNumberGenerator filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApNumberGeneratorFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'application_number' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'application_number' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_number_generator_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApNumberGenerator';
  }

  public function getFields()
  {
    return array(
      'form_id'            => 'Number',
      'application_number' => 'Text',
    );
  }
}
