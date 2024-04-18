<?php

/**
 * SfGuardUserAssociates filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSfGuardUserAssociatesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'senderid'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'receiverid'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'accepted'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'seemyplans'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'modifymyplans' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'senderid'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'receiverid'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'accepted'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'seemyplans'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'modifymyplans' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_associates_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfGuardUserAssociates';
  }

  public function getFields()
  {
    return array(
      'id'            => 'Number',
      'senderid'      => 'Number',
      'receiverid'    => 'Number',
      'accepted'      => 'Number',
      'seemyplans'    => 'Number',
      'modifymyplans' => 'Number',
    );
  }
}
