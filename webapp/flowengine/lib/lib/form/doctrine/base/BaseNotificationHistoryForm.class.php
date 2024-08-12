<?php

/**
 * NotificationHistory form base class.
 *
 * @method NotificationHistory getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseNotificationHistoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'user_id'           => new sfWidgetFormInputText(),
      'notification'      => new sfWidgetFormTextarea(),
      'notification_type' => new sfWidgetFormInputText(),
      'sent_on'           => new sfWidgetFormInputText(),
      'confirmed_receipt' => new sfWidgetFormInputText(),
      'application_id'    => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'user_id'           => new sfValidatorInteger(array('required' => false)),
      'notification'      => new sfValidatorString(array('required' => false)),
      'notification_type' => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'sent_on'           => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'confirmed_receipt' => new sfValidatorInteger(array('required' => false)),
      'application_id'    => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('notification_history[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'NotificationHistory';
  }

}
