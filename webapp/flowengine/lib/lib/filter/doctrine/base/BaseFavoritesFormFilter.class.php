<?php

/**
 * Favorites filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFavoritesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'subject'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'content'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'application_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'isread'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'userid'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'datesent'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'subject'        => new sfValidatorPass(array('required' => false)),
      'content'        => new sfValidatorPass(array('required' => false)),
      'application_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'isread'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'userid'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'datesent'       => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('favorites_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Favorites';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'subject'        => 'Text',
      'content'        => 'Text',
      'application_id' => 'Number',
      'isread'         => 'Number',
      'userid'         => 'Number',
      'datesent'       => 'Text',
    );
  }
}
