<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Create a new user.
 *
 * @package    symfony
 * @subpackage task
 * @author     Emanuele Panzeri <thepanz@gmail.com>
 */
class sfGuardGroupCreateTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The group name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('description', null, sfCommandOption::PARAMETER_REQUIRED, 'The Group description', null),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'guard';
    $this->name = 'group:create';
    $this->briefDescription = 'Creates a Group';

    $this->detailedDescription = <<<EOF
The [guard:group:create|INFO] task creates a group:

  [./symfony guard:group:create GroupName --description="This is a nice Group description"|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $table = Doctrine_Core::getTable('sfGuardGroup');
    if ($ret = $table->findOneBy('name', $arguments['name'])) {
        $this->logSection('guard', sprintf('Group "%s" already exists!', $arguments['name']), null, 'ERROR');
        return -1;
    }

    $group = new sfGuardGroup();
    $group->setName($arguments['name']);
    $group->setDescription($options['description']);
    $group->save();

    $this->logSection('guard', sprintf('Created group "%s"', $arguments['name']));
  }
}
