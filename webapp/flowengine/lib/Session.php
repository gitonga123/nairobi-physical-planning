<?php

#namespace Lib\IFC;

/**
 * This class is used to initialise and manage the application session
 * 
 * @version   1.0.0
 * @author    Ken Gichia
 */
class Session {
    private $sf_user;

    /**
     * Called to destroy a given session
     */
    public function __construct($sf_user) {
        $this->sf_user = $sf_user;
    }

    /**
     * Use this to get a value from the session 
     * 
     * @return  mixed
     */
    public function __get($key) {
        return $this->sf_user->getAttribute($key, null);
    }

    /**
     * Set a value in the session variable
     * 
     * @param   string
     * @param   mixed
     */
    public function __set($key, $value) {
        $this->sf_user->setAttribute($key, $value);
    }
}