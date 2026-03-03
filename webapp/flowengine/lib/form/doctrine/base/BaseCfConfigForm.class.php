<?php

/**
 * CfConfig form base class.
 *
 * @method CfConfig getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCfConfigForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'strcf_server'             => new sfWidgetFormTextarea(),
      'strsmtp_use_auth'         => new sfWidgetFormTextarea(),
      'strsmtp_server'           => new sfWidgetFormTextarea(),
      'strsmtp_port'             => new sfWidgetFormInputText(),
      'strsmtp_userid'           => new sfWidgetFormTextarea(),
      'strsmtp_pwd'              => new sfWidgetFormTextarea(),
      'strsysreplyaddr'          => new sfWidgetFormTextarea(),
      'strmailaddtextdef'        => new sfWidgetFormTextarea(),
      'strdeflang'               => new sfWidgetFormInputText(),
      'bdetailseperatewindow'    => new sfWidgetFormInputText(),
      'strdefsortcol'            => new sfWidgetFormInputText(),
      'bshowposmail'             => new sfWidgetFormInputText(),
      'bfilter_ar_wordstart'     => new sfWidgetFormInputText(),
      'strcirculation_cols'      => new sfWidgetFormInputText(),
      'ndelay_norm'              => new sfWidgetFormInputText(),
      'ndelay_interm'            => new sfWidgetFormInputText(),
      'ndelay_late'              => new sfWidgetFormInputText(),
      'stremail_format'          => new sfWidgetFormInputText(),
      'stremail_values'          => new sfWidgetFormInputText(),
      'nsubstituteperson_hours'  => new sfWidgetFormInputText(),
      'strsubstituteperson_unit' => new sfWidgetFormTextarea(),
      'nconfigid'                => new sfWidgetFormInputHidden(),
      'strsortdirection'         => new sfWidgetFormTextarea(),
      'strversion'               => new sfWidgetFormTextarea(),
      'nshowrows'                => new sfWidgetFormInputText(),
      'nautoreload'              => new sfWidgetFormInputText(),
      'strurlpassword'           => new sfWidgetFormTextarea(),
      'tslastupdate'             => new sfWidgetFormInputText(),
      'ballowunencryptedrequest' => new sfWidgetFormInputText(),
      'userdefined1_title'       => new sfWidgetFormTextarea(),
      'userdefined2_title'       => new sfWidgetFormTextarea(),
      'strdateformat'            => new sfWidgetFormTextarea(),
      'strmailsendtype'          => new sfWidgetFormTextarea(),
      'strmtapath'               => new sfWidgetFormTextarea(),
      'strslotvisibility'        => new sfWidgetFormInputText(),
      'strsmtpencryption'        => new sfWidgetFormInputText(),
      'bsendworkflowmail'        => new sfWidgetFormInputText(),
      'bsendremindermail'        => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'strcf_server'             => new sfValidatorString(),
      'strsmtp_use_auth'         => new sfValidatorString(),
      'strsmtp_server'           => new sfValidatorString(),
      'strsmtp_port'             => new sfValidatorString(array('max_length' => 8, 'required' => false)),
      'strsmtp_userid'           => new sfValidatorString(),
      'strsmtp_pwd'              => new sfValidatorString(),
      'strsysreplyaddr'          => new sfValidatorString(),
      'strmailaddtextdef'        => new sfValidatorString(),
      'strdeflang'               => new sfValidatorString(array('max_length' => 3, 'required' => false)),
      'bdetailseperatewindow'    => new sfValidatorString(array('max_length' => 5, 'required' => false)),
      'strdefsortcol'            => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'bshowposmail'             => new sfValidatorString(array('max_length' => 5, 'required' => false)),
      'bfilter_ar_wordstart'     => new sfValidatorString(array('max_length' => 5, 'required' => false)),
      'strcirculation_cols'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'ndelay_norm'              => new sfValidatorInteger(array('required' => false)),
      'ndelay_interm'            => new sfValidatorInteger(array('required' => false)),
      'ndelay_late'              => new sfValidatorInteger(array('required' => false)),
      'stremail_format'          => new sfValidatorString(array('max_length' => 8, 'required' => false)),
      'stremail_values'          => new sfValidatorString(array('max_length' => 8, 'required' => false)),
      'nsubstituteperson_hours'  => new sfValidatorInteger(array('required' => false)),
      'strsubstituteperson_unit' => new sfValidatorString(),
      'nconfigid'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('nconfigid')), 'empty_value' => $this->getObject()->get('nconfigid'), 'required' => false)),
      'strsortdirection'         => new sfValidatorString(),
      'strversion'               => new sfValidatorString(),
      'nshowrows'                => new sfValidatorInteger(array('required' => false)),
      'nautoreload'              => new sfValidatorInteger(array('required' => false)),
      'strurlpassword'           => new sfValidatorString(),
      'tslastupdate'             => new sfValidatorInteger(),
      'ballowunencryptedrequest' => new sfValidatorInteger(),
      'userdefined1_title'       => new sfValidatorString(),
      'userdefined2_title'       => new sfValidatorString(),
      'strdateformat'            => new sfValidatorString(),
      'strmailsendtype'          => new sfValidatorString(),
      'strmtapath'               => new sfValidatorString(),
      'strslotvisibility'        => new sfValidatorString(array('max_length' => 100)),
      'strsmtpencryption'        => new sfValidatorString(array('max_length' => 100)),
      'bsendworkflowmail'        => new sfValidatorInteger(),
      'bsendremindermail'        => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('cf_config[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CfConfig';
  }

}
