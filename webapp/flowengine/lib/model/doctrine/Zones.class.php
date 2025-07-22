<?php

class Zones extends BaseZones
{
	public function __toString()
	{
		return $this->name;
	}
    public function save(Doctrine_Connection $conn = null)
    {
        parent::save($conn);
    }
}
