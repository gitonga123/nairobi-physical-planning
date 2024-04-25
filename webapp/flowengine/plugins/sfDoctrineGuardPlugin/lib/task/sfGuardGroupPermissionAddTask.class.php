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
class sfGuardGroupPermissionAddTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('group', sfCommandArgument::REQUIRED, 'The group name'),
      new sfCommandArgument('permission', sfCommandArgument::REQUIRED, 'The permission name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('description', null, sfCommandOption::PARAMETER_REQUIRED, 'The Group description', null),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'guard';
    $this->name = 'group:permission-add';
    $this->briefDescription = 'Associated a Group with a Permission';

    $this->detailedDescription = <<<EOF
The [guard:group:permission-add|INFO] task add a group with a permission:

  [./symfony guard:group:permission-add GroupName PermissionName|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    /** @var sfGuardGroup $group */
    $group = Doctrine_Core::getTable('sfGuardGroup')->findOneByName($arguments['group']);
    if (!$group) {
        $this->logSection('guard', sprintf('Group "%s" not found!', $arguments['group']), null, 'ERROR');
        return -1;
    }

    /** @var sfGuardPermission $permission */
    $permission = Doctrine_Core::getTable('sfGuardPermission')->findOneByName($arguments['permission']);
    if (!$permission) {
        $this->logSection('guard', sprintf('Permission "%s" not found!', $arguments['permission']), null, 'ERROR');
        return -1;
    }

    /** @var sfGuardGroupPermission $groupPermission */
    $groupPermission = Doctrine_Core::getTable('sfGuardGroupPermission')->find(array(
        $group->getPrimaryKey(),
        $permission->getPrimaryKey(),
    ));
    if ($groupPermission) {
      $this->logSection('guard', 'Group Permission already exists', null, 'ERROR');
      return -1;
    }

    $groupPermission = new sfGuardGroupPermission();
    $groupPermission
        ->setGroup($group)
        ->setPermission($permission);
    $groupPermission->save();

    $this->logSection('guard', sprintf('Associated group "%s" with permission "%s".', $group->getName(), $permission->getName()));
  }
}
