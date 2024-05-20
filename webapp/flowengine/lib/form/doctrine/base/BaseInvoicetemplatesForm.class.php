<?php

/**
 * Invoicetemplates form base class.
 *
 * @method Invoicetemplates getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseInvoicetemplatesForm extends BaseFormDoctrine
{
  public function setup()
  {
    if(sfContext::getInstance()->getUser()->getAttribute('service_type') == 2)
    {
        $this->setWidgets(array(
        'id'               => new sfWidgetFormInputHidden(),
        'title'            => new sfWidgetFormInputText(),
        'applicationform'  => new sfWidgetFormChoice(
                array(
                    'choices' => Doctrine_Core::getTable('ApForms')->getExtraForms()
                )
            ),
        'applicationstage' => new sfWidgetFormChoice(
                array(
                    'choices' => Doctrine_Core::getTable('SubMenus')->getStages()
                )
            ),
        'content'          => new sfWidgetFormTextarea(),
        'max_duration'     => new sfWidgetFormInputText(),
        'due_duration'     => new sfWidgetFormInputText(),
        'invoice_number'   => new sfWidgetFormInputText(),
        'expiration_type'  => new sfWidgetFormChoice(
                array(
                    'choices' => array(
                        1 => "Expires after a specified number of days",
                        2 => "Expires at the end of each month",
                        3 => "Expires at the end of each year"
                    )
                )
            ),
        'payment_type'     => new sfWidgetFormChoice(
                array(
                    'choices' => array(
                        1 => "Allow only full payments",
                        2 => "Allow partial payments"
                    )
                )
            ),
        ));
    }
    else
    {
        $this->setWidgets(array(
        'id'               => new sfWidgetFormInputHidden(),
        'title'            => new sfWidgetFormInputText(),
        'applicationform'  => new sfWidgetFormChoice(
                array(
                    'choices' => Doctrine_Core::getTable('ApForms')->getForms()
                )
            ),
        'applicationstage' => new sfWidgetFormChoice(
                array(
                    'choices' => Doctrine_Core::getTable('SubMenus')->getStages()
                )
            ),
        'content'          => new sfWidgetFormTextarea(),
        'max_duration'     => new sfWidgetFormInputText(),
        'due_duration'     => new sfWidgetFormInputText(),
        'invoice_number'   => new sfWidgetFormInputText(),
        'expiration_type'  => new sfWidgetFormChoice(
                array(
                    'choices' => array(
                        1 => "Expires after a specified number of days",
                        2 => "Expires at the end of each month",
                        3 => "Expires at the end of each year"
                    )
                )
            ),
        'payment_type'     => new sfWidgetFormChoice(
                array(
                    'choices' => array(
                        1 => "Allow only full payments",
                        2 => "Allow partial payments"
                    )
                )
            ),
        ));
    }

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'applicationform'  => new sfValidatorInteger(array('required' => false)),
      'applicationstage' => new sfValidatorInteger(array('required' => false)),
      'content'          => new sfValidatorString(),
      'max_duration'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'due_duration'     => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'invoice_number'   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'expiration_type'  => new sfValidatorInteger(array('required' => false)),
      'payment_type'     => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('invoicetemplates[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Invoicetemplates';
  }

}
