<?php

/**
 * ApiContentForm form.
 *
 */
class ApiContentForm extends BaseApiContentForm
{
  public function configure()
  {
	$this->widgetSchema['form_id'] = new sfWidgetFormChoice(array(
		'choices' => Doctrine_Core::getTable('ApForms')->getForms()
	));
	$this->validatorSchmea['form_id'] = new sfValidatorChoice(array(
		'choices' => array_keys(Doctrine_Core::getTable('ApForms')->getForms())
	));
	$this->widgetSchema['api_use'] = new sfWidgetFormChoice(array(
		'choices' => Doctrine_Core::getTable('ApiContent')->getApiuses()
	));
	$this->validatorSchmea['api_use'] = new sfValidatorChoice(array(
		'choices' => array_keys(Doctrine_Core::getTable('ApiContent')->getApiuses())
	));
	$this->widgetSchema['merchant_id'] = new sfWidgetFormDoctrineChoice(array(
		'model' => 'Merchant'
	));
	$this->validatorSchmea['merchant_id'] = new sfValidatorDoctrineChoice(array(
		'model' => 'Merchant' 
	));
	$this->widgetSchema['api_use_diff'] = new sfWidgetFormChoice(array(
		'choices' => Doctrine_Core::getTable('ApiContent')->getApiusesdiff()
	));
	$this->validatorSchmea['api_use_diff'] = new sfValidatorChoice(array(
		'choices' => array_keys(Doctrine_Core::getTable('ApiContent')->getApiusesdiff())
	));
  }
}
