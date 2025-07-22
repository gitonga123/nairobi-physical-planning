<?php

/**
 * SfGuardUserCategoriesForms form base class.
 *
 * @method SfGuardUserCategoriesForms getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSfGuardUserCategoriesFormsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'categoryid'    => new sfWidgetFormInputText(),
      'formid'        => new sfWidgetFormInputText(),
      'islinkedto'    => new sfWidgetFormInputText(),
      'islinkedtitle' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'categoryid'    => new sfValidatorInteger(),
      'formid'        => new sfValidatorInteger(),
      'islinkedto'    => new sfValidatorInteger(array('required' => false)),
      'islinkedtitle' => new sfValidatorString(array('max_length' => 250, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_user_categories_forms[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfGuardUserCategoriesForms';
  }

}
