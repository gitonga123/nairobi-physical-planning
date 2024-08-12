<?php

/**
 * infoSuccess.php template.
 *
 * Displays a dynamically generated application form
 *
 * @package    frontend
 * @subpackage forms
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 use_helper('I18N');
?>
<div class="panel panel-default panel-blog">
    <div class="panel-body">
    <h3 class="blogsingle-title"><?php echo $form->getFormName(); ?></h3>


    <div class="mb20"></div>

    <?php echo html_entity_decode($form->getFormDescription()); ?>

    </div><!-- panel-body -->
    <div class="panel-footer">
    <a class="btn btn-primary waves-effect w-md waves-light" href="/plan/forms/view?id=<?php echo $form->getFormId(); ?>"><?php echo __("Apply Now"); ?></a>
    </div>

</div><!-- panel -->