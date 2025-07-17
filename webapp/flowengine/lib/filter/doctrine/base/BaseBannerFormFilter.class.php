<?php

/**
 * Banner filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseBannerFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'title'       => new sfWidgetFormFilterInput(),
      'description' => new sfWidgetFormFilterInput(),
      'image'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'page_id'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'title'       => new sfValidatorPass(array('required' => false)),
      'description' => new sfValidatorPass(array('required' => false)),
      'image'       => new sfValidatorPass(array('required' => false)),
      'page_id'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('banner_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Banner';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'title'       => 'Text',
      'description' => 'Text',
      'image'       => 'Text',
      'page_id'     => 'Number',
    );
  }
}
