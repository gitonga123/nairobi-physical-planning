<?php

/**
 * Department form base class.
 *
 * @method Department getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseDepartmentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'department_name' => new sfWidgetFormInputText(),
      'department_head' => new sfWidgetFormChoice(
            array(
                'choices' => Doctrine_Core::getTable('CfUser')->getReviewers()
            )
        ),
      'department_reviewers' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'CfUser')),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'department_name' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'department_head' => new sfValidatorInteger(array('required' => false)),
      'department_reviewers' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'CfUser', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('department[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Department';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['department_reviewers']))
    {
      $this->setDefault('department_reviewers', $this->object->Users->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateUsersList($values);

    parent::doUpdateObject($values);
  }

  public function updateUsersList($values)
  {
    if (!isset($this->widgetSchema['department_reviewers']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('department_reviewers', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->Users->getPrimaryKeys();
    $values = $values['department_reviewers'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Users', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Users', array_values($link));
    }
  }

}
