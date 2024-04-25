<?php

/**
 * ReviewerComments filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseReviewerCommentsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'commentcontent' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_created'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'reviewer_id'    => new sfWidgetFormFilterInput(),
      'sender_id'      => new sfWidgetFormFilterInput(),
      'messageread'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'application_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'commentcontent' => new sfValidatorPass(array('required' => false)),
      'date_created'   => new sfValidatorPass(array('required' => false)),
      'reviewer_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'sender_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'messageread'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'application_id' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('reviewer_comments_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReviewerComments';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'commentcontent' => 'Text',
      'date_created'   => 'Text',
      'reviewer_id'    => 'Number',
      'sender_id'      => 'Number',
      'messageread'    => 'Number',
      'application_id' => 'Text',
    );
  }
}
