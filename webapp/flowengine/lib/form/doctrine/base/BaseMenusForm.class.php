<?php

/**
 * Menus form base class.
 *
 * @method Menus getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseMenusForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'       => new sfWidgetFormInputHidden(),
      'title'    => new sfWidgetFormInputText(),
      'service_type'      => new sfWidgetFormChoice(
            array(
                'choices' => array("1" => "Workflow Processing", "2" => "Cyclic Billing")
            )
        ),
      'service_form'      => new sfWidgetFormChoice(
            array(
                'choices' => Doctrine_Core::getTable('ApForms')->getExtraForms()
            )
        ),
      'service_number'    => new sfWidgetFormInputText(),
	  'category_id' => new sfWidgetFormDoctrineChoice(
		array(
			'model' => 'WorkflowCategory',
			'order_by' => array('order_id','asc')
		)
	  ),
    ));

    $this->setValidators(array(
      'id'       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title'    => new sfValidatorString(),
      'service_type'      => new sfValidatorInteger(array('required' => false)),
      'service_number'      => new sfValidatorString(),
      'service_form'      => new sfValidatorInteger(array('required' => false)),
	  'category_id' => new sfValidatorDoctrineChoice(
		array(
			'model' => 'WorkflowCategory'
		)
	  ),
    ));

    $this->widgetSchema->setNameFormat('menus[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Menus';
  }

}
