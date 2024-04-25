<?php

/**
 * ApUsers filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApUsersFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_email'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_password'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_fullname'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'priv_administer' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'priv_new_forms'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'priv_new_themes' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'last_login_date' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'last_ip_address' => new sfWidgetFormFilterInput(),
      'cookie_hash'     => new sfWidgetFormFilterInput(),
      'status'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'user_email'      => new sfValidatorPass(array('required' => false)),
      'user_password'   => new sfValidatorPass(array('required' => false)),
      'user_fullname'   => new sfValidatorPass(array('required' => false)),
      'priv_administer' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'priv_new_forms'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'priv_new_themes' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'last_login_date' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'last_ip_address' => new sfValidatorPass(array('required' => false)),
      'cookie_hash'     => new sfValidatorPass(array('required' => false)),
      'status'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_users_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApUsers';
  }

  public function getFields()
  {
    return array(
      'user_id'         => 'Number',
      'user_email'      => 'Text',
      'user_password'   => 'Text',
      'user_fullname'   => 'Text',
      'priv_administer' => 'Number',
      'priv_new_forms'  => 'Number',
      'priv_new_themes' => 'Number',
      'last_login_date' => 'Date',
      'last_ip_address' => 'Text',
      'cookie_hash'     => 'Text',
      'status'          => 'Number',
    );
  }
}
