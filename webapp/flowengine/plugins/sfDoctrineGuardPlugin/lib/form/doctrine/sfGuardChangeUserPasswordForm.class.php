<?php

/**
 * sfGuardChangeUserPasswordForm for changing a users password
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage form
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfGuardChangeUserPasswordForm extends BasesfGuardChangeUserPasswordForm
{
  /**
   * @see sfForm
   */
  public function configure()
  {
  	$this->widgetSchema->getFormFormatter()->setTranslationCatalogue('sf_guard');
  }
}