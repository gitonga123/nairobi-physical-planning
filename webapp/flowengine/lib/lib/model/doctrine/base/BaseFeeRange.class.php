<?php 

	abstract class BaseFeeRange extends sfDoctrineRecord
	{
		public function setTableDefinition()
		{
			$this->setTableName('fee_range'); 
			$this->hasColumn('id', 'integer', 11, array(
				 'type' => 'integer',
				 'primary' => true,
				 'autoincrement' => true,
				 'length' => 11,
				 ));
        $this->hasColumn('fee_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('name', 'string', 100, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => 100,
             ));
			$this->hasColumn('range_1', 'string', 100, array(
				 'type' => 'string',
				 'notnull' => true,
				 'length' => 100,
				 )); 
			$this->hasColumn('range_2', 'string', 100, array(
				 'type' => 'string',
				 'notnull' => true,
				 'length' => 100,
				 ));
        $this->hasColumn('result_value', 'string', 65536, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 65536,
             ));
        $this->hasColumn('condition_field', 'integer', 11, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 11,
             ));
        $this->hasColumn('condition_operator', 'integer', 11, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 11,
             ));
        $this->hasColumn('condition_value', 'string', 65536, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 65536,
             ));
        $this->hasColumn('condition_set_operator', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => 50,
             ));
			$this->hasColumn('created_by', 'integer', 11, array(
				 'type' => 'integer',
				 'notnull' => true,
				 'length' => 11,
				 ));
        $this->hasColumn('value_type', 'string', 100, array(
             'type' => 'string',
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 100,
             ));
		}

		public function setUp()
		{
			parent::setUp();
			$this->hasOne('Fee as Fee', array(
				 'local' => 'fee_id',
				 'foreign' => 'id',
				 'onDelete' => 'CASCADE'));
		}
	}
