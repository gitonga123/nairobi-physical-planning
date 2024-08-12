<?php

/**
 * SfGuardUserCategoriesForms filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSfGuardUserCategoriesFormsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'categoryid'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'formid'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'islinkedto'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'islinkedtitle' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'categoryid'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'formid'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'islinkedto'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'islinkedtitle' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_categories_forms_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfGuardUserCategoriesForms';
  }

  public function getFields()
  {
    return array(
      'id'            => 'Number',
      'categoryid'    => 'Number',
      'formid'        => 'Number',
      'islinkedto'    => 'Number',
      'islinkedtitle' => 'Text',
    );
  }
}
