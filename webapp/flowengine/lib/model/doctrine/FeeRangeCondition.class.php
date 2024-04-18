<?php
class FeeRangeCondition extends BaseFeeRangeCondition
{
    public function save(Doctrine_Connection $conn = null)
    {
		if(!$this->getCreatedBy()){
			$this->setCreatedBy(sfContext::getInstance()->getUser()->getAttribute('userid'));
		}
        parent::save($conn);
    }
}