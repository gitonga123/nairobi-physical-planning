<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Removes a sfGuard group to a user.
 *
 * @package    symfony
 * @subpackage task
 * @author     Emanuele Panzeri <thepanz@gmail.com>
 */
class sfGuardUserGroupRemoveTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('username', sfCommandArgument::REQUIRED, 'The user name'),
      new sfCommandArgument('group', sfCommandArgument::REQUIRED, 'The group name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'guard';
    $this->name = 'user:group-remove';
    $this->briefDescription = 'Removes a group from a user';

    $this->detailedDescription = <<<EOF
The [guard:user:group-remove|INFO] task removes a group to a user:

  [./symfony guard:user:group-remove fabien admin-group|INFO]

The user and the group must exist in the database.
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

    $user->removeUserGroupByGroupName($arguments['group']);

    $this->logSection('guard', sprintf('Remove group %s from to user %s', $arguments['group'], $arguments['username']));
  }
}
