<?php

/**
 * Announcement form base class.
 *
 * @method Announcement getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseAnnouncementForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'content'    => new sfWidgetFormTextarea(),
      'start_date' => new sfWidgetFormInputText(),
      'end_date'   => new sfWidgetFormInputText(),
      'frontend'   => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    0 => "Backend",
                    1 => "Frontend"
                )
            )
        ),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'content'    => new sfValidatorString(),
      'start_date' => new sfValidatorString(array('max_length' => 250)),
      'end_date'   => new sfValidatorString(array('max_length' => 250)),
      'frontend'   => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('announcement[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Announcement';
  }

}
