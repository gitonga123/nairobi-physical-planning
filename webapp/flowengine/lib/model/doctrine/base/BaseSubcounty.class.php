<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Subcounty', 'doctrine');

abstract class BaseSubcounty extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('subcounties');

        $this->hasColumn('id', 'integer', 4, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true,
            'length' => 4,
        ));
        $this->hasColumn('name', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
            'length' => '',
        ));
        $this->hasColumn('uuid', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
            'length' => '',
        ));

    }

    public function setUp()
    {
        parent::setUp();

        $this->hasMany('Ward', array(
            'local' => 'id',
            'foreign' => 'subcounty_id'
        ));
    }
}
