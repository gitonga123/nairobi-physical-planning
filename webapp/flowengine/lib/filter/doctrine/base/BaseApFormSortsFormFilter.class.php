<?php

/**
 * ApFormSorts filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApFormSortsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'sort_by' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'sort_by' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_form_sorts_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApFormSorts';
  }

  public function getFields()
  {
    return array(
      'user_id' => 'Number',
      'sort_by' => 'Text',
    );
  }
}
