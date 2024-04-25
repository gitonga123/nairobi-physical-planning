<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Associates a sfGuard group with a permission.
 *
 * @package    symfony
 * @subpackage task
 * @author     Emanuele Panzeri <thepanz@gmail.com>
 */
class sfGuardGroupPermissionListTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('group', sfCommandArgument::REQUIRED, 'The group name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'guard';
    $this->name = 'group:permission-list';
    $this->briefDescription = 'List the group permissions';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    /** @var sfGuardGroup $group */
    $group = Doctrine_Core::getTable('sfGuardGroup')->findOneByName($arguments['group']);
    if (!$group) {
        $this->logSection('guard', sprintf('Group "%s" not found!', $arguments['group']), null, 'ERROR');
        return -1;
    }

    $this->logSection('guard', sprintf('Listing permissions for Group "%s"', $group->getName()));

    /** @var sfGuardPermission $permission */
    foreach ($group->getPermissions() as $permission) {
        $this->logSection('guard', sprintf(' - %s', $permission->getName()));
    }
  }
}
