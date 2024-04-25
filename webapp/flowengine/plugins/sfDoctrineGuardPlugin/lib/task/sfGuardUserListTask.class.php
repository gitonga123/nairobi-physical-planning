<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * List the sfGuard users.
 *
 * @package    symfony
 * @subpackage task
 * @author     Emanuele Panzeri <thepanz@gmail.com>
 */
class sfGuardUserListTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array());

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'guard';
    $this->name = 'user:list';
    $this->briefDescription = 'List users';
  }

  protected function execute($arguments = array(), $options = array())
  {
      $databaseManager = new sfDatabaseManager($this->configuration);

      $model = sfConfig::get('app_sf_guard_user_model', 'sfGuardUser');
      $users = Doctrine_Core::getTable($model)->findAll();
      /** @var sfGuardUser $user */
      foreach ($users as $user) {
          $this->logSection('guard', sprintf('%s (name: "%s", email: "%s")', $user->getUsername(), $user->getName(), $user->getEmailAddress()));
      }
  }
}
