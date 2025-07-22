<?php

/**
 * SfGuardUserAssociates form base class.
 *
 * @method SfGuardUserAssociates getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSfGuardUserAssociatesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'senderid'      => new sfWidgetFormInputText(),
      'receiverid'    => new sfWidgetFormInputText(),
      'accepted'      => new sfWidgetFormInputText(),
      'seemyplans'    => new sfWidgetFormInputText(),
      'modifymyplans' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'senderid'      => new sfValidatorInteger(),
      'receiverid'    => new sfValidatorInteger(),
      'accepted'      => new sfValidatorInteger(array('required' => false)),
      'seemyplans'    => new sfValidatorInteger(array('required' => false)),
      'modifymyplans' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_associates[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfGuardUserAssociates';
  }

}
