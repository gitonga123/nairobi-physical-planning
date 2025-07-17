<?php
class WorkflowCategoryTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object WorkflowCategoryTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('WorkflowCategory');
    }
}