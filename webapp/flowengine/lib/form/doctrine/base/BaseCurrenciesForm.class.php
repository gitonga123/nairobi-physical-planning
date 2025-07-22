<?php

/**
 * //OTB patch - For Currency Settings
 *
 * @method Activity getObject() Returns the current form's model object
 *
 * @package    permit
 * @subpackage form
 * @author      boniface@otbafrica.com
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseCurrenciesForm extends BaseFormDoctrine
{
  //Use a helper class to get countries
  
  public function setup()
  {
     //OTB pacth Use a helper class to get countries
    $otbhelper = new OTBHelper() ;
    ///
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'name'          => new sfWidgetFormInputText(),
      'symbol'    => new sfWidgetFormInputText(),
      'code'    => new sfWidgetFormInputText(),
      'state' => new sfWidgetFormChoice(array('choices' =>$otbhelper->getCountries() )),
      // 'state' => new sfWidgetFormChoice($otbhelper->getCountries()),  
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'          => new sfValidatorString(array('required' => false)),
      'symbol'    => new sfValidatorString(array('required' => false)),
      'code'    => new sfValidatorString(array('required' => false)),
      'state' => new sfValidatorString(array('required' => false)),
    
    ));

    $this->widgetSchema->setNameFormat('currencies[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }
  
  

  public function getModelName()
  {
    return 'Currencies';
  }
  
}
