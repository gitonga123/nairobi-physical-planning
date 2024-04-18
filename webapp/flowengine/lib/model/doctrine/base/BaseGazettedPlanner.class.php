<?php

// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('GazettedPlanner', 'doctrine');

/**
 * BaseGazettedPlanner
 * 
 * @property integer $id
 * @property string  $registration_no
 * @property string  $full_name
 * @property string  $postal_address
 * @property string  $email
 * @property string  $qualification
 * @property string  $business_name
 * 
 * @method integer  getId()              Returns the current record's "id" value
 * @method string   getRegistrationNo()  Returns the current record's "registration_no" value
 * @method string   getFullName()        Returns the current record's "full_name" value
 * @method string   getPostalAddress()   Returns the current record's "postal_address" value
 * @method string   getEmail()           Returns the current record's "email" value
 * @method string   getQualification()   Returns the current record's "qualification" value
 * @method string   getBusinessName()    Returns the current record's "business_name" value
 * @method GazettedPlanner setId()              Sets the current record's "id" value
 * @method GazettedPlanner setRegistrationNo()  Sets the current record's "registration_no" value
 * @method GazettedPlanner setFullName()        Sets the current record's "full_name" value
 * @method GazettedPlanner setPostalAddress()   Sets the current record's "postal_address" value
 * @method GazettedPlanner setEmail()           Sets the current record's "email" value
 * @method GazettedPlanner setQualification()   Sets the current record's "qualification" value
 * @method GazettedPlanner setBusinessName()    Sets the current record's "business_name" value
 * 
 * @package    permit
 * @subpackage model
 */
abstract class BaseGazettedPlanner extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('activity');
        $this->hasColumn('id', 'integer', 11, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true,
            'length' => 11
        ));
        $this->hasColumn('registration_no', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'length' => 10
        ));
        $this->hasColumn('full_name', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'length' => 255
        ));
        $this->hasColumn('postal_address', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'length' => 255
        ));
        $this->hasColumn('email', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'length' => 255
        ));
        $this->hasColumn('qualification', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'length' => 255
        ));
        $this->hasColumn('business_name', 'string', null, array(
            'type' => 'string',
            'fixed' => 0,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'length' => 255
        ));
    }

    public function setUp()
    {
        parent::setUp();
    }
}