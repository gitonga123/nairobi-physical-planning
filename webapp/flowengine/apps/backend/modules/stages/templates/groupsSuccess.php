<?php
  use_helper("I18N");
?>
<div class="contentpanel">

    <form id="stageform" class="form-bordered" action="/backend.php/stages/groups/id/<?php echo $stage->getId(); ?>" method="post" autocomplete="off" data-ajax="false">
      <div class="panel panel-default">

      <div class="panel-heading">
        <h3 class="panel-title"><?php echo $stage->getTitle().' Stage'; ?></h3>
        <?php echo __('Group access'); ?>
      </div>
      <div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
        <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
        <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this stage'); ?></a>.
      </div>
      <div class="panel-body padding-0">
        <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Group access'); ?></i></label>
        <div class="col-sm-8">
          <select name='allowed_groups[]' id='allowed_groups' multiple>
            <?php
              $selected = "";
              $q = Doctrine_Query::create()
                 ->from("MfGuardGroup a")
                 ->orderBy("a.name ASC");
              $groups = $q->execute();
              foreach($groups as $group)
              {
                $selected = "";
                $grouppermissions = $group->getPermissions();
                foreach($grouppermissions as $grouppermission)
                {
                    $q = Doctrine_Query::create()
                       ->from("MfGuardPermission a")
                       ->where("a.name = ?", "accesssubmenu".$stage->getId());
                    $permission = $q->fetchOne();
                    if($permission) {
                      if ($permission->getId() == $grouppermission->getId()) {
                        $selected = "selected";
                      }
                    }
                }
                echo "<option value='".$group->getId()."' ".$selected.">".$group->getName()."</option>";
              }
            ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-4"><i class="bold-label"><?php echo __('Add a new group'); ?></i></label>
        <div class="col-sm-8">
          <div id="newgroup" name="newgroup">
          </div>
          <div class="col-sm-12" style="margin-top: 10px;" align="right">
          <button type="button" class="btn btn-primary" id="addnewgroup" name="addnewgroup"><?php echo __('+ Add Group'); ?></button>
          <br><br>
          </div>
          <script>
            jQuery(document).ready(function(){

              $( "#addnewgroup" ).click(function() {
                  $("#newgroup").append("<div class='form-group' class='formgroup'><label class='col-sm-4'><?php echo __('Group'); ?></label><div class='col-sm-8'><input type='text' name='name[]' class='form-control' placeholder='<?php echo __('Group Name'); ?>'></div><a style='float: right; margin-top: 10px;' href='#' class='panel-close' onClick='$(this).closest(\"div\").remove();'>&times;</a></div>");
              });

            });
            </script>
        </div>
        </div>
      </div>
      <div class="panel-footer">
           <a class="btn btn-danger mr10" href="/backend.php/stages/index/filter/<?php echo $stage->getMenuId(); ?>"><?php echo __('Back to Workflow'); ?></a><button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
     </div>
   </div>

    </form>
</div>

<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script>
jQuery(document).ready(function(){
  var groups = $('[id="allowed_groups"]').bootstrapDualListbox();
});
</script>
