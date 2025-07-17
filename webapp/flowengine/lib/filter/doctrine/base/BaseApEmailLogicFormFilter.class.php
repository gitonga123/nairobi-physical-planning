<?php

/**
 * ApEmailLogic filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApEmailLogicFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'rule_all_any'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'target_email'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'template_name'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'custom_from_name'              => new sfWidgetFormFilterInput(),
      'custom_from_email'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'custom_replyto_email'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'custom_bcc'                    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'custom_subject'                => new sfWidgetFormFilterInput(),
      'custom_content'                => new sfWidgetFormFilterInput(),
      'custom_plain_text'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'custom_pdf_enable'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'custom_pdf_content'            => new sfWidgetFormFilterInput(),
      'delay_notification_until_paid' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'rule_all_any'                  => new sfValidatorPass(array('required' => false)),
      'target_email'                  => new sfValidatorPass(array('required' => false)),
      'template_name'                 => new sfValidatorPass(array('required' => false)),
      'custom_from_name'              => new sfValidatorPass(array('required' => false)),
      'custom_from_email'             => new sfValidatorPass(array('required' => false)),
      'custom_replyto_email'          => new sfValidatorPass(array('required' => false)),
      'custom_bcc'                    => new sfValidatorPass(array('required' => false)),
      'custom_subject'                => new sfValidatorPass(array('required' => false)),
      'custom_content'                => new sfValidatorPass(array('required' => false)),
      'custom_plain_text'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'custom_pdf_enable'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'custom_pdf_content'            => new sfValidatorPass(array('required' => false)),
      'delay_notification_until_paid' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_email_logic_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApEmailLogic';
  }

  public function getFields()
  {
    return array(
      'form_id'                       => 'Number',
      'rule_id'                       => 'Number',
      'rule_all_any'                  => 'Text',
      'target_email'                  => 'Text',
      'template_name'                 => 'Text',
      'custom_from_name'              => 'Text',
      'custom_from_email'             => 'Text',
      'custom_replyto_email'          => 'Text',
      'custom_bcc'                    => 'Text',
      'custom_subject'                => 'Text',
      'custom_content'                => 'Text',
      'custom_plain_text'             => 'Number',
      'custom_pdf_enable'             => 'Number',
      'custom_pdf_content'            => 'Text',
      'delay_notification_until_paid' => 'Number',
    );
  }
}
