<?php

/**
 * Permits filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BasePermitsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'title'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'applicationform'     => new sfWidgetFormFilterInput(),
      'applicationstage'    => new sfWidgetFormFilterInput(),
      'parttype'            => new sfWidgetFormFilterInput(),
      'content'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'footer'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'max_duration'        => new sfWidgetFormFilterInput(),
      'remote_url'          => new sfWidgetFormFilterInput(),
      'remote_field'        => new sfWidgetFormFilterInput(),
      'remote_username'     => new sfWidgetFormFilterInput(),
      'remote_password'     => new sfWidgetFormFilterInput(),
      'remote_request_type' => new sfWidgetFormFilterInput(),
      'expiration_type'     => new sfWidgetFormFilterInput(),
      'page_type'           => new sfWidgetFormFilterInput(),
      'page_orientation'    => new sfWidgetFormFilterInput(),
      'qr_content'          => new sfWidgetFormFilterInput(),
      'expiry_type'         => new sfWidgetFormFilterInput(),
      'expiry_trigger'      => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'title'               => new sfValidatorPass(array('required' => false)),
      'applicationform'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'applicationstage'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'parttype'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'content'             => new sfValidatorPass(array('required' => false)),
      'footer'              => new sfValidatorPass(array('required' => false)),
      'max_duration'        => new sfValidatorPass(array('required' => false)),
      'remote_url'          => new sfValidatorPass(array('required' => false)),
      'remote_field'        => new sfValidatorPass(array('required' => false)),
      'remote_username'     => new sfValidatorPass(array('required' => false)),
      'remote_password'     => new sfValidatorPass(array('required' => false)),
      'remote_request_type' => new sfValidatorPass(array('required' => false)),
      'expiration_type'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'page_type'           => new sfValidatorPass(array('required' => false)),
      'page_orientation'    => new sfValidatorPass(array('required' => false)),
      'qr_content'          => new sfValidatorPass(array('required' => false)),
      'expiry_type'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'expiry_trigger'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('permits_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Permits';
  }

  public function getFields()
  {
    return array(
      'id'                  => 'Number',
      'title'               => 'Text',
      'applicationform'     => 'Number',
      'applicationstage'    => 'Number',
      'parttype'            => 'Number',
      'content'             => 'Text',
      'footer'              => 'Text',
      'max_duration'        => 'Text',
      'remote_url'          => 'Text',
      'remote_field'        => 'Text',
      'remote_username'     => 'Text',
      'remote_password'     => 'Text',
      'remote_request_type' => 'Text',
      'expiration_type'     => 'Number',
      'page_type'           => 'Text',
      'page_orientation'    => 'Text',
      'qr_content'          => 'Text',
      'expiry_type'         => 'Number',
      'expiry_trigger'      => 'Number',
    );
  }
}
