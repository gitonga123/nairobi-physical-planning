<?php

/**
 * PluginsfGuardUser form.
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class PluginsfGuardUserForm extends BasesfGuardUserForm
{
  public function setup()
  {
    parent::setUp();
    $this->setValidator('username', new sfValidatorRegex(array('pattern' => '/^[a-z0-9-]+$/i'), array('invalid' => 'Only a-z, 0-9 or - character')));
  }
}
