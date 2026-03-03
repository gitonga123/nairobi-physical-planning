<?php

/**
 * Communication filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCommunicationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'sender'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'receiver'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'message'        => new sfWidgetFormFilterInput(),
      'reply'          => new sfWidgetFormFilterInput(),
      'isread'         => new sfWidgetFormFilterInput(),
      'created_on'     => new sfWidgetFormFilterInput(),
      'application_id' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'sender'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'receiver'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'message'        => new sfValidatorPass(array('required' => false)),
      'reply'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'isread'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_on'     => new sfValidatorPass(array('required' => false)),
      'application_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('communication_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Communication';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'sender'         => 'Number',
      'receiver'       => 'Number',
      'message'        => 'Text',
      'reply'          => 'Number',
      'isread'         => 'Number',
      'created_on'     => 'Text',
      'application_id' => 'Number',
    );
  }
}
