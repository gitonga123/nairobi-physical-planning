<?php

class AgendaColumnsForm extends BaseAgendaColumnsForm
{
  public function configure()
  {
	$this->widgetSchema['form_id'] = new sfWidgetFormChoice(array(
		'choices' => Doctrine_Core::getTable('ApForms')->getAllApplicationForms(1),
	));
	$this->validatorSchema['form_id'] = new sfValidatorChoice(array(
		'choices' => array_keys(Doctrine_Core::getTable('ApForms')->getAllApplicationForms(1)),
	));
	$this->widgetSchema['element_id'] = new sfWidgetFormChoice(array(
		'choices' => array(''),
		'multiple' => true,
	));
	$this->widgetSchema['position'] = new sfWidgetFormInputHidden() ;
	$this->widgetSchema['entry_column'] = new sfWidgetFormChoice(array(
		'choices' => Doctrine_Core::getTable('FormEntry')->getColumnsAgenda(),
		'multiple' => true,
	));
	$this->validatorSchema['entry_column'] = new sfValidatorChoice(array(
		'choices' => array_keys(Doctrine_Core::getTable('FormEntry')->getColumnsAgenda()),
		'multiple' => true,
	));
	$elements=array();
	foreach(range(1,1000) as $e){
		$elements[]='element_'.$e;
	}
	$this->validatorSchema['element_id'] = new sfValidatorChoice(array(
		'choices' => $elements,
		'multiple' => true,
	));
	$this->widgetSchema['stage'] = new sfWidgetFormChoice(array(
		'choices' => Doctrine_Core::getTable('Menus')->getAllAgendastages(),
	));
  }
}
