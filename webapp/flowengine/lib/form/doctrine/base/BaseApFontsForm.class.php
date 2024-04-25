<?php

/**
 * ApFonts form base class.
 *
 * @method ApFonts getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFontsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'font_id'               => new sfWidgetFormInputHidden(),
      'font_origin'           => new sfWidgetFormInputText(),
      'font_family'           => new sfWidgetFormInputText(),
      'font_variants'         => new sfWidgetFormTextarea(),
      'font_variants_numeric' => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'font_id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('font_id')), 'empty_value' => $this->getObject()->get('font_id'), 'required' => false)),
      'font_origin'           => new sfValidatorString(array('max_length' => 11, 'required' => false)),
      'font_family'           => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'font_variants'         => new sfValidatorString(array('required' => false)),
      'font_variants_numeric' => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_fonts[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFonts';
  }

}
