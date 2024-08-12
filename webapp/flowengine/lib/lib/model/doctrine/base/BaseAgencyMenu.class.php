<?php
abstract class BaseAgencyMenu extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agency_menu');
        $this->hasColumn('menu_id', 'integer', null, array(
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
        $this->hasOne('Menus as Workflow', array(
             'local' => 'menu_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('Agency as Agency', array(
             'local' => 'agency_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));
    }
}