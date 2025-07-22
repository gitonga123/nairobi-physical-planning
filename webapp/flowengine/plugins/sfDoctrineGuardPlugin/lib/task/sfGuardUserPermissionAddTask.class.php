<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Add a permission to a user.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGuardUserPermissionAddTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('username', sfCommandArgument::REQUIRED, 'The user name'),
      new sfCommandArgument('permission', sfCommandArgument::REQUIRED, 'The permission name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'guard';
    $this->name = 'user:permission-add';
    $this->briefDescription = 'Adds a permission to a user';

    $this->detailedDescription = <<<EOF
The [guard:user:permission-add|INFO] task adds a permission to a user:

  [./symfony guard:user:permission-add fabien admin|INFO]

The user and the permission must exist in the database.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $model = sfConfig::get('app_sf_guard_user_model', 'sfGuardUser');

    /** @var sfGuardUser $user */
    $user = Doctrine_Core::getTable($model)->findOneByUsername($arguments['username']);
    if (!$user)
    {
      throw new sfCommandException(sprintf('User "%s" does not exist.', $arguments['username']));
    }

    $user->addPermissionByName($arguments['permission']);

    $this->logSection('guard', sprintf('Add permission %s to user %s', $arguments['permission'], $arguments['username']));
  }
}
