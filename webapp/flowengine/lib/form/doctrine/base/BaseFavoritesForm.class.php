<?php

/**
 * Favorites form base class.
 *
 * @method Favorites getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseFavoritesForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'subject'        => new sfWidgetFormInputText(),
      'content'        => new sfWidgetFormInputText(),
      'application_id' => new sfWidgetFormInputText(),
      'isread'         => new sfWidgetFormInputText(),
      'userid'         => new sfWidgetFormInputText(),
      'datesent'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'subject'        => new sfValidatorString(array('max_length' => 250)),
      'content'        => new sfValidatorString(array('max_length' => 250)),
      'application_id' => new sfValidatorInteger(),
      'isread'         => new sfValidatorInteger(),
      'userid'         => new sfValidatorInteger(),
      'datesent'       => new sfValidatorString(array('max_length' => 250)),
    ));

    $this->widgetSchema->setNameFormat('favorites[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Favorites';
  }

}
