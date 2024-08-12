<?php

/**
 * Notifications form base class.
 *
 * @method Notifications getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseNotificationsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'submenu_id' => new sfWidgetFormInputText(),
      'title'      => new sfWidgetFormInputText(),
      'content'    => new sfWidgetFormTextarea(),
      'sms'        => new sfWidgetFormInputText(),
      'form_id'    => new sfWidgetFormInputText(),
      'autosend'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'submenu_id' => new sfValidatorInteger(array('required' => false)),
      'title'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'content'    => new sfValidatorString(array('required' => false)),
      'sms'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'form_id'    => new sfValidatorInteger(array('required' => false)),
      'autosend'   => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('notifications[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Notifications';
  }

}
