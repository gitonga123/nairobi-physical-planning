<?php

/**
 * CfUser form base class.
 *
 * @method CfUser getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCfUserForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'nid'                         => new sfWidgetFormInputHidden(),
      'strlastname'                 => new sfWidgetFormInputText(),
      'strfirstname'                => new sfWidgetFormInputText(),
      'stremail'                    => new sfWidgetFormInputText(),
      'naccesslevel'                => new sfWidgetFormInputText(),
      'struserid'                   => new sfWidgetFormInputText(),
      'strpassword'                 => new sfWidgetFormTextarea(),
      'stremail_format'             => new sfWidgetFormInputText(),
      'stremail_values'             => new sfWidgetFormInputText(),
      'nsubstitudeid'               => new sfWidgetFormInputText(),
      'tslastaction'                => new sfWidgetFormInputText(),
      'bdeleted'                    => new sfWidgetFormInputText(),
      'strstreet'                   => new sfWidgetFormTextarea(),
      'strcountry'                  => new sfWidgetFormInputText(),
      'strzipcode'                  => new sfWidgetFormTextarea(),
      'strcity'                     => new sfWidgetFormInputText(),
      'strphone_main1'              => new sfWidgetFormTextarea(),
      'strphone_main2'              => new sfWidgetFormTextarea(),
      'strphone_mobile'             => new sfWidgetFormInputText(),
      'strfax'                      => new sfWidgetFormTextarea(),
      'strorganisation'             => new sfWidgetFormTextarea(),
      'strdepartment'               => new sfWidgetFormDoctrineChoice(array('model' => 'Department')),
      'strcostcenter'               => new sfWidgetFormTextarea(),
      'userdefined1_value'          => new sfWidgetFormInputText(),
      'userdefined2_value'          => new sfWidgetFormInputText(),
      'nsubstitutetimevalue'        => new sfWidgetFormInputText(),
      'strsubstitutetimeunit'       => new sfWidgetFormTextarea(),
      'busegeneralsubstituteconfig' => new sfWidgetFormInputText(),
      'busegeneralemailconfig'      => new sfWidgetFormInputText(),
      'enable_email'                => new sfWidgetFormInputText(),
      'enable_chat'                 => new sfWidgetFormInputText(),
      'about_me'                    => new sfWidgetFormTextarea(),
      'profile_pic'                 => new sfWidgetFormInputText(),
      'address'                     => new sfWidgetFormTextarea(),
      'twitter'                     => new sfWidgetFormInputText(),
      'facebook'                    => new sfWidgetFormInputText(),
      'youtube'                     => new sfWidgetFormInputText(),
      'linkedin'                    => new sfWidgetFormInputText(),
      'pinterest'                   => new sfWidgetFormInputText(),
      'instagram'                   => new sfWidgetFormInputText(),
      'strtoken'                    => new sfWidgetFormInputText(),
      'strtemppassword'             => new sfWidgetFormInputText(),
      'groups_list'                 => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MfGuardGroup')),
      'user_list'                   => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MfGuardGroup')),
    ));

    $this->setValidators(array(
      'nid'                         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('nid')), 'empty_value' => $this->getObject()->get('nid'), 'required' => false)),
      'strlastname'                 => new sfValidatorString(),
      'strfirstname'                => new sfValidatorString(),
      'stremail'                    => new sfValidatorString(),
      'naccesslevel'                => new sfValidatorInteger(array('required' => false)),
      'struserid'                   => new sfValidatorString(),
      'strpassword'                 => new sfValidatorString(array('required' => false)),
      'stremail_format'             => new sfValidatorString(array('max_length' => 8, 'required' => false)),
      'stremail_values'             => new sfValidatorString(array('max_length' => 8, 'required' => false)),
      'nsubstitudeid'               => new sfValidatorInteger(array('required' => false)),
      'tslastaction'                => new sfValidatorInteger(array('required' => false)),
      'bdeleted'                    => new sfValidatorInteger(array('required' => false)),
      'strstreet'                   => new sfValidatorString(array('required' => false)),
      'strcountry'                  => new sfValidatorString(array('required' => false)),
      'strzipcode'                  => new sfValidatorString(array('required' => false)),
      'strcity'                     => new sfValidatorString(array('required' => false)),
      'strphone_main1'              => new sfValidatorString(array('required' => false)),
      'strphone_main2'              => new sfValidatorString(array('required' => false)),
      'strphone_mobile'             => new sfValidatorString(array('required' => true)),
      'strfax'                      => new sfValidatorString(array('required' => false)),
      'strorganisation'             => new sfValidatorString(array('required' => false)),
      'strdepartment'               => new sfValidatorString(array('required' => false)),
      'strcostcenter'               => new sfValidatorString(array('required' => false)),
      'userdefined1_value'          => new sfValidatorString(array('required' => false)),
      'userdefined2_value'          => new sfValidatorString(array('required' => false)),
      'nsubstitutetimevalue'        => new sfValidatorInteger(array('required' => false)),
      'strsubstitutetimeunit'       => new sfValidatorString(array('required' => false)),
      'busegeneralsubstituteconfig' => new sfValidatorInteger(array('required' => false)),
      'busegeneralemailconfig'      => new sfValidatorInteger(array('required' => false)),
      'enable_email'                => new sfValidatorInteger(array('required' => false)),
      'enable_chat'                 => new sfValidatorInteger(array('required' => false)),
      'about_me'                    => new sfValidatorString(array('required' => false)),
      'profile_pic'                 => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'address'                     => new sfValidatorString(array('required' => false)),
      'twitter'                     => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'facebook'                    => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'youtube'                     => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'linkedin'                    => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'pinterest'                   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'instagram'                   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'strtoken'                    => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'strtemppassword'             => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'groups_list'                 => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'MfGuardGroup', 'required' => false)),
      'user_list'                   => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'MfGuardGroup', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('cf_user[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CfUser';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['groups_list']))
    {
      $this->setDefault('groups_list', $this->object->Groups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['user_list']))
    {
      $this->setDefault('user_list', $this->object->User->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateGroupsList($values);
    $this->updateUserList($values);

    parent::doUpdateObject($values);
  }

  public function updateGroupsList($values)
  {
    if (!isset($this->widgetSchema['groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('groups_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->Groups->getPrimaryKeys();
    $values = $values['groups_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Groups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Groups', array_values($link));
    }
  }

  public function updateUserList($values)
  {
    if (!isset($this->widgetSchema['user_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('user_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->User->getPrimaryKeys();
    $values = $values['user_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('User', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('User', array_values($link));
    }
  }

}
