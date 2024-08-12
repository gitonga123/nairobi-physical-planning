<?php

/**
 * AppChange filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseAppChangeFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'stage_id'         => new sfWidgetFormFilterInput(),
      'form_id'          => new sfWidgetFormFilterInput(),
      'identifier_type'  => new sfWidgetFormFilterInput(),
      'app_identifier'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'identifier_start' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'stage_id'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_id'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'identifier_type'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'app_identifier'   => new sfValidatorPass(array('required' => false)),
      'identifier_start' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('app_change_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'AppChange';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'stage_id'         => 'Number',
      'form_id'          => 'Number',
      'identifier_type'  => 'Number',
      'app_identifier'   => 'Text',
      'identifier_start' => 'Text',
    );
  }
}
