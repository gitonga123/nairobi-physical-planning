<?php

/**
 * ApFonts filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFontsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'font_origin'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'font_family'           => new sfWidgetFormFilterInput(),
      'font_variants'         => new sfWidgetFormFilterInput(),
      'font_variants_numeric' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'font_origin'           => new sfValidatorPass(array('required' => false)),
      'font_family'           => new sfValidatorPass(array('required' => false)),
      'font_variants'         => new sfValidatorPass(array('required' => false)),
      'font_variants_numeric' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_fonts_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFonts';
  }

  public function getFields()
  {
    return array(
      'font_id'               => 'Number',
      'font_origin'           => 'Text',
      'font_family'           => 'Text',
      'font_variants'         => 'Text',
      'font_variants_numeric' => 'Text',
    );
  }
}
