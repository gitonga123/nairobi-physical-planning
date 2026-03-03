<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('SystemLogPath', 'doctrine');

abstract class BaseSystemLogPath extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('system_log_path');

        $this->hasColumn('id', 'integer', 4, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true,
            'length' => 4,
        ));
        $this->hasColumn('title', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
            'length' => '',
        ));

        $this->hasColumn('path', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
            'length' => '',
        ));

        $this->hasColumn('deleted', 'integer', 4, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'default' => '0',
            'notnull' => false,
            'autoincrement' => false,
            'length' => 4,
        ));

    }

    public function setUp()
    {
        parent::setUp();
    }
}
