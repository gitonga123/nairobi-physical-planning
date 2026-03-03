<?php
class FeeRange extends BaseFeeRange
{
    public function save(Doctrine_Connection $conn = null)
    {
		if(!$this->getRange_1()){
			$this->setRange_1(0);
		}
		if(!$this->getRange_2()){
			$this->setRange_2(0);
		}
		if(!$this->getConditionField()){
			$this->setConditionField(0);
		}
		if(!$this->getCreatedBy()){
			$this->setCreatedBy(sfContext::getInstance()->getUser()->getAttribute('userid'));
		}
        parent::save($conn);
    }
}