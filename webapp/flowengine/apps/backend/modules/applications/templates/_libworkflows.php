<?php
	/**
	 * _libcustom.php partial.
	 *
	 * Contains external libraries related to workflow management (Comment sheets, Reviewers and Departments)
	 *
	 * @package    backend
	 * @subpackage applications
	 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
	 */

    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	#require($prefix_folder.'includes/db-core.php');
	#require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');

	#require($prefix_folder.'includes/language.php');
	require($prefix_folder.'includes/common-validator.php');
	#require($prefix_folder.'includes/post-functions.php');
	require($prefix_folder.'includes/filter-functions.php');
	#require($prefix_folder.'includes/entry-functions.php');
	require($prefix_folder.'includes/view-functions.php');
	#require($prefix_folder.'includes/users-functions.php');
	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
?>