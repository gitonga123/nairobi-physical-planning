<?php 

class BackendServiceSettingsForm extends BaseForm
{
  public function configure()
  {
    $this->setWidgets(array(
        'form_type'    => new sfWidgetFormChoice(
            array(
                'choices' => array(
                    1 => "Application Form",
                    2 => "Comment Sheet"
                )
            )
        ),
        'form_stage'    => new sfWidgetFormChoice(
            array(
                'choices' => Doctrine_Core::getTable('SubMenus')->getStages()
            )
        ),
        'form_idn'    => new sfWidgetFormInputText(),
        'form_code'    => new sfWidgetFormInputText()
    ));

    $this->widgetSchema->setLabels(array(
        'form_type' => "Type of Form",
        'form_stage' => "Submission Stage",
        'form_idn' => "Submission Identification",
        'form_code' => "Service Code"
    ));

    $this->setValidators(array(
        'form_type' => new sfValidatorString(),
        'form_stage' => new sfValidatorString(),
        'form_idn' => new sfValidatorEmail(),
        'form_code' => new sfValidatorString()
    ));

    $this->widgetSchema->setNameFormat('service_settings[%s]');
  }
}