<?php

/**
 * Plot form base class.
 *
 * @method Plot getObject() Returns the current form's model object
 *
 * @package    permit
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BasePlotForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'plot_no'     => new sfWidgetFormInputText(),
      'plot_type'   => new sfWidgetFormInputText(),
      'plot_status' => new sfWidgetFormInputText(),
      'plot_size'   => new sfWidgetFormInputText(),
      'plot_lat'    => new sfWidgetFormInputText(),
      'plot_long'   => new sfWidgetFormInputText(),
      'plot_location'   => new sfWidgetFormInputText(),
      'plot_comments'   => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'plot_no'   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'plot_type'   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'plot_status' => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'plot_size'   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'plot_lat'    => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'plot_long'   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'plot_location'   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'plot_comments'   => new sfValidatorString(array('max_length' => 250, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('plot[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Plot';
  }

}
