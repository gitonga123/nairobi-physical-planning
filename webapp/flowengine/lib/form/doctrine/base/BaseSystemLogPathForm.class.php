<?php

/**
 * SavedPermit form base class.
 *
 * @method SavedPermit getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseSystemLogPathForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id' => new sfWidgetFormInputHidden(),
      'title' => new sfWidgetFormInputText(),
      'path' => new sfWidgetFormInputText()
    ));

    $this->setValidators(array(
      'id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title' => new sfValidatorString(),
      'path' => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('system_log_path[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SystemLogPath';
  }

}
