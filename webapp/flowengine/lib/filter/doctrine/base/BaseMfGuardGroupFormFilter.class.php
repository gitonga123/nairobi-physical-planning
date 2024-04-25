<?php

/**
 * MfGuardGroup filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseMfGuardGroupFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'        => new sfWidgetFormFilterInput(),
      'description' => new sfWidgetFormFilterInput(),
      'users_list'  => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'CfUser')),
      'group_list'  => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'CfUser')),
    ));

    $this->setValidators(array(
      'name'        => new sfValidatorPass(array('required' => false)),
      'description' => new sfValidatorPass(array('required' => false)),
      'users_list'  => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'CfUser', 'required' => false)),
      'group_list'  => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'CfUser', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('mf_guard_group_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addUsersListColumnQuery(Doctrine_Query $query, $field, $values)
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

  public function addGroupListColumnQuery(Doctrine_Query $query, $field, $values)
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

  public function getModelName()
  {
    return 'MfGuardGroup';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'name'        => 'Text',
      'description' => 'Text',
      'users_list'  => 'ManyKey',
      'group_list'  => 'ManyKey',
    );
  }
}
