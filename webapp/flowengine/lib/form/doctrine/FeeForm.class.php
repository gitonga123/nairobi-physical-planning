<?php

/**
 * Fee form.
 *
 * @package    permitflow
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id$
 */
class FeeForm extends BaseFeeForm
{
  public function configure()
  {
	  $this->widgetSchema['fee_code']=new sfWidgetFormDoctrineChoice(
		array(
			'model' => 'FeeCode',
			'key_method' => 'getServiceId' 
		)
	  );
	  $this->validationSchema['fee_code']=new sfValidatorDoctrineChoice (
		array(
			'model' => 'FeeCode',
			'column' => 'service_id'
		)
	  );
  }
}
