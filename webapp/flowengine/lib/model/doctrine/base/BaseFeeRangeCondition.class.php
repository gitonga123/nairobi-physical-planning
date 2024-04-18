<?php 

	abstract class BaseFeeRangeCondition extends sfDoctrineRecord
	{
		public function setTableDefinition()
		{
			$this->setTableName('fee_range_condition'); 
			$this->hasColumn('id', 'integer', 11, array(
				 'type' => 'integer',
				 'primary' => true,
				 'autoincrement' => true,
				 'length' => 11,
				 ));
        $this->hasColumn('fee_range_id', 'integer', null, array(
             'type' => 'integer',
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
			$this->hasColumn('created_by', 'integer', 11, array(
				 'type' => 'integer',
				 'notnull' => true,
				 'length' => 11,
				 ));
		}

		public function setUp()
		{
			parent::setUp();
			$this->hasOne('FeeRange as FeeRange', array(
				 'local' => 'fee_range_id',
				 'foreign' => 'id',
				 'onDelete' => 'CASCADE'));
		}
	}
