<?php

/**
 * Buttons form base class.
 *
 * @method Buttons getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseButtonsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'      => new sfWidgetFormInputHidden(),
      'title'   => new sfWidgetFormTextarea(),
      'link'    => new sfWidgetFormTextarea(),
      'submenus_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Submenus')),
    ));

    $this->setValidators(array(
      'id'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title'   => new sfValidatorString(),
      'link'    => new sfValidatorString(),
      'submenus_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Submenus', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('buttons[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Buttons';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['submenus_list']))
    {
      $this->setDefault('submenus_list', $this->object->Submenus->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveSubmenusList($con);

    parent::doSave($con);
  }

  public function saveSubmenusList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['submenus_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Submenus->getPrimaryKeys();
    $values = $this->getValue('submenus_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Submenus', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Submenus', array_values($link));
    }
  }

}
