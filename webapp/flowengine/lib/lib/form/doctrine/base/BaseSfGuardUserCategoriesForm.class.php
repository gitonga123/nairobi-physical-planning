<?php

/**
 * SfGuardUserCategories form base class.
 *
 * @method SfGuardUserCategories getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSfGuardUserCategoriesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $forms_members = Doctrine_Core::getTable('ApForms')->getAllApplicationForms(4);
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'name'        => new sfWidgetFormInputText(),
      'description' => new sfWidgetFormTextarea(),
      'formid'      => new sfWidgetFormChoice(
        array(
          'choices' => Doctrine_Core::getTable('ApForms')->getExtraForms()
        )
      ),
      'orderid'     => new sfWidgetFormInputText(),
      //OTB Start - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc.
      'member_no_element_id' => new sfWidgetFormChoice(array('choices' => array())),
      'validation_email_element_id' => new sfWidgetFormChoice(array('choices' => array())),
      'membership_email_match'             => new sfWidgetFormChoice(array('choices' => array(0 => 'No', 1 => 'Yes'))),
      'send_verification_email'             => new sfWidgetFormChoice(array('choices' => array(0 => 'No', 1 => 'Yes'))),
      'member_association_name'  => new sfWidgetFormInputText(),
      'member_email_verification_message'  => new sfWidgetFormTextArea(),
      'member_database' => new sfWidgetFormChoice(array('choices' => $forms_members)),
      'member_database_member_no_field' => new sfWidgetFormChoice(array('choices' => array())),
      'member_database_member_email_field' => new sfWidgetFormChoice(array('choices' => array())),
      'member_database_member_name_field' => new sfWidgetFormChoice(array('choices' => array())),
      'member_address' => new sfWidgetFormChoice(array('choices' => array())),
      'member_database_member_one_single_use' => new sfWidgetFormChoice(array('choices' => array()), array('class' => 'form-control')),
      //OTB End - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc.
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'        => new sfValidatorString(array('max_length' => 250)),
      'description' => new sfValidatorString(array('required' => false)),
      'formid'      => new sfValidatorInteger(array('required' => false)),
      'orderid'     => new sfValidatorInteger(),
      //OTB Start - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc.
      'member_no_element_id'    => new sfValidatorInteger(array('required' => false)),
      'validation_email_element_id'    => new sfValidatorInteger(array('required' => false)),
      'membership_email_match'        => new sfValidatorNumber(array('max' => 1, 'required' => false)),
      'send_verification_email'        => new sfValidatorNumber(array('max' => 1, 'required' => false)),
      'member_association_name'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'member_email_verification_message' => new sfValidatorString(array('max_length' => 65536, 'required' => false)),
      'member_database'    => new sfValidatorChoice(array('choices' => array_keys($forms_members), 'required' => false)),
      'member_database_member_no_field'    => new sfValidatorInteger(array('required' => false)),
      'member_database_member_email_field'    => new sfValidatorInteger(array('required' => false)),
      'member_database_member_name_field'    => new sfValidatorInteger(array('required' => false)),
      'member_address'    => new sfValidatorInteger(array('required' => false)),
      'member_database_member_one_single_use'      => new sfValidatorInteger(array('required' => false)),
      //OTB End - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc.
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_categories[%s]');
    $this->widgetSchema['member_email_verification_message']->setDefault('Dear {member_full_name},<br/>{user_full_name} is trying to use your {association} number {membership_no} on the eConstruction permitting system. Kindly click on the following link to validate this request {validation_link}');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfGuardUserCategories';
  }
}
