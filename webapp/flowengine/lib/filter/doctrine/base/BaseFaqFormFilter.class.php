<?php

/**
 * Faq filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFaqFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'question'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'answer'     => new sfWidgetFormFilterInput(),
      'published'  => new sfWidgetFormFilterInput(),
      'posted_by'  => new sfWidgetFormFilterInput(),
      'email'      => new sfWidgetFormFilterInput(),
      'created_on' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'deleted'    => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'question'   => new sfValidatorPass(array('required' => false)),
      'answer'     => new sfValidatorPass(array('required' => false)),
      'published'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'posted_by'  => new sfValidatorPass(array('required' => false)),
      'email'      => new sfValidatorPass(array('required' => false)),
      'created_on' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'deleted'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('faq_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Faq';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'question'   => 'Text',
      'answer'     => 'Text',
      'published'  => 'Number',
      'posted_by'  => 'Text',
      'email'      => 'Text',
      'created_on' => 'Date',
      'deleted'    => 'Number',
    );
  }
}
