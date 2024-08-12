<?php
abstract class BaseAgendaApplications extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('agenda_applications');
        $this->hasColumn('agenda_id', 'integer', 11, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => 11,
             ));
        $this->hasColumn('form_id', 'integer', 11, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 11,
             ));
        $this->hasColumn('entry_id', 'integer', 11, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => 11,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Agenda', array(
             'local' => 'agenda_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));
    }
}