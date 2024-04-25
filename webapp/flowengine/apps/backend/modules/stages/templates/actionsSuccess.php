<?php
  use_helper("I18N");
?>
<div class="contentpanel">
  <div class="panel panel-default">
    <script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>

    <form id="stageform" class="form-bordered" action="/backend.php/stages/actions/id/<?php echo $stage->getId(); ?>" method="post" autocomplete="off" data-ajax="false">

      <div class="panel-heading">
        <h3 class="panel-title"><?php echo $stage->getTitle().' -&gt; Edit Actions'; ?></h3>
      </div>
      <div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
        <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
        <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this stage'); ?></a>.
      </div>
      <div class="panel-body padding-0">

                 <div class="form-group">
            <label class="col-sm-4"><i class="bold-label"><?php echo __('Allowed Actions'); ?></i></label>
            <div class="col-sm-8" id="loadinner" name='loadinner'>
              <table class="table dt-on-steroids mb0" id="table3">
                <thead>
               <tr>
                  <th width="60">#</th>
                  <th><?php echo __('Title'); ?></th>
                  <th width="7%"><?php echo __('Actions'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                if(!$stage->isNew())
                {
                  $q = Doctrine_Query::create()
                     ->from("Buttons a")
                     ->leftJoin("a.Submenus b")
                     ->andWhere("b.id = ?", $stage->getId());
                  $buttonss = $q->execute();
                }
                $count = 1;
              ?>
                <?php foreach ($buttonss as $buttons): ?>
                <tr id="row_<?php echo $buttons->getId() ?>">
                <td><?php echo $count++; ?></td>
                  <td><?php echo $buttons->getTitle() ?></td>
                  <td>
                <a id="editaction<?php echo $buttons->getId(); ?>" href="#" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                <a id="deleteaction<?php echo $buttons->getId(); ?>" href="#" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>

                <script language="javascript">
                jQuery(document).ready(function(){
                  $( "#editaction<?php echo $buttons->getId(); ?>" ).click(function() {
                      $("#loadinner").load("/backend.php/buttons/edit/id/<?php echo $buttons->getId(); ?>/filter/<?php echo $stage->getId(); ?>");
                  });
                  $( "#deleteaction<?php echo $buttons->getId(); ?>" ).click(function() {
                    if(confirm('Are you sure you want to delete this button?')){
                      $("#loadinner").load("/backend.php/buttons/delete/id/<?php echo $buttons->getId(); ?>/filter/<?php echo $stage->getId(); ?>");
                      $("#row_<?php echo $buttons->getId() ?>").remove();
                    }
                    else
                    {
                      return false;
                    }
                  });
                });
                </script>
              </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>
            <br><br><br>
            <div id="newactions" name="newactions">
            </div>
            <div class="col-sm-12" style="margin-top: 10px;" align="right">
            <button type="button" class="btn btn-primary" id="addnewaction" name="addaction"><?php echo __('Add Action'); ?></button>
            <br><br>
            </div>
            <script>
              jQuery(document).ready(function(){

              var demo1 = $('[id="sub_menus_buttonsubs_list"]').bootstrapDualListbox();


                $( "#addnewaction" ).click(function() {
                    $("#newactions").append("<div class='form-group' class='formgroup'><label class='col-sm-4'><?php echo __('Action'); ?></label><div class='col-sm-8'><input type='text' name='name[]' class='form-control' placeholder='<?php echo __('Action Name'); ?>'> <br> <select name='action[]' class='form-control'><option><?php echo __('Choose action'); ?></option><option value='/backend.php/forms/move?'><?php echo __('Move to another stage'); ?></option><option value='/backend.php/forms/reject?'><?php echo __('Reject'); ?></option><option value='/backend.php/forms/decline?'><?php echo __('Back to Client'); ?></option><option value='/backend.php/forms/approve?'><?php echo __('Approve'); ?></option><?php

    ?></select><br><select name='stage[]' class='form-control'><option><?php echo __('Choose next stage'); ?></option><?php

    //List Forms
    $q = Doctrine_Query::create()
       ->from("Menus a")
       ->orderBy("a.title");
    $parent_stages = $q->execute();

    foreach($parent_stages as $parent_stage)
    {
      echo "<optgroup label='".$parent_stage->getTitle()."'>";
      $q = Doctrine_Query::create()
         ->from('SubMenus a')
         ->where('a.menu_id = '.$parent_stage->getId())
         ->andWhere('a.id <> '.$stage->getId())
         ->andWhere('a.deleted = 0')
         ->orderBy('a.order_no ASC');
      $stages = $q->execute();
      foreach($stages as $action_stage)
      {
        echo "<option value='".$action_stage->getId()."'>".$action_stage->getTitle()."</option>";
      }
      echo "</optgroup>";
    }

    ?></select><br><select name='group[]' class='form-control'><option disabled='disabled'><?php echo __('Choose group of reviewers'); ?></option><?php

    //List Forms
    $q = Doctrine_Query::create()
       ->from('MfGuardGroup a')
       ->orderBy('a.name ASC');
    $groups = $q->execute();
    foreach($groups as $group)
    {
      echo "<option value='".$group->getId()."'>".$group->getName()."</option>";
    }

    ?></select></div><a style='float: right; margin-top: 10px;' href='#' class='panel-close' onClick='$(this).closest(\"div\").remove();'>&times;</a></div>");
                });

              });
              </script>
            </div>
      </div>
      <div class="panel-footer">
           <a class="btn btn-danger mr10" href="/backend.php/stages/index/filter/<?php echo $stage->getMenuId(); ?>"><?php echo __('Back to Workflow'); ?></a><button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
     </div>
    </form>
  </div>
</div>

<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script>
jQuery(document).ready(function(){
  var groups = $('[id="allowed_groups"]').bootstrapDualListbox();
});
</script>