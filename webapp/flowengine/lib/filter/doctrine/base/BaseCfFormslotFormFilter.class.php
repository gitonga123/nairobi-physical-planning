<?php

/**
 * CfFormslot filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BaseCfFormslotFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'strname'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strdescription' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'ntemplateid'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'nslotnumber'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'nsendtype'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'strname'        => new sfValidatorPass(array('required' => false)),
      'strdescription' => new sfValidatorPass(array('required' => false)),
      'ntemplateid'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'nslotnumber'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'nsendtype'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('cf_formslot_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CfFormslot';
  }

  public function getFields()
  {
    return array(
      'nid'            => 'Number',
      'strname'        => 'Text',
      'strdescription' => 'Text',
      'ntemplateid'    => 'Number',
      'nslotnumber'    => 'Number',
      'nsendtype'      => 'Number',
    );
  }
}
