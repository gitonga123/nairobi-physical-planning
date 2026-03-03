<?php

/**
 * FormEntryLinks form base class.
 *
 * @method FormEntryLinks getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormEntryLinksForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'formentryid'        => new sfWidgetFormInputText(),
      'form_id'            => new sfWidgetFormInputText(),
      'entry_id'           => new sfWidgetFormInputText(),
      'user_id'            => new sfWidgetFormInputText(),
      'date_of_submission' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'formentryid'        => new sfValidatorInteger(),
      'form_id'            => new sfValidatorInteger(),
      'entry_id'           => new sfValidatorInteger(array('required' => false)),
      'user_id'            => new sfValidatorInteger(array('required' => false)),
      'date_of_submission' => new sfValidatorString(array('max_length' => 200)),
    ));

    $this->widgetSchema->setNameFormat('form_entry_links[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormEntryLinks';
  }

}
