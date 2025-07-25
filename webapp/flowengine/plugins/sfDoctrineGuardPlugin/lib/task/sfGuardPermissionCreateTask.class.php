<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Create a new sfGuard permission.
 *
 * @package    symfony
 * @subpackage task
 * @author     Emanuele Panzeri <thepanz@gmail.com>
 */
class sfGuardPermissionCreateTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The permission name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('description', null, sfCommandOption::PARAMETER_REQUIRED, 'The Permission description', null),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'guard';
    $this->name = 'permission:create';
    $this->briefDescription = 'Creates a Permission';

    $this->detailedDescription = <<<EOF
The [guard:permission:create|INFO] task creates a Permission:

  [./symfony guard:permission:create PermissionName --description="This is a nice Permission description"|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $table = Doctrine_Core::getTable('sfGuardPermission');
    if ($ret = $table->findOneBy('name', $arguments['name'])) {
        $this->logSection('guard', sprintf('Permission "%s" already exists!', $arguments['name']), null, 'ERROR');
        return -1;
    }

    $permission = new sfGuardPermission();

    $permission->setName($arguments['name']);
    $permission->setDescription($options['description']);
    $permission->save();

    $this->logSection('guard', sprintf('Created Permission "%s"', $arguments['name']));
  }
}
