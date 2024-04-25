<?php

$application_id = $application->getId();

$conn = Doctrine_Manager::getInstance()->getCurrentConnection();

$current_stage = $application->getCurrentStage();

if (empty($current_stage)) {
    return;
}

if ($current_stage->getStageType() == 4) :
    # find any signable attachments for this application
    if ($files = Functions::files_to_sign_in_form($application)) {

        $files = array_filter(
            $files,
            function ($k) {
                return $k['local_file'] and !file_exists($k['local_file_signed']);
            }
        );

        # only show this if construction permit has been generated
        $form_id = $application->getFormId();
        $should_show = true;
        # does it have a construction permit
        if ($form_id == 9772 and count(Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc(
            "SELECT s.id FROM saved_permit s LEFT JOIN permits p ON p.id = s.type_id  WHERE application_id = $application_id AND p.id = 12"
        )) == 0) {
            $should_show = false;
        }
        error_log('----------should_show---------' . var_export($should_show, true));
        if (count($files) > 0 and $should_show) : ?>
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Please sign the attachments indicated here'); ?></h3>
                </div>

                <div class="panel-body padding-0">
                    <table class="table table-special m-b-0">
                        <thead>
                            <tr>
                                <th><?php echo __("Document"); ?></th>
                                <th><?php echo __("Actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php foreach ($files as $file) : ?>
                                <tr>
                                    <td><?php echo $file['file_name'];
                                        echo '<br/><small>' . $file['local_file'] . "</small>" ?>
                                        <br />
                                        <div class="badge badge-info">
                                            file <?php echo file_exists($file['local_file']) ? 'exists' : 'missing' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!Functions::isDocumentInSigningSession($file['local_file'])) : ?>
                                            <a class="btn btn-info" href="/backend.php/signingsessions/add?document=<?php echo $file['local_file'] ?>&name=<?php echo $file['file_name'] ?>&type=Attachment&application_id=<?php echo $application_id ?>&slug=<?php echo $file['slug'] ?>&redirect_to=/backend.php/applications/view/id/<?php echo $application_id ?>&id=<?php echo str_replace('-', '', $file['slug']) ?>">
                                                <i class="fa fa-plus"></i> Add to Signing List
                                            </a>
                                        <?php else : ?>
                                            <a class="btn btn-danger" href="/backend.php/signingsessions/remove?document=<?php echo $file['local_file'] ?>&redirect_to=/backend.php/applications/view/id/<?php echo $application_id ?>">
                                                <i class="fa fa-minus"></i> Remove from Signing List
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif ?>
<?php }
endif ?>