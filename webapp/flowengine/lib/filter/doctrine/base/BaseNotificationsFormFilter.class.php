<?php

/**
 * Notifications filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseNotificationsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'submenu_id' => new sfWidgetFormFilterInput(),
      'title'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'content'    => new sfWidgetFormFilterInput(),
      'sms'        => new sfWidgetFormFilterInput(),
      'form_id'    => new sfWidgetFormFilterInput(),
      'autosend'   => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'submenu_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'title'      => new sfValidatorPass(array('required' => false)),
      'content'    => new sfValidatorPass(array('required' => false)),
      'sms'        => new sfValidatorPass(array('required' => false)),
      'form_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'autosend'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('notifications_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Notifications';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'submenu_id' => 'Number',
      'title'      => 'Text',
      'content'    => 'Text',
      'sms'        => 'Text',
      'form_id'    => 'Number',
      'autosend'   => 'Number',
    );
  }
}
