<?php

/**
 * //OTB patch - For Merchant details
 *
 * @method Activity getObject() Returns the current form's model object
 *
 * @package    permit
 * @subpackage form
 * @author      boniface@otbafrica.com
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseMerchantForm extends BaseFormDoctrine
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
      'description'    => new sfWidgetFormInputText(),
     
      'currency_id'=> new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Currency'), 'add_empty' => false)), 
      'link'    => new sfWidgetFormInputText(),
      'status'    => new sfWidgetFormChoice(array('choices'=> array(0 => 'Inactive', 1 => 'Active'))),  
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'          => new sfValidatorString(array('required' => true)),
      'description'    => new sfValidatorString(array('required' => false)),
      'link'    => new sfValidatorString(array('required' => false)),
      'currency_id'    => new sfValidatorInteger(array('required' => true)),
      'status'    => new sfValidatorInteger(array('required' => true)),
    
    ));

    $this->widgetSchema->setNameFormat('merchant[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }
  
  

  public function getModelName()
  {
    return 'Merchant';
  }
  

}
