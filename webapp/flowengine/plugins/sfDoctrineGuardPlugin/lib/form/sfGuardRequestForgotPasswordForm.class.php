<?php

/**
 * BasesfGuardRequestForgotPasswordForm for requesting a forgot password email
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage form
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfGuardRequestForgotPasswordForm extends BasesfGuardRequestForgotPasswordForm
{
  /**
   * @see sfForm
   */
  public function configure()
  {
  	$this->widgetSchema->getFormFormatter()->setTranslationCatalogue('sf_guard');
  }
}