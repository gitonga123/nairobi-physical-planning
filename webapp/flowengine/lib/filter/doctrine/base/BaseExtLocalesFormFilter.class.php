<?php

/**
 * ExtLocales filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseExtLocalesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'locale_identifier'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'local_title'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'locale_description' => new sfWidgetFormFilterInput(),
      'is_default'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'text_align'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'locale_identifier'  => new sfValidatorPass(array('required' => false)),
      'local_title'        => new sfValidatorPass(array('required' => false)),
      'locale_description' => new sfValidatorPass(array('required' => false)),
      'is_default'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'text_align'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ext_locales_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExtLocales';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'locale_identifier'  => 'Text',
      'local_title'        => 'Text',
      'locale_description' => 'Text',
      'is_default'         => 'Number',
      'text_align'         => 'Number',
    );
  }
}
