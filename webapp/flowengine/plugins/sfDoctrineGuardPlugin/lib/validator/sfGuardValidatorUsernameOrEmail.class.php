<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Gordon Franke <gfranke@savedcite.com>
 * @version    SVN: $Id$
 */
class sfGuardValidatorUsernameOrEmail extends sfValidatorBase
{
  protected function doClean($value)
  {
    $clean = (string) $value;

    // user exists?
    $user = Doctrine_Core::getTable('sfGuardUser')
      ->retrieveByUsernameOrEmailAddress($clean);
    if ($user)
    {
      return $value;
    }

    throw new sfValidatorError($this, 'invalid', array('value' => $value));
  }
}
