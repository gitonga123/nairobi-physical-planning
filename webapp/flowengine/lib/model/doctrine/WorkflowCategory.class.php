<?php


class WorkflowCategory extends BaseWorkflowCategory
{
    public function __toString()
    {
        return $this->getTitle();
    }
    public function save(Doctrine_Connection $conn = null)
    {
		if(!$this->getOrderId()){
			$this->setOrderId($this->getId());
		}
        parent::save($conn);

    }
}