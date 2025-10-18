<?php

class AgencyManager
{

	public function __construct()
	{
	}

	public function getAllowedServices($user_id)
	{
		$q = Doctrine_Query::create()
			->from('AgencyUser a')
			->where('a.user_id = ?', $user_id);
		$allowed_useragencies = $q->execute();

		$allowed_user_agency_ids = array(0);
		foreach ($allowed_useragencies as $user_agency) {
			array_push($allowed_user_agency_ids, $user_agency->getAgencyId());
		}

		$q = Doctrine_Query::create()
			->from('AgencyMenu a')
			->whereIn('a.agency_id', $allowed_user_agency_ids);
		$user_service_agencies = $q->execute();

		$allowed_agency_services = array(0);
		foreach ($user_service_agencies as $service_agency) {
			array_push($allowed_agency_services, $service_agency->getMenuId());
		}

		return $allowed_agency_services;
	}

	public function getAllowedStages($user_id)
	{
		$q = Doctrine_Query::create()
			->from('SubMenus a')
			->where('a.deleted = 0');
		$stages = $q->execute();

		$allowed_user_stage_ids = array(0);
		foreach ($stages as $stage) {
			if ($this->checkAgencyStageAccess($user_id, $stage->getId())) {
				array_push($allowed_user_stage_ids, $stage->getId());
			}
		}

		return $allowed_user_stage_ids;
	}

	public function checkAgencyServiceAccess($user_id, $service_id)
	{
		$q = Doctrine_Query::create()
			->from('AgencyUser a')
			->where('a.user_id = ?', $user_id);
		error_log('-------User-----' . $user_id);
		$allowed_useragencies = $q->execute();

		$allowed_user_agency_ids = array(0);
		foreach ($allowed_useragencies as $user_agency) {
			array_push($allowed_user_agency_ids, $user_agency->getAgencyId());
		}

		$q = Doctrine_Query::create()
			->from('AgencyMenu a')
			->whereIn('a.agency_id', $allowed_user_agency_ids);
		$user_service_agencies = $q->execute();

		$allowed_agency_services = array(0);
		foreach ($user_service_agencies as $service_agency) {
			array_push($allowed_agency_services, $service_agency->getMenuId());
		}

		error_log("Service id found is ---->");
		error_log(print_r($allowed_agency_services));
		error_log("Service id is ----> {$service_id}");

		if (in_array($service_id, $allowed_agency_services)) {
			return true;
		} else {
			return false;
		}
	}

	public function getAgencyDepartments($user_id)
	{
		$q = Doctrine_Query::create()
			->from('AgencyUser a')
			->where('a.user_id = ?', $user_id);
		$useragencies = $q->execute();

		$user_agency_ids = array(0);
		foreach ($useragencies as $user_agency) {
			array_push($user_agency_ids, $user_agency->getAgencyId());
		}

		$q = Doctrine_Query::create()
			->from('AgencyDepartment a')
			->whereIn('a.agency_id', $user_agency_ids);
		$department_agencies = $q->execute();

		$allowed_agency_departments = array(0);
		foreach ($department_agencies as $department_agency) {
			array_push($allowed_agency_departments, $department_agency->getDepartmentId());
		}

		return $allowed_agency_departments;
	}
	public function checkAgencyDepartmentAccess($user_id, $department_id)
	{
		$q = Doctrine_Query::create()
			->from('AgencyUser a')
			->where('a.user_id = ?', $user_id);
		$useragencies = $q->execute();

		$user_agency_ids = array(0);
		foreach ($useragencies as $user_agency) {
			array_push($user_agency_ids, $user_agency->getAgencyId());
		}

		$q = Doctrine_Query::create()
			->from('AgencyDepartment a')
			->whereIn('a.agency_id', $user_agency_ids);
		$department_agencies = $q->execute();

		$allowed_agency_departments = array(0);
		foreach ($department_agencies as $department_agency) {
			array_push($allowed_agency_departments, $department_agency->getDepartmentId());
		}

		if (in_array($department_id, $allowed_agency_departments)) {
			return true;
		} else {
			return false;
		}
	}

	public function checkAgencyStageAccess($user_id, $stage_id)
	{
		error_log("line 130: -----> {$stage_id} -------------> user -id ----> {$user_id}");
		$q = Doctrine_Query::create()
			->from('SubMenus a')
			->where('a.id = ?', $stage_id)
			->limit(1);
		$stage = $q->fetchOne();

		if ($stage) {
			$q = Doctrine_Query::create()
				->from('Menus a')
				->where('a.id = ?', $stage->getMenuId())
				->limit(1);
			$service = $q->fetchOne();

			error_log("line 144: -----> {$stage->getMenuId()} -------------> user -id ----> {$user_id}");
			if ($service) {
				error_log("line 146: -----> {$service->getId()} -------------> user -id ----> {$user_id}");
				return $this->checkAgencyServiceAccess($user_id, $service->getId());
			}
		}
		return false;
	}

	public function checkAgencyApplicationAccess($user_id, $application_id)
	{
		$q = Doctrine_Query::create()
			->from('FormEntry a')
			->where('a.id = ?', $application_id)
			->limit(1);
		$application = $q->fetchOne();
		error_log('------Appid----' . $application_id . '--------App-----' . print_r($application, true));
		if ($application) {
			$q = Doctrine_Query::create()
				->from('SubMenus a')
				->where('a.id = ?', $application->getApproved())
				->limit(1);
			$stage = $q->fetchOne();

			if ($stage) {
				$q = Doctrine_Query::create()
					->from('Menus a')
					->where('a.id = ?', $stage->getMenuId())
					->limit(1);
				$service = $q->fetchOne();
				error_log("line 185: -----> {$stage->getMenuId()} -------------> user -id ----> {$user_id}");

				if ($service) {
					error_log("line 188: -----> {$service->getId()} -------------> user -id ----> {$user_id}");
					return $this->checkAgencyServiceAccess($user_id, $service->getId());
				}
			}
		}
		return false;
	}

	public function getAgencyServices($agency_id)
	{
		$q = Doctrine_Query::create()
			->from('AgencyMenu a')
			->where('a.agency_id = ?', $agency_id);
		$service_agencies = $q->execute();
		$service_agency_ids = array(0);
		foreach ($service_agencies as $service_agency) {
			array_push($service_agency_ids, $service_agency->getAgencyId());
		}

		return $service_agency_ids;
	}

	public function getLogo($user_id)
	{
		$siteconfig = Doctrine_Core::getTable('ApSettings')->find(array(1));
		$q = Doctrine_Query::create()
			->from('AgencyUser a')
			->where('a.user_id = ?', $user_id);
		$useragencies = $q->execute();

		$user_agency_ids = array(0);
		foreach ($useragencies as $user_agency) {
			array_push($user_agency_ids, $user_agency->getAgencyId());
		}

		$q = Doctrine_Query::create()
			->from('Agency a')
			->whereIn('a.id', $user_agency_ids);
		$agencies = $q->execute();

		$logo = "none";
		$parent_agency_id = False;
		foreach ($agencies as $agency) {
			if ($agency->getParentAgency()) {
				$parent_agency_id = $agency->getParentAgency();
			}
			$logo = $agency->getLogo() ? $agency->getLogo() : $logo;
		}

		if ($parent_agency_id && sizeof($agencies) > 1) {
			$q = Doctrine_Query::create()
				->from('Agency a')
				->where('a.id = ?', $parent_agency_id);
			$parent_agency = $q->fetchone();
			$parent_logo = $parent_agency->getLogo();
			return "/" . $siteconfig->getUploadDir() . "/" . $parent_logo;
		} else {
			return "/" . $siteconfig->getUploadDir() . "/" . $logo;
		}
	}

	public function getAllowedAgencyApplicationsFromQuery($q, $user_id)
	{

		$applications = $q->execute();
		$allowedApplicationIds = array(0);

		foreach ($applications as $application) {
			$q = Doctrine_Query::create()
				->from('SubMenus a')
				->where('a.id = ?', $application->getApproved())
				->limit(1);
			$stage = $q->fetchOne();

			if ($stage) {
				$q = Doctrine_Query::create()
					->from('Menus a')
					->where('a.id = ?', $stage->getMenuId())
					->limit(1);
				$service = $q->fetchOne();

				if ($service) {
					if ($this->checkAgencyServiceAccess($user_id, $service->getId())) {
						array_push($allowedApplicationIds, $application->getId());
					}
				}
			}
		}
		return $allowedApplicationIds;
	}

	public function checkAgencyPaymentAccess($payment, $user_id)
	{
		$q = Doctrine_Query::create()
			->from('FormEntry a')
			->where('a.form_id = ? and a.entry_id = ?', array($payment->getFormId(), $payment->getRecordId()))
			->limit(1);
		$application = $q->fetchOne();

		$q = Doctrine_Query::create()
			->from('SubMenus a')
			->where('a.id = ?', $application->getApproved())
			->limit(1);
		$stage = $q->fetchOne();

		if ($stage) {
			$q = Doctrine_Query::create()
				->from('Menus a')
				->where('a.id = ?', $stage->getMenuId())
				->limit(1);
			$service = $q->fetchOne();

			if ($service) {
				return $this->checkAgencyServiceAccess($user_id, $service->getId());
			}
		}

		return false;
	}
}
?>