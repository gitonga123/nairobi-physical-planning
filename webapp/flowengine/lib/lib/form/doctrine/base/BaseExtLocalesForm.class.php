<?php

/**
 * ExtLocales form base class.
 *
 * @method ExtLocales getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseExtLocalesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'locale_identifier'  => new sfWidgetFormInputText(),
      'local_title'        => new sfWidgetFormInputText(),
      'locale_description' => new sfWidgetFormTextarea(),
      'is_default'        => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    0 => "Not Default",
                    1 => "Default"
                )
            )
        ),
      'text_align'        => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    0 => "Left Aligned",
                    1 => "Right Aligned"
                )
            )
        ),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'locale_identifier'  => new sfValidatorString(array('max_length' => 250)),
      'local_title'        => new sfValidatorString(array('max_length' => 250)),
      'locale_description' => new sfValidatorString(array('required' => false)),
      'is_default'         => new sfValidatorInteger(array('required' => false)),
      'text_align'         => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ext_locales[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ExtLocales';
  }

}
