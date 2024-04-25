<?php
abstract class BaseAgencyDepartment extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agency_department');
        $this->hasColumn('department_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('agency_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));

        $this->option('symfony', array(
             'form' => false,
             'filter' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Department as Department', array(
             'local' => 'department_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('Agency as Agency', array(
             'local' => 'agency_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));
    }
}