<?php use_helper("I18N"); ?>
<div class="pageheader">
    <h2><i class="fa fa-home"></i> <?php echo __("Agenda"); ?></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __("You are here"); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="<?php echo public_path("backend.php"); ?>"><?php echo __("Home"); ?></a></li>
            <li class="active"><?php echo __("Edit Agenda"); ?></li>
        </ol>
    </div>
</div>

<?php include_partial('form', array('form' => $form)) ?>
