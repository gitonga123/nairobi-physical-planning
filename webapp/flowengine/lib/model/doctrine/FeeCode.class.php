<?php

class FeeCode extends BaseFeeCode
{
	public function __toString()
	{
		return $this->service_id .' - '.$this->service_name;
	}
    public function save(Doctrine_Connection $conn = null)
    {
        parent::save($conn);
    }
}
