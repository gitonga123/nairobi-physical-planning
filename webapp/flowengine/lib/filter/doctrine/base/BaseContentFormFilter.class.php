<?php

/**
 * Content filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseContentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'parent_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'type_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ContentType'), 'add_empty' => true)),
      'created_on'       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'published'        => new sfWidgetFormFilterInput(),
      'menu_index'       => new sfWidgetFormFilterInput(),
      'url'              => new sfWidgetFormFilterInput(),
      'top_article'      => new sfWidgetFormFilterInput(),
      'menu_title'       => new sfWidgetFormFilterInput(),
      'breadcrumb_title' => new sfWidgetFormFilterInput(),
      'last_modified_on' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'visibility'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'deleted'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'parent_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'type_id'          => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ContentType'), 'column' => 'id')),
      'created_on'       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'published'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'menu_index'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'url'              => new sfValidatorPass(array('required' => false)),
      'top_article'      => new sfValidatorPass(array('required' => false)),
      'menu_title'       => new sfValidatorPass(array('required' => false)),
      'breadcrumb_title' => new sfValidatorPass(array('required' => false)),
      'last_modified_on' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'visibility'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'deleted'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('content_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Content';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'parent_id'        => 'Number',
      'type_id'          => 'ForeignKey',
      'created_on'       => 'Date',
      'published'        => 'Number',
      'menu_index'       => 'Number',
      'url'              => 'Text',
      'top_article'      => 'Text',
      'menu_title'       => 'Text',
      'breadcrumb_title' => 'Text',
      'last_modified_on' => 'Date',
      'visibility'       => 'Number',
      'deleted'          => 'Number',
    );
  }
}
