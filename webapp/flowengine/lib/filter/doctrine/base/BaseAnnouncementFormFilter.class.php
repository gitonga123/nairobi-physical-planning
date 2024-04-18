<?php

/**
 * Announcement filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseAnnouncementFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'content'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'start_date' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'end_date'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'frontend'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'content'    => new sfValidatorPass(array('required' => false)),
      'start_date' => new sfValidatorPass(array('required' => false)),
      'end_date'   => new sfValidatorPass(array('required' => false)),
      'frontend'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('announcement_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Announcement';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'content'    => 'Text',
      'start_date' => 'Text',
      'end_date'   => 'Text',
      'frontend'   => 'Number',
    );
  }
}
