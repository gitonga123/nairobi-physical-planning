<?php

$prefix_folder = dirname(__FILE__) . "/../../../../../lib/vendor/form_builder/";
require_once($prefix_folder . 'includes/init.php');

require_once($prefix_folder . '../../../config/form_builder_config.php');
require_once($prefix_folder . 'includes/db-core.php');
require_once($prefix_folder . 'includes/helper-functions.php');
require_once($prefix_folder . 'includes/check-session.php');

require_once($prefix_folder . 'includes/language.php');
require_once($prefix_folder . 'includes/entry-functions.php');
require_once($prefix_folder . 'includes/post-functions.php');
require_once($prefix_folder . 'includes/users-functions.php');

$dbh = mf_connect_db();
$mf_settings = mf_get_settings($dbh);


//get entry details for particular entry_id
$param['checkbox_image'] = '/form_builder/images/icons/59_blue_16.png';
$entry_details = mf_get_entry_details($dbh, $form_id, $entry_id, $param);

//Print Out Application Details
foreach ($entry_details as $data) {
    if (strlen($data['element_type'] == "section")) {
?>
        <section>
            <label for="text_field" style="font-weight: 900; width: 100%;"><?php echo $data['label']; ?></label>
        </section>
    <?php
    } else {
    ?>
        <section>
            <label for="text_field" style="font-weight: 900;"><?php echo $data['label']; ?></label>
            <div>&nbsp;&nbsp;&nbsp;<?php if ($data['value']) {
                                        echo nl2br($data['value']);
                                    } else {
                                        echo "-";
                                    } ?></div>
            <hr>
        </section>
<?php
    }
}
?>