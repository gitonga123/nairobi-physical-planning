<?php

/**
 * ApFormGroups filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFormGroupsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ApForms'), 'add_empty' => true)),
      'group_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormGroups'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'form_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ApForms'), 'column' => 'form_id')),
      'group_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('FormGroups'), 'column' => 'group_id')),
    ));

    $this->widgetSchema->setNameFormat('ap_form_groups_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFormGroups';
  }

  public function getFields()
  {
    return array(
      'id'       => 'Number',
      'form_id'  => 'ForeignKey',
      'group_id' => 'ForeignKey',
    );
  }
}
