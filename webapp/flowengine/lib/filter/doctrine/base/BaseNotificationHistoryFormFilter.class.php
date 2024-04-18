<?php

/**
 * NotificationHistory filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseNotificationHistoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'           => new sfWidgetFormFilterInput(),
      'notification'      => new sfWidgetFormFilterInput(),
      'notification_type' => new sfWidgetFormFilterInput(),
      'sent_on'           => new sfWidgetFormFilterInput(),
      'confirmed_receipt' => new sfWidgetFormFilterInput(),
      'application_id'    => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'user_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'notification'      => new sfValidatorPass(array('required' => false)),
      'notification_type' => new sfValidatorPass(array('required' => false)),
      'sent_on'           => new sfValidatorPass(array('required' => false)),
      'confirmed_receipt' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'application_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('notification_history_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'NotificationHistory';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'user_id'           => 'Number',
      'notification'      => 'Text',
      'notification_type' => 'Text',
      'sent_on'           => 'Text',
      'confirmed_receipt' => 'Number',
      'application_id'    => 'Number',
    );
  }
}
