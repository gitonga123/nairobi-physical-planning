<?php

/**
 * SfGuardUserCategories filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSfGuardUserCategoriesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'description' => new sfWidgetFormFilterInput(),
      'formid'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'orderid'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'name'        => new sfValidatorPass(array('required' => false)),
      'description' => new sfValidatorPass(array('required' => false)),
      'formid'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'orderid'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_categories_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfGuardUserCategories';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'name'        => 'Text',
      'description' => 'Text',
      'formid'      => 'Number',
      'orderid'     => 'Number',
    );
  }
}
