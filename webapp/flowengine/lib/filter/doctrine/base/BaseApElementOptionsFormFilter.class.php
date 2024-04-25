<?php

/**
 * ApElementOptions filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApElementOptionsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'element_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'option_id'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'position'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'option_text'       => new sfWidgetFormFilterInput(),
      'option_is_default' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'live'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'form_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'element_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'option_id'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'position'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'option_text'       => new sfValidatorPass(array('required' => false)),
      'option_is_default' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'live'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_element_options_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApElementOptions';
  }

  public function getFields()
  {
    return array(
      'aeo_id'            => 'Number',
      'form_id'           => 'Number',
      'element_id'        => 'Number',
      'option_id'         => 'Number',
      'position'          => 'Number',
      'option_text'       => 'Text',
      'option_is_default' => 'Number',
      'live'              => 'Number',
    );
  }
}
