<?php

/**
 * Faq form base class.
 *
 * @method Faq getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFaqForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'question'   => new sfWidgetFormInputText(),
      'answer'     => new sfWidgetFormTextarea(),
      'published'  => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    0 => "Not Published",
                    1 => "Published"
                )
            )
        ),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'question'   => new sfValidatorString(array('max_length' => 250)),
      'answer'     => new sfValidatorString(array('required' => false)),
      'published'  => new sfValidatorInteger(array('required' => false))
    ));

    $this->widgetSchema->setNameFormat('faq[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Faq';
  }

}
