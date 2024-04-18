<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ApiContent', 'doctrine');

abstract class BaseApiContent extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('api_content');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => 4,
             ));
        $this->hasColumn('form_id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => false,
             'length' => 4,
             ));
        $this->hasColumn('api_use', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => 255,
             ));
        $this->hasColumn('content', 'string', null, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('merchant_id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => false,
             'length' => 4,
             ));
        $this->hasColumn('request_url', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
			 'length' => 255,
             ));
          $this->hasColumn('api_use_diff', 'string', 255, array(
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
        
		$this->hasOne('ApForms', array(
			'local' => 'form_id',
			'foreign' => 'form_id'
		));
		$this->hasOne('Merchant', array(
			'local' => 'merchant_id',
			'foreign' => 'id'
		));
    }
}