<?php

/**
 * Buttons filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseButtonsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'img'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'title'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'tooltip' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'link'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'img'     => new sfValidatorPass(array('required' => false)),
      'title'   => new sfValidatorPass(array('required' => false)),
      'tooltip' => new sfValidatorPass(array('required' => false)),
      'link'    => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('buttons_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Buttons';
  }

  public function getFields()
  {
    return array(
      'id'      => 'Number',
      'img'     => 'Text',
      'title'   => 'Text',
      'tooltip' => 'Text',
      'link'    => 'Text',
    );
  }
}
