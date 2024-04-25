<?php

/**
 * SubMenus filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSubMenusFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'title'                    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'order_no'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'max_duration'             => new sfWidgetFormFilterInput(),
      'deleted'                  => new sfWidgetFormFilterInput(),
      'change_identifier'        => new sfWidgetFormFilterInput(),
      'new_identifier'           => new sfWidgetFormFilterInput(),
      'new_identifier_start'     => new sfWidgetFormFilterInput(),
      'menu_id'                  => new sfWidgetFormFilterInput(),
      'hide_comments'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'allow_edit'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'stage_type'               => new sfWidgetFormFilterInput(),
      'stage_property'           => new sfWidgetFormFilterInput(),
      'stage_expired_movement'   => new sfWidgetFormFilterInput(),
      'stage_type_movement'      => new sfWidgetFormFilterInput(),
      'stage_type_notification'  => new sfWidgetFormFilterInput(),
      'stage_type_movement_fail' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'title'                    => new sfValidatorPass(array('required' => false)),
      'order_no'                 => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'max_duration'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'deleted'                  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'change_identifier'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'new_identifier'           => new sfValidatorPass(array('required' => false)),
      'new_identifier_start'     => new sfValidatorPass(array('required' => false)),
      'menu_id'                  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'hide_comments'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'allow_edit'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'stage_type'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'stage_property'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'stage_expired_movement'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'stage_type_movement'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'stage_type_notification'  => new sfValidatorPass(array('required' => false)),
      'stage_type_movement_fail' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('sub_menus_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SubMenus';
  }

  public function getFields()
  {
    return array(
      'id'                       => 'Number',
      'title'                    => 'Text',
      'order_no'                 => 'Number',
      'max_duration'             => 'Number',
      'deleted'                  => 'Number',
      'change_identifier'        => 'Number',
      'new_identifier'           => 'Text',
      'new_identifier_start'     => 'Text',
      'menu_id'                  => 'Number',
      'hide_comments'            => 'Number',
      'allow_edit'               => 'Number',
      'stage_type'               => 'Number',
      'stage_property'           => 'Number',
      'stage_expired_movement'   => 'Number',
      'stage_type_movement'      => 'Number',
      'stage_type_notification'  => 'Text',
      'stage_type_movement_fail' => 'Number',
    );
  }
}
