<?php

/**
 * History filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BaseHistoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'subject'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'content'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'link'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'isread'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'userid'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'datesent' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'subject'  => new sfValidatorPass(array('required' => false)),
      'content'  => new sfValidatorPass(array('required' => false)),
      'link'     => new sfValidatorPass(array('required' => false)),
      'isread'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'userid'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'datesent' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('history_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'History';
  }

  public function getFields()
  {
    return array(
      'id'       => 'Number',
      'subject'  => 'Text',
      'content'  => 'Text',
      'link'     => 'Text',
      'isread'   => 'Number',
      'userid'   => 'Number',
      'datesent' => 'Text',
    );
  }
}
