<?php

/**
 * NbConnected filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BaseNbConnectedFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'ip'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'time' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'ip'   => new sfValidatorPass(array('required' => false)),
      'time' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('nb_connected_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'NbConnected';
  }

  public function getFields()
  {
    return array(
      'id'   => 'Number',
      'ip'   => 'Text',
      'time' => 'Number',
    );
  }
}
