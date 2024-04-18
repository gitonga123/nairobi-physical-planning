<?php

/**
 * FormGroups filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormGroupsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'group_parent'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'group_name'        => new sfWidgetFormFilterInput(),
      'group_description' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'group_parent'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'group_name'        => new sfValidatorPass(array('required' => false)),
      'group_description' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('form_groups_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormGroups';
  }

  public function getFields()
  {
    return array(
      'group_id'          => 'Number',
      'group_parent'      => 'Number',
      'group_name'        => 'Text',
      'group_description' => 'Text',
    );
  }
}
