<?php

class mfUserProfilePluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('user.method_not_found', array('BackendUser', 'methodNotFound'));
  }
}