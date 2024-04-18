<?php

/**
 * PlotActivity filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id$
 */
abstract class BasePlotActivityFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'plot_id'  => new sfWidgetFormFilterInput(),
      'entry_id' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'plot_id'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'entry_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('plot_activity_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PlotActivity';
  }

  public function getFields()
  {
    return array(
      'id'       => 'Number',
      'plot_id'  => 'Number',
      'entry_id' => 'Number',
    );
  }
}
