<?php

/**
 * _latest_applications template.
 *
 * Displays a list of the latest applications
 *
 * @package    frontend
 * @subpackage lastest_applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper("I18N");
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __("Recent Services"); ?></h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-hover table-special table-striped" id="apps_lastest">
                <thead>
                    <th><?php echo __("#"); ?></th>
                    <th><?php echo __("Form name"); ?></th>
                    <th><?php echo __("Application id"); ?></th>
                    <th><?php echo __("Stage"); ?></th>
                    <th><?php echo __("Date Submitted"); ?></th>
                    <th><?php echo __("Action"); ?></th>
                </thead>
                <tbody>
                    <?php foreach($latest_applications as $application): ?>
                        <tr>
                            <td><?php echo $application->getId() ?></td>
                            <td><?php echo $application->getForm()->getFormName() ?></td>
                            <td><?php echo $application->getApplicationId() ?></td>
                            <td>
                            <?php if($application->getStage()): ?>
                                <?php echo $application->getStage()->getTitle() ?>
                            <?php endif; ?>
                            </td>
                            <td><?php echo date('jS M Y H:i:s',strtotime($application->getDateOfSubmission())) ?></td>
                            <td><a title="<?php echo __('View Application') ?>" href="/index.php/application/view/id/<?php echo $application->getId() ?>"><span class="badge badge-primary"><i class="fa fa-eye"></i></span></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
       </div>
    </div>
</div>
