<?php
/**
 * indexSuccess.php template.
 *
 * Displays a summary of all application/reviewer related information for the reviewers
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 use_helper('I18N');

if($first_run)
{
    $invoice_form = new InvoicetemplatesForm();
    $permit_form = new permitsForm();

    include_partial('dashboard_wizard', 
        array(
            'wizard_manager' => $wizard_manager,
            'invoice_form' => $invoice_form,
            'permit_form' => $permit_form,
            'departments' => $departments,
            'groups' => $groups,
            'permissions' => $permissions,
            'workflow' => $workflow
        )
    );
}
else
{
    include_partial('dashboard_home',
        array(
            'my_tasks' => $my_tasks_stats,
            'completed_tasks' => $completed_tasks_stats,
            'new_messages' => $new_messages_stats,
            'current_paginator' => $current_paginator,
            'current_tab' => $current_tab,
            'page' => $page,
            'filter' => $filter,
            'user' => $logged_user,
            'applicants' => $total_count ,
            'signing_tasks' => count(json_decode(Functions::lastSigningSession()['documents']))
        )
    );
}
?>
