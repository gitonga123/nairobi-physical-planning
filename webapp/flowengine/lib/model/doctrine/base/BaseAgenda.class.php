<?php
abstract class BaseAgenda extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agenda');
        $this->hasColumn('agenda_date', 'timestamp', null, array(
             'type' => 'timestamp',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '',
             ));
        $this->hasColumn('subject', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 255,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('AgendaApplications', array(
             'local' => 'id',
             'foreign' => 'agenda_id'));
    }
}