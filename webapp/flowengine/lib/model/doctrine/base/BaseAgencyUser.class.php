<?php
abstract class BaseAgencyUser extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agency_user');
        $this->hasColumn('user_id', 'integer', null, array(
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
        $this->hasOne('CfUser as User', array(
             'local' => 'user_id',
             'foreign' => 'nid',
             'onDelete' => 'CASCADE'));

        $this->hasOne('Agency as Agency', array(
             'local' => 'agency_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));
    }
}