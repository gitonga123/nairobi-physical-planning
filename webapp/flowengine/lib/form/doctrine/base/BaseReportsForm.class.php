<?php

/**
 * Reports form base class.
 *
 * @method Reports getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseReportsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'      => new sfWidgetFormInputHidden(),
      'type'    => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    2 => "Single Application Summary",
                    1 => "Multiple Applications Summary"
                )
            )
        ),
      'form_id' => new sfWidgetFormChoice(
            array(
                'choices' => Doctrine_Core::getTable('ApForms')->getForms()
            )
        ),
      'title'   => new sfWidgetFormInputText(),
      'content' => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'type'    => new sfValidatorInteger(array('required' => false)),
      'form_id' => new sfValidatorInteger(array('required' => false)),
      'title'   => new sfValidatorString(),
      'content' => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('reports[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Reports';
  }

}
