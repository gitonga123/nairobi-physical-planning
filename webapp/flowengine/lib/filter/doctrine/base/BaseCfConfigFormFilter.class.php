<?php

/**
 * CfConfig filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseCfConfigFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'strcf_server'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsmtp_use_auth'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsmtp_server'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsmtp_port'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsmtp_userid'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsmtp_pwd'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsysreplyaddr'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strmailaddtextdef'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strdeflang'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'bdetailseperatewindow'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strdefsortcol'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'bshowposmail'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'bfilter_ar_wordstart'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strcirculation_cols'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'ndelay_norm'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'ndelay_interm'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'ndelay_late'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'stremail_format'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'stremail_values'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'nsubstituteperson_hours'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsubstituteperson_unit' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsortdirection'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strversion'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'nshowrows'                => new sfWidgetFormFilterInput(),
      'nautoreload'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strurlpassword'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'tslastupdate'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'ballowunencryptedrequest' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'userdefined1_title'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'userdefined2_title'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strdateformat'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strmailsendtype'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strmtapath'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strslotvisibility'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'strsmtpencryption'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'bsendworkflowmail'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'bsendremindermail'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'strcf_server'             => new sfValidatorPass(array('required' => false)),
      'strsmtp_use_auth'         => new sfValidatorPass(array('required' => false)),
      'strsmtp_server'           => new sfValidatorPass(array('required' => false)),
      'strsmtp_port'             => new sfValidatorPass(array('required' => false)),
      'strsmtp_userid'           => new sfValidatorPass(array('required' => false)),
      'strsmtp_pwd'              => new sfValidatorPass(array('required' => false)),
      'strsysreplyaddr'          => new sfValidatorPass(array('required' => false)),
      'strmailaddtextdef'        => new sfValidatorPass(array('required' => false)),
      'strdeflang'               => new sfValidatorPass(array('required' => false)),
      'bdetailseperatewindow'    => new sfValidatorPass(array('required' => false)),
      'strdefsortcol'            => new sfValidatorPass(array('required' => false)),
      'bshowposmail'             => new sfValidatorPass(array('required' => false)),
      'bfilter_ar_wordstart'     => new sfValidatorPass(array('required' => false)),
      'strcirculation_cols'      => new sfValidatorPass(array('required' => false)),
      'ndelay_norm'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'ndelay_interm'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'ndelay_late'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'stremail_format'          => new sfValidatorPass(array('required' => false)),
      'stremail_values'          => new sfValidatorPass(array('required' => false)),
      'nsubstituteperson_hours'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'strsubstituteperson_unit' => new sfValidatorPass(array('required' => false)),
      'strsortdirection'         => new sfValidatorPass(array('required' => false)),
      'strversion'               => new sfValidatorPass(array('required' => false)),
      'nshowrows'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'nautoreload'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'strurlpassword'           => new sfValidatorPass(array('required' => false)),
      'tslastupdate'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'ballowunencryptedrequest' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'userdefined1_title'       => new sfValidatorPass(array('required' => false)),
      'userdefined2_title'       => new sfValidatorPass(array('required' => false)),
      'strdateformat'            => new sfValidatorPass(array('required' => false)),
      'strmailsendtype'          => new sfValidatorPass(array('required' => false)),
      'strmtapath'               => new sfValidatorPass(array('required' => false)),
      'strslotvisibility'        => new sfValidatorPass(array('required' => false)),
      'strsmtpencryption'        => new sfValidatorPass(array('required' => false)),
      'bsendworkflowmail'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'bsendremindermail'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('cf_config_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'CfConfig';
  }

  public function getFields()
  {
    return array(
      'strcf_server'             => 'Text',
      'strsmtp_use_auth'         => 'Text',
      'strsmtp_server'           => 'Text',
      'strsmtp_port'             => 'Text',
      'strsmtp_userid'           => 'Text',
      'strsmtp_pwd'              => 'Text',
      'strsysreplyaddr'          => 'Text',
      'strmailaddtextdef'        => 'Text',
      'strdeflang'               => 'Text',
      'bdetailseperatewindow'    => 'Text',
      'strdefsortcol'            => 'Text',
      'bshowposmail'             => 'Text',
      'bfilter_ar_wordstart'     => 'Text',
      'strcirculation_cols'      => 'Text',
      'ndelay_norm'              => 'Number',
      'ndelay_interm'            => 'Number',
      'ndelay_late'              => 'Number',
      'stremail_format'          => 'Text',
      'stremail_values'          => 'Text',
      'nsubstituteperson_hours'  => 'Number',
      'strsubstituteperson_unit' => 'Text',
      'nconfigid'                => 'Number',
      'strsortdirection'         => 'Text',
      'strversion'               => 'Text',
      'nshowrows'                => 'Number',
      'nautoreload'              => 'Number',
      'strurlpassword'           => 'Text',
      'tslastupdate'             => 'Number',
      'ballowunencryptedrequest' => 'Number',
      'userdefined1_title'       => 'Text',
      'userdefined2_title'       => 'Text',
      'strdateformat'            => 'Text',
      'strmailsendtype'          => 'Text',
      'strmtapath'               => 'Text',
      'strslotvisibility'        => 'Text',
      'strsmtpencryption'        => 'Text',
      'bsendworkflowmail'        => 'Number',
      'bsendremindermail'        => 'Number',
    );
  }
}
