<?php

/**
 * News filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseNewsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'title'      => new sfWidgetFormFilterInput(),
      'article'    => new sfWidgetFormFilterInput(),
      'created_by' => new sfWidgetFormFilterInput(),
      'created_on' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'published'  => new sfWidgetFormFilterInput(),
      'hits'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'agency_id'  => new sfWidgetFormFilterInput(),
      'deleted'    => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'title'      => new sfValidatorPass(array('required' => false)),
      'article'    => new sfValidatorPass(array('required' => false)),
      'created_by' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_on' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'published'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'hits'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'agency_id'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'deleted'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('news_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'News';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'title'      => 'Text',
      'article'    => 'Text',
      'created_by' => 'Number',
      'created_on' => 'Date',
      'published'  => 'Number',
      'hits'       => 'Number',
      'agency_id'  => 'Number',
      'deleted'    => 'Number',
    );
  }
}
