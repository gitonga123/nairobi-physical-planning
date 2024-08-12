<?php

/**
 * PenaltyTemplate form base class.
 *
 * @method PenaltyTemplate getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BasePenaltyTemplateForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'description'               => new sfWidgetFormTextarea(),
      'template_id'  => new sfWidgetFormChoice(
            array(
                'choices' => Doctrine_Core::getTable('Permits')->getPermitTemplates()
            )
        ),
      'trigger_type'            => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    1 => "Number of days",
                    2 => "Number of months"
                )
            )
        ),
      'trigger_period'             => new sfWidgetFormInputText(),
      'penalty_type'            => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    1 => "Percentage of total",
                    2 => "Fixed amount",
					3 => "Percentage of service fee"
                )
            )
        ),
        'penalty_amount'             => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'description'               => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'template_id'     => new sfValidatorInteger(array('required' => false)),
      'trigger_type'    => new sfValidatorInteger(array('required' => false)),
      'trigger_period'             => new sfValidatorString(),
      'penalty_type'     => new sfValidatorInteger(array('required' => false)),
      'penalty_amount'             => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('penalties[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PenaltyTemplate';
  }

}
