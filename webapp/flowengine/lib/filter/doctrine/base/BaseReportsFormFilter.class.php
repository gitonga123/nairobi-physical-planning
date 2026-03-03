<?php

/**
 * Reports filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseReportsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'type'    => new sfWidgetFormFilterInput(),
      'form_id' => new sfWidgetFormFilterInput(),
      'title'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'content' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'type'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'form_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'title'   => new sfValidatorPass(array('required' => false)),
      'content' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('reports_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Reports';
  }

  public function getFields()
  {
    return array(
      'id'      => 'Number',
      'type'    => 'Number',
      'form_id' => 'Number',
      'title'   => 'Text',
      'content' => 'Text',
    );
  }
}
