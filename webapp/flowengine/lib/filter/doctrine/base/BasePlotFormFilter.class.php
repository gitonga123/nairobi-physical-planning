<?php

/**
 * Plot filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BasePlotFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'plot_no'       => new sfWidgetFormFilterInput(),
      'plot_type'     => new sfWidgetFormFilterInput(),
      'plot_status'   => new sfWidgetFormFilterInput(),
      'plot_size'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'plot_lat'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'plot_long'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'plot_location' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'plot_no'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'plot_type'     => new sfValidatorPass(array('required' => false)),
      'plot_status'   => new sfValidatorPass(array('required' => false)),
      'plot_size'     => new sfValidatorPass(array('required' => false)),
      'plot_lat'      => new sfValidatorPass(array('required' => false)),
      'plot_long'     => new sfValidatorPass(array('required' => false)),
      'plot_location' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('plot_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Plot';
  }

  public function getFields()
  {
    return array(
      'id'            => 'Number',
      'plot_no'       => 'Number',
      'plot_type'     => 'Text',
      'plot_status'   => 'Text',
      'plot_size'     => 'Text',
      'plot_lat'      => 'Text',
      'plot_long'     => 'Text',
      'plot_location' => 'Text',
    );
  }
}
