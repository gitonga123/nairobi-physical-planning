<?php
use_helper("I18N");
?>
<div class="pageheader">
   <h2><i class="fa fa-home"></i> <?php echo __("Application"); ?> <span><?php echo __("Decline"); ?></span></h2>
   <div class="breadcrumb-wrapper">
     <span class="label"><?php echo __("You are here"); ?>:</span>
     <ol class="breadcrumb">
       <li><a href="<?php echo public_path('plan/dashboard') ?>"><?php echo __("Home"); ?></a></li>
       <li class="active"><?php echo __("Applications"); ?></li>
     </ol>
   </div>
 </div>

<div class="contentpanel">
    <div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title"><?php echo __("Decline Application"); ?></h3>
        <p><?php echo __("Send back for corrections"); ?></p>
        <div class="pull-right">

        </div>
    </div>
    <div class="panel-body padding-0">
        <div class="col-md-12">

        <form action="/backend.php/forms/confirmdecline" method="POST"  autocomplete="off" data-ajax="false">
            <fieldset>
            <input type="hidden" name="id" value="<?php echo $application->getId(); ?>">
            <?php if($moveto){ ?>
            <input type="hidden" name="moveto" value="<?php echo $moveto; ?>">
            <?php } ?>
                <label>
                <h4><?php echo __("Reason"); ?>:</h4>
                </label>
                <div>
                <textarea class="form-control" name="reason" cols="50" rows="10" required></textarea>
                </div>
            </fieldset>
            <fieldset>
            <fieldset>
                <label>
                <h4><?php echo __("Select the fields you want the client to edit"); ?>:</h4>
                </label>
                <p>
                    <div class="checkbox block"><label><input type="checkbox" name="checkAll" id="checkAll"> Select all </label></div>
                    <hr>
                </p>
                <div style="margin-left: 20px;">
                    <?php
                    $q = Doctrine_Query::create()
                        ->from("ApFormElements a")
                        ->where("a.form_id = ?", $application->getFormId())
                        ->andWhere("a.element_status = ?", 1)
                        ->orderBy("a.element_position ASC");
                    $fields = $q->execute();

                    foreach($fields as $field)
                    {
                        if($field->getElementType() == "section")
                        {
                        ?>
                        <h4><?php echo $field->getElementTitle(); ?></h4>
                        <?php
                        }
                        else {
                        ?>
                        <div class="checkbox block"><label><input type="checkbox" name="edit_fields[]" value="<?php echo $field->getElementId(); ?>"> <?php echo $field->getElementTitle(); ?></label></div>
                        <?php
                        }
                    }
                    ?>
                </div>
            </fieldset>
            <fieldset>
                <section>
                <div>
                    <button id="sendComments" class="btn btn-default mt10" type="submit" class="submit"><?php echo __("Send Comments"); ?></button>
                </div>
                </section>
            </fieldset>
        </form>
    <br>

    </div>
</div>


<script type="text/javascript">
$(document).ready(function () {
    $('#sendComments').click(function() {
    checked = $("input[type=checkbox]:checked").length;

    if(!checked) {
        alert("You must check at least one checkbox.");
        return false;
    }
    });

    $("#checkAll").change(function () {
        $("input:checkbox").prop('checked', $(this).prop("checked"));
    });
});

</script>