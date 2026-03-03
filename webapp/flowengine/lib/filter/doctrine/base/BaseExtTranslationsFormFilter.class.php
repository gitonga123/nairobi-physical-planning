<?php

/**
 * ExtTranslations filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseExtTranslationsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'locale'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'table_class' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'field_name'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'field_id'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'trl_content' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'option_id'   => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'locale'      => new sfValidatorPass(array('required' => false)),
      'table_class' => new sfValidatorPass(array('required' => false)),
      'field_name'  => new sfValidatorPass(array('required' => false)),
      'field_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'trl_content' => new sfValidatorPass(array('required' => false)),
      'option_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ext_translations_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExtTranslations';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'locale'      => 'Text',
      'table_class' => 'Text',
      'field_name'  => 'Text',
      'field_id'    => 'Number',
      'trl_content' => 'Text',
      'option_id'   => 'Number',
    );
  }
}
