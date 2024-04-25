<?php

/**
 * CfUserIndex filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCfUserIndexFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'indexe'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'indexe'  => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('cf_user_index_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CfUserIndex';
  }

  public function getFields()
  {
    return array(
      'user_id' => 'Number',
      'indexe'  => 'Text',
    );
  }
}
