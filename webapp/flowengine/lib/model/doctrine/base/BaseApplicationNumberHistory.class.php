<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ApplicationNumberHistory', 'doctrine');


abstract class BaseApplicationNumberHistory extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('application_number_history');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'autoincrement' => true,
             'primary' => true,
             ));
        $this->hasColumn('form_entry_id', 'integer', 11, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => 11,
             ));
        $this->hasColumn('application_number', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => 255,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }

}