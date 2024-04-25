<?php

/**
 * Permits form base class.
 *
 * @method Permits getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BasePermitsForm extends BaseFormDoctrine
{
  public function setup()
  {
        if(sfContext::getInstance()->getUser()->getAttribute('service_type') == 2)
        {
            $this->setWidgets(array(
            'id'                  => new sfWidgetFormInputHidden(),
            'title'               => new sfWidgetFormInputText(),
            'applicationform'  => new sfWidgetFormChoice(
                    array(
                        'choices' => Doctrine_Core::getTable('ApForms')->getExtraForms()
                    )
                ),
            'applicationstage' => new sfWidgetFormChoice(
                    array(
                        'choices' => Doctrine_Core::getTable('SubMenus')->getStages()
                    )
                ),
            'parttype'            => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            1 => "Service for Clients and Reviewers",
                            2 => "PDF for Client to Download and Attach",
                            3 => "Service for Reviewers Only"
                        )
                    )
                ),
            'content'             => new sfWidgetFormTextarea(),
            'footer'              => new sfWidgetFormTextarea(),
            'max_duration'        => new sfWidgetFormInputText(),
            'remote_url'          => new sfWidgetFormInputText(),
            'remote_field'        => new sfWidgetFormTextarea(),
            'remote_username'     => new sfWidgetFormInputText(),
            'remote_password'     => new sfWidgetFormInputPassword(),
            'remote_request_type' => new sfWidgetFormInputText(),
            'expiration_type'  => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            1 => "Expires after a specified number of days",
                            2 => "Expires at the end of each month",
                            3 => "Expires at the end of each year"
                        )
                    )
                ),
            'page_type'          => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            "A4" => "A4",
                            "A5" => "A5"
                        )
                    )
                ),
            'page_orientation'    => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            "potrait" => "Potrait",
                            "landscape" => "Landscape"
                        )
                    )
                ),
            'qr_content'          => new sfWidgetFormTextarea(),
            'expiry_trigger'          => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            "0" => "Do nothing",
                            "1" => "Generate invoice",
                            "2" => "Trigger stage movement",
                            "3" => "Trigger stage movement and generate invoice",
                        )
                    )
                ),
				'allow_issue_last_paid' => new sfWidgetFormChoice(
					array(
						'choices' => array(
							0 => "Disabled",
							1 => "Enabled"
						)
					)
                ),
				'check_conditions' => new sfWidgetFormChoice(
					array(
						'choices' => array(
							0 => "Disabled",
							1 => "Enabled"
						)
					)
                ),
				'allows_signing' => new sfWidgetFormChoice(
					array(
						'choices' => array(
							0 => "No",
							1 => "Yes"
						)
					)
                ),
            ));
        }
        else 
        {
            $this->setWidgets(array(
            'id'                  => new sfWidgetFormInputHidden(),
            'title'               => new sfWidgetFormInputText(),
            'applicationform'  => new sfWidgetFormChoice(
                    array(
                        'choices' => Doctrine_Core::getTable('ApForms')->getForms()
                    )
                ),
            'applicationstage' => new sfWidgetFormChoice(
                    array(
                        'choices' => Doctrine_Core::getTable('SubMenus')->getStages()
                    )
                ),
            'parttype'            => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            1 => "Service for Clients and Reviewers",
                            2 => "PDF for Client to Download and Attach",
                            3 => "Service for Reviewers Only"
                        )
                    )
                ),
            'content'             => new sfWidgetFormTextarea(),
            'footer'              => new sfWidgetFormTextarea(),
            'max_duration'        => new sfWidgetFormInputText(),
            'remote_url'          => new sfWidgetFormInputText(),
            'remote_field'        => new sfWidgetFormTextarea(),
            'remote_username'     => new sfWidgetFormInputText(),
            'remote_password'     => new sfWidgetFormInputText(),
            'remote_request_type' => new sfWidgetFormInputText(),
            'expiration_type'  => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            1 => "Expires after a specified number of days",
                            2 => "Expires at the end of each month",
                            3 => "Expires at the end of each year"
                        )
                    )
                ),
            'page_type'          => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            "A4" => "A4",
                            "A5" => "A5"
                        )
                    )
                ),
            'page_orientation'    => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            "potrait" => "Potrait",
                            "landscape" => "Landscape"
                        )
                    )
                ),
            'qr_content'          => new sfWidgetFormTextarea(),
            'expiry_trigger'          => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            "0" => "Do nothing",
                            "1" => "Generate invoice",
                            "2" => "Trigger stage movement",
                            "3" => "Trigger stage movement and generate invoice",
                        )
                    )
                ),
				'allow_issue_last_paid' => new sfWidgetFormChoice(
					array(
						'choices' => array(
							0 => "Disabled",
							1 => "Enabled"
						)
					)
                ),
				'check_conditions' => new sfWidgetFormChoice(
					array(
						'choices' => array(
							0 => "Disabled",
							1 => "Enabled"
						)
					)
                ),
                'allows_signing' => new sfWidgetFormChoice(
                    array(
                        'choices' => array(
                            0 => "No",
                            1 => "Yes"
                        )
                    )
                ),
            ));
        }

    $this->setValidators(array(
      'id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title'               => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'applicationform'     => new sfValidatorInteger(array('required' => false)),
      'applicationstage'    => new sfValidatorInteger(array('required' => false)),
      'parttype'            => new sfValidatorInteger(array('required' => false)),
      'content'             => new sfValidatorString(),
      'footer'              => new sfValidatorString(array('required' => false)),
      'max_duration'        => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'remote_url'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'remote_field'        => new sfValidatorString(array('required' => false)),
      'remote_username'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'remote_password'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'remote_request_type' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'expiration_type'     => new sfValidatorInteger(array('required' => false)),
      'page_type'           => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'page_orientation'    => new sfValidatorString(array('max_length' => 250, 'required' => false)),
      'qr_content'          => new sfValidatorString(array('required' => false)),
      'expiry_trigger'      => new sfValidatorInteger(array('required' => false)),
      'allow_issue_last_paid' => new sfValidatorInteger(array('required' => false)),
      'check_conditions' => new sfValidatorInteger(array('required' => false)),
      'allows_signing' => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('permits[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Permits';
  }

}
