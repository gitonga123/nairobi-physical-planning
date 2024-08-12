<?php

/**
 * PermitCheckerConfig filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BasePermitCheckerConfigFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'permit_template_id' => new sfWidgetFormFilterInput(),
      'reference_object'   => new sfWidgetFormFilterInput(),
      'label_to_show'      => new sfWidgetFormFilterInput(),
      'value_to_show'      => new sfWidgetFormFilterInput(),
      'sequence_no'        => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'permit_template_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'reference_object'   => new sfValidatorPass(array('required' => false)),
      'label_to_show'      => new sfValidatorPass(array('required' => false)),
      'value_to_show'      => new sfValidatorPass(array('required' => false)),
      'sequence_no'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('permit_checker_config_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PermitCheckerConfig';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'permit_template_id' => 'Number',
      'reference_object'   => 'Text',
      'label_to_show'      => 'Text',
      'value_to_show'      => 'Text',
      'sequence_no'        => 'Number',
    );
  }
}
