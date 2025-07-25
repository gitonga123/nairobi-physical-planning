<?php

/**
 * User table.
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage model
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class PluginsfGuardUserTable extends Doctrine_Table
{
  /**
   * Retrieves a sfGuardUser object by username and is_active flag.
   *
   * @param  string  $username The username
   * @param  boolean $isActive The user's status
   *
   * @return sfGuardUser
   */
  public function retrieveByUsername($username, $isActive = true)
  {
    $query = $this->createQuery('u')
      ->where('u.username = ?', $username)
      ->addWhere('u.is_active = ?', $isActive)
    ;

    return $query->fetchOne();
  }

  /**
   * Retrieves a sfGuardUser object by username or email_address and is_active flag.
   *
   * @param  string  $username The username
   * @param  boolean $isActive The user's status
   *
   * @return sfGuardUser
   */
  public function retrieveByUsernameOrEmailAddress($username, $isActive = true)
  {
    $query = Doctrine_Core::getTable('sfGuardUser')->createQuery('u')
      ->innerJoin('u.Profile p')
      ->where('u.username = ? OR p.email = ?', [$username, $username])
      ->addWhere('u.is_active = ?', $isActive)
    ;

    return $query->fetchOne();
  }

  /**
   * Get choices
   *
   * @return array
   */
  public function getChoices()
  {
    $q = $this->createQuery('u')
      ->select('u.id, u.username')
      ->orderBy('u.username');

    $choices = array();

    foreach ($q->fetchArray() as $type)
    {
      $choices[$type['id']] = trim($type['username']);
    }

    return $choices;
  }
}
