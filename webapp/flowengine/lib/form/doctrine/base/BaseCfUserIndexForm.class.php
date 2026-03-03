<?php

/**
 * CfUserIndex form base class.
 *
 * @method CfUserIndex getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCfUserIndexForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id' => new sfWidgetFormInputHidden(),
      'indexe'  => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'user_id' => new sfValidatorChoice(array('choices' => array($this->getObject()->get('user_id')), 'empty_value' => $this->getObject()->get('user_id'), 'required' => false)),
      'indexe'  => new sfValidatorString(),
    ));

    $this->widgetSchema->setNameFormat('cf_user_index[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CfUserIndex';
  }

}
