<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Agency', 'doctrine');

abstract class BaseAgency extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agency');
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
             'notnull' => false,
             'autoincrement' => false,
             'length' => '',
             ));
        $this->hasColumn('address', 'string', null, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'default' => '',
             'length' => '',
             ));
        $this->hasColumn('logo', 'string', null, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '',
             ));
        $this->hasColumn('tag_line', 'string', null, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '',
             ));
        $this->hasColumn('parent_agency', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 4,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Agency as Agency', array(
             'local' => 'parent_agency',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasMany('CfUser as Users', array(
             'refClass' => 'AgencyUser',
             'local' => 'agency_id',
             'foreign' => 'user_id'));

        $this->hasMany('Menus as Workflows', array(
             'refClass' => 'AgencyMenu',
             'local' => 'agency_id',
             'foreign' => 'menu_id'));

        $this->hasMany('Department as Departments', array(
             'refClass' => 'AgencyDepartment',
             'local' => 'agency_id',
             'foreign' => 'department_id'));
    }
}