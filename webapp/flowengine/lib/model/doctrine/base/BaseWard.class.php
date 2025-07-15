<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Ward', 'doctrine');

abstract class BaseWard extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('wards');

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
        $this->hasColumn('subcounty_id', 'integer', 4, array(
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

        $this->hasOne('Subcounty', array(
            'local' => 'subcounty_id',
            'foreign' => 'id'
        ));
    }
}
