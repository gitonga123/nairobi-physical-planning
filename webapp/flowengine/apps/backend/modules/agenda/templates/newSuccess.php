<?php use_helper("I18N"); ?>
<div class="pageheader">
    <h2><i class="fa fa-home"></i> <?php echo __("Agenda"); ?></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __("You are here"); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="<?php echo public_path("plan"); ?>"><?php echo __("Home"); ?></a></li>
            <li class="active"><?php echo __("Agenda"); ?></li>
        </ol>
    </div>
</div>
  <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>

<?php include_partial('form', array('form' => $form)) ?>
