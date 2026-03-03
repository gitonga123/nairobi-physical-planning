<?php
    /**
     * _libforms.php partial.
     *
     * Contains external libraries related to form management (Application details and User additional details)
     *
     * @package    backend
     * @subpackage applications
     * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
     */

    $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/cp_machform/";

    require_once($prefix_folder.'config.php');
    require_once($prefix_folder.'includes/db-core.php');
    require_once($prefix_folder.'includes/helper-functions.php');
    require_once($prefix_folder.'includes/check-session.php');

    require_once($prefix_folder.'includes/entry-functions.php');
    require_once($prefix_folder.'includes/post-functions.php');
    require_once($prefix_folder.'includes/users-functions.php');
?>
