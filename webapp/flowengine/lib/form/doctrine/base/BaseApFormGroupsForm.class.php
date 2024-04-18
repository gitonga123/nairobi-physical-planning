<?php

/**
 * ApFormGroups form base class.
 *
 * @method ApFormGroups getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFormGroupsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'       => new sfWidgetFormInputHidden(),
      'form_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ApForms'), 'add_empty' => true)),
      'group_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('FormGroups'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'form_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ApForms'), 'column' => 'form_id', 'required' => false)),
      'group_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('FormGroups'), 'column' => 'group_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_form_groups[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFormGroups';
  }

}
