<?php

/**
 * FormGroups form base class.
 *
 * @method FormGroups getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFormGroupsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'group_id'          => new sfWidgetFormInputHidden(),
      'group_parent'      => new sfWidgetFormInputText(),
      'group_name'        => new sfWidgetFormInputText(),
      'group_description' => new sfWidgetFormInputText(),
      'group_forms' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ApForms')),
    ));

    $this->setValidators(array(
      'group_id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('group_id')), 'empty_value' => $this->getObject()->get('group_id'), 'required' => false)),
      'group_parent'      => new sfValidatorInteger(array('required' => false)),
      'group_name'        => new sfValidatorString(array('required' => false)),
      'group_description' => new sfValidatorString(array('required' => false)),
      'group_forms' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ApForms', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('form_groups[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FormGroups';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['group_forms']))
    {
      $this->setDefault('group_forms', $this->object->Forms->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateFormsList($values);

    parent::doUpdateObject($values);
  }

  public function updateFormsList($values)
  {
    if (!isset($this->widgetSchema['group_forms']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('group_forms', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->Forms->getPrimaryKeys();
    $values = $values['group_forms'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Forms', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Forms', array_values($link));
    }
  }

}
