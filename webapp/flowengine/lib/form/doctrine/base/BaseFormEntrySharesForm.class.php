<?php

/**
 * FormEntryShares form base class.
 *
 * @method FormEntryShares getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormEntrySharesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'senderid'    => new sfWidgetFormInputText(),
      'receiverid'  => new sfWidgetFormInputText(),
      'formentryid' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'senderid'    => new sfValidatorInteger(),
      'receiverid'  => new sfValidatorInteger(),
      'formentryid' => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('form_entry_shares[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormEntryShares';
  }

}
