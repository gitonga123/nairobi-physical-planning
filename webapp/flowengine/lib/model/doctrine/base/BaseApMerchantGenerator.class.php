<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ApMerchantGenerator', 'doctrine');

abstract class BaseApMerchantGenerator extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('ap_merchant_generator');
        $this->hasColumn('form_id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => false,
             'length' => 4,
             ));
        $this->hasColumn('merchant_identifier', 'string', 255, array(
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
    }
}