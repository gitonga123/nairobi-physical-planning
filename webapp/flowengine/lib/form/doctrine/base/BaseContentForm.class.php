<?php

/**
 * Content form base class.
 *
 * @method Content getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseContentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'menu_title'       => new sfWidgetFormInputText(),
      'top_article'      => new sfWidgetFormTextarea(),
      'published'        => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    0 => "Not Published",
                    1 => "Published"
                )
            )
        ),
      'parent_id'    => new sfWidgetFormChoice(
            array(
                'choices' => Doctrine_Core::getTable('Content')->getPages()
            )
        )
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'menu_title'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'top_article'      => new sfValidatorString(array('required' => false)),
      'published'        => new sfValidatorInteger(array('required' => false)),
      'parent_id'        => new sfValidatorInteger(array('required' => false))
    ));

    $this->widgetSchema->setNameFormat('content[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Content';
  }

}
