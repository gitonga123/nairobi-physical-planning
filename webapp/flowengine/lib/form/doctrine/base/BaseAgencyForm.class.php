<?php

abstract class BaseAgencyForm extends BaseFormDoctrine
{
  public function setup()
  {
    $siteconfig = Doctrine_Core::getTable('ApSettings')->find(array(1));
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'name'    => new sfWidgetFormInputText(),
      'address'           => new sfWidgetFormTextarea(),
      'logo'       => new sfWidgetFormInputFile(array('label' => 'Agency Logo Image'), array('class' => 'form-control')),
      'tag_line'    => new sfWidgetFormInputText(),
      'parent_agency'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Agency'), 'add_empty' => true)),
      'users_list'       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'CfUser')),
      'menus_list'       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Menus')),
      'departments_list'       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Department')),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'           => new sfValidatorString(array('required' => false)),
      'address'           => new sfValidatorString(array('required' => false)),
      'logo'       => new sfValidatorFile(array(
        'required' => false,
        'path' => $siteconfig->getUploadDir(),
        'mime_types' => 'web_images',
        )),
      'tag_line'           => new sfValidatorString(array('required' => false)),
      'parent_agency'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Agency'), 'required' => false)),
      'users_list'       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'CfUser', 'required' => false)),
      'menus_list'       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Menus', 'required' => false)),
      'departments_list'       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Department', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('agency[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Agency';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['users_list']))
    {
      $this->setDefault('users_list', $this->object->Users->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['menus_list']))
    {
      $this->setDefault('menus_list', $this->object->Workflows->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['departments_list']))
    {
      $this->setDefault('departments_list', $this->object->Departments->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveUsersList($con);
    $this->saveMenusList($con);
    $this->saveDepartmentsList($con);

    parent::doSave($con);
  }

  public function saveUsersList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['users_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Users->getPrimaryKeys();
    $values = $this->getValue('users_list');
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

  public function saveMenusList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['menus_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Workflows->getPrimaryKeys();
    $values = $this->getValue('menus_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Workflows', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Workflows', array_values($link));
    }
  }

  public function saveDepartmentsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['departments_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Departments->getPrimaryKeys();
    $values = $this->getValue('departments_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Departments', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Departments', array_values($link));
    }
  }


}
