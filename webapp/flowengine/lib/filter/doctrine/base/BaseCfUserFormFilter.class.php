<?php

/**
 * CfUser filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCfUserFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'strlastname'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strfirstname'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'stremail'                    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'naccesslevel'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'struserid'                   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strpassword'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'stremail_format'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'stremail_values'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'nsubstitudeid'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'tslastaction'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'bdeleted'                    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strstreet'                   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strcountry'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strzipcode'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strcity'                     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strphone_main1'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strphone_main2'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strphone_mobile'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strfax'                      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strorganisation'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strdepartment'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strcostcenter'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'userdefined1_value'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'userdefined2_value'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'nsubstitutetimevalue'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsubstitutetimeunit'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'busegeneralsubstituteconfig' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'busegeneralemailconfig'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'enable_email'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'enable_chat'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'about_me'                    => new sfWidgetFormFilterInput(),
      'profile_pic'                 => new sfWidgetFormFilterInput(),
      'address'                     => new sfWidgetFormFilterInput(),
      'twitter'                     => new sfWidgetFormFilterInput(),
      'facebook'                    => new sfWidgetFormFilterInput(),
      'youtube'                     => new sfWidgetFormFilterInput(),
      'linkedin'                    => new sfWidgetFormFilterInput(),
      'pinterest'                   => new sfWidgetFormFilterInput(),
      'instagram'                   => new sfWidgetFormFilterInput(),
      'strtoken'                    => new sfWidgetFormFilterInput(),
      'strtemppassword'             => new sfWidgetFormFilterInput(),
      'groups_list'                 => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MfGuardGroup')),
      'user_list'                   => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'MfGuardGroup')),
    ));

    $this->setValidators(array(
      'strlastname'                 => new sfValidatorPass(array('required' => false)),
      'strfirstname'                => new sfValidatorPass(array('required' => false)),
      'stremail'                    => new sfValidatorPass(array('required' => false)),
      'naccesslevel'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'struserid'                   => new sfValidatorPass(array('required' => false)),
      'strpassword'                 => new sfValidatorPass(array('required' => false)),
      'stremail_format'             => new sfValidatorPass(array('required' => false)),
      'stremail_values'             => new sfValidatorPass(array('required' => false)),
      'nsubstitudeid'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'tslastaction'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'bdeleted'                    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'strstreet'                   => new sfValidatorPass(array('required' => false)),
      'strcountry'                  => new sfValidatorPass(array('required' => false)),
      'strzipcode'                  => new sfValidatorPass(array('required' => false)),
      'strcity'                     => new sfValidatorPass(array('required' => false)),
      'strphone_main1'              => new sfValidatorPass(array('required' => false)),
      'strphone_main2'              => new sfValidatorPass(array('required' => false)),
      'strphone_mobile'             => new sfValidatorPass(array('required' => false)),
      'strfax'                      => new sfValidatorPass(array('required' => false)),
      'strorganisation'             => new sfValidatorPass(array('required' => false)),
      'strdepartment'               => new sfValidatorPass(array('required' => false)),
      'strcostcenter'               => new sfValidatorPass(array('required' => false)),
      'userdefined1_value'          => new sfValidatorPass(array('required' => false)),
      'userdefined2_value'          => new sfValidatorPass(array('required' => false)),
      'nsubstitutetimevalue'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'strsubstitutetimeunit'       => new sfValidatorPass(array('required' => false)),
      'busegeneralsubstituteconfig' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'busegeneralemailconfig'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'enable_email'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'enable_chat'                 => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'about_me'                    => new sfValidatorPass(array('required' => false)),
      'profile_pic'                 => new sfValidatorPass(array('required' => false)),
      'address'                     => new sfValidatorPass(array('required' => false)),
      'twitter'                     => new sfValidatorPass(array('required' => false)),
      'facebook'                    => new sfValidatorPass(array('required' => false)),
      'youtube'                     => new sfValidatorPass(array('required' => false)),
      'linkedin'                    => new sfValidatorPass(array('required' => false)),
      'pinterest'                   => new sfValidatorPass(array('required' => false)),
      'instagram'                   => new sfValidatorPass(array('required' => false)),
      'strtoken'                    => new sfValidatorPass(array('required' => false)),
      'strtemppassword'             => new sfValidatorPass(array('required' => false)),
      'groups_list'                 => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'MfGuardGroup', 'required' => false)),
      'user_list'                   => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'MfGuardGroup', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('cf_user_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addGroupsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.mfGuardUserGroup mfGuardUserGroup')
      ->andWhereIn('mfGuardUserGroup.user_id', $values)
    ;
  }

  public function addUserListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.mfGuardUserGroup mfGuardUserGroup')
      ->andWhereIn('mfGuardUserGroup.group_id', $values)
    ;
  }

  public function getModelName()
  {
    return 'CfUser';
  }

  public function getFields()
  {
    return array(
      'nid'                         => 'Number',
      'strlastname'                 => 'Text',
      'strfirstname'                => 'Text',
      'stremail'                    => 'Text',
      'naccesslevel'                => 'Number',
      'struserid'                   => 'Text',
      'strpassword'                 => 'Text',
      'stremail_format'             => 'Text',
      'stremail_values'             => 'Text',
      'nsubstitudeid'               => 'Number',
      'tslastaction'                => 'Number',
      'bdeleted'                    => 'Number',
      'strstreet'                   => 'Text',
      'strcountry'                  => 'Text',
      'strzipcode'                  => 'Text',
      'strcity'                     => 'Text',
      'strphone_main1'              => 'Text',
      'strphone_main2'              => 'Text',
      'strphone_mobile'             => 'Text',
      'strfax'                      => 'Text',
      'strorganisation'             => 'Text',
      'strdepartment'               => 'Text',
      'strcostcenter'               => 'Text',
      'userdefined1_value'          => 'Text',
      'userdefined2_value'          => 'Text',
      'nsubstitutetimevalue'        => 'Number',
      'strsubstitutetimeunit'       => 'Text',
      'busegeneralsubstituteconfig' => 'Number',
      'busegeneralemailconfig'      => 'Number',
      'enable_email'                => 'Number',
      'enable_chat'                 => 'Number',
      'about_me'                    => 'Text',
      'profile_pic'                 => 'Text',
      'address'                     => 'Text',
      'twitter'                     => 'Text',
      'facebook'                    => 'Text',
      'youtube'                     => 'Text',
      'linkedin'                    => 'Text',
      'pinterest'                   => 'Text',
      'instagram'                   => 'Text',
      'strtoken'                    => 'Text',
      'strtemppassword'             => 'Text',
      'groups_list'                 => 'ManyKey',
      'user_list'                   => 'ManyKey',
    );
  }
}
