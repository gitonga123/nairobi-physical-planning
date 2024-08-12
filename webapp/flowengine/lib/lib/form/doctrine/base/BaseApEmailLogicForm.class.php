<?php

/**
 * ApEmailLogic form base class.
 *
 * @method ApEmailLogic getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApEmailLogicForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'form_id'                       => new sfWidgetFormInputHidden(),
      'rule_id'                       => new sfWidgetFormInputHidden(),
      'rule_all_any'                  => new sfWidgetFormInputText(),
      'target_email'                  => new sfWidgetFormTextarea(),
      'template_name'                 => new sfWidgetFormInputText(),
      'custom_from_name'              => new sfWidgetFormTextarea(),
      'custom_from_email'             => new sfWidgetFormInputText(),
      'custom_replyto_email'          => new sfWidgetFormInputText(),
      'custom_bcc'                    => new sfWidgetFormTextarea(),
      'custom_subject'                => new sfWidgetFormTextarea(),
      'custom_content'                => new sfWidgetFormTextarea(),
      'custom_plain_text'             => new sfWidgetFormInputText(),
      'custom_pdf_enable'             => new sfWidgetFormInputText(),
      'custom_pdf_content'            => new sfWidgetFormTextarea(),
      'delay_notification_until_paid' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'form_id'                       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'rule_id'                       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('rule_id')), 'empty_value' => $this->getObject()->get('rule_id'), 'required' => false)),
      'rule_all_any'                  => new sfValidatorString(array('max_length' => 3, 'required' => false)),
      'target_email'                  => new sfValidatorString(),
      'template_name'                 => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'custom_from_name'              => new sfValidatorString(array('required' => false)),
      'custom_from_email'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'custom_replyto_email'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'custom_bcc'                    => new sfValidatorString(array('required' => false)),
      'custom_subject'                => new sfValidatorString(array('required' => false)),
      'custom_content'                => new sfValidatorString(array('required' => false)),
      'custom_plain_text'             => new sfValidatorInteger(array('required' => false)),
      'custom_pdf_enable'             => new sfValidatorInteger(array('required' => false)),
      'custom_pdf_content'            => new sfValidatorString(array('required' => false)),
      'delay_notification_until_paid' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_email_logic[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApEmailLogic';
  }

}
