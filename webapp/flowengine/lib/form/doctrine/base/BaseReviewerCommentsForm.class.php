<?php

/**
 * ReviewerComments form base class.
 *
 * @method ReviewerComments getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseReviewerCommentsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'commentcontent' => new sfWidgetFormTextarea(),
      'date_created'   => new sfWidgetFormTextarea(),
      'reviewer_id'    => new sfWidgetFormInputText(),
      'sender_id'      => new sfWidgetFormInputText(),
      'messageread'    => new sfWidgetFormInputText(),
      'application_id' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'commentcontent' => new sfValidatorString(),
      'date_created'   => new sfValidatorString(),
      'reviewer_id'    => new sfValidatorInteger(array('required' => false)),
      'sender_id'      => new sfValidatorInteger(array('required' => false)),
      'messageread'    => new sfValidatorInteger(array('required' => false)),
      'application_id' => new sfValidatorString(array('max_length' => 254)),
    ));

    $this->widgetSchema->setNameFormat('reviewer_comments[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReviewerComments';
  }

}
