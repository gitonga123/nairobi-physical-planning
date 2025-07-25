<?php

/**
 * ApFormLocks form base class.
 *
 * @method ApFormLocks getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFormLocksForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'        => new sfWidgetFormInputHidden(),
      'form_id'   => new sfWidgetFormInputText(),
      'user_id'   => new sfWidgetFormInputText(),
      'lock_date' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'        => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'   => new sfValidatorInteger(),
      'user_id'   => new sfValidatorInteger(),
      'lock_date' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('ap_form_locks[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFormLocks';
  }

}
