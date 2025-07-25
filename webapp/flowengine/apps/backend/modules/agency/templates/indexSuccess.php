<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed agency settings");

if ($sf_user->mfHasCredential("manageagencies")) {
  $_SESSION['current_module'] = "agency";
  $_SESSION['current_action'] = "index";
  $_SESSION['current_id'] = "";
  ?>
  <div class="contentpanel">
    <div class="panel panel-dark">
      <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Agency'); ?></h3>
      </div>
      <div class="panel-heading">
        <a class="btn btn-primary settings-margin42" id="newdepartment"
          href="<?php echo url_for('/plan/agency/new') ?>"><?php echo __('New Agency'); ?></a>
      </div>

      <div class="panel panel-body panel-body-nopadding ">

        <div class="table-responsive">
          <table class="table dt-on-steroids mb0" id="table3">
            <thead>
              <tr>
                <th class="no-sort"><input type='checkbox' name='batchall'
                    onclick="boxes = document.getElementsByTagName('input'); for(var index = 0; index < boxes.length; index++) { box = boxes[index]; if (box.type == 'checkbox' && box.name == 'batch') { if(this.checked == true){ box.checked = true; }else{ box.checked = false; } } } ">
                </th>
                <th width="60">#</th>
                <th><?php echo __('Name'); ?></th>
                <th class="no-sort" width="7%"><?php echo __('Actions'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
              $count = 1;
              ?>
              <?php foreach ($agencies as $agency): ?>
                <tr id="row_<?php echo $agency->getId() ?>">
                  <td><input type='checkbox' name='batch' id='batch_<?php echo $agency->getId() ?>'
                      value='<?php echo $agency->getId() ?>'></td>
                  <td><?php echo $count++; ?></td>
                  <td><?php echo $agency->getName() ?></td>
                  <td>
                    <a id="editdepartment<?php echo $agency->getId(); ?>"
                      href="<?php echo url_for('/plan/agency/edit/id/' . $agency->getId()); ?>"
                      title="<?php echo __('Edit'); ?>"><span class="badge badge-primary"><i
                          class="fa fa-pencil"></i></span></a>
                    <?php
                    /*$q = Doctrine_Query::create()
                        ->from("AgencyUser a")
                        ->where("a.agency_id = ?", $agency->getId());
                      $agency_members = $q->execute();*/
                    ?>
                    <a id="deletedepartment<?php echo $agency->getId(); ?>"
                      onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }"
                      href="<?php echo url_for('/plan/agency/delete/id/' . $agency->getId()); ?>"
                      title="<?php echo __('Delete'); ?>"><span class="badge badge-primary"><i
                          class="fa fa-trash-o"></i></span></a>

                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan='7' style='text-align: left;'>
                  <select id='batch_action' name='batch_action'
                    onChange="if(this.value != ''){if(confirm('Are you sure?')){ batch('agency', this.options[this.selectedIndex].text, this.value); document.getElementById('default').selected='selected'; }}">
                    <option id='default' value=''><?php echo __('Choose an action'); ?>..</option>
                    <option value='delete'><?php echo __('Set As Deleted'); ?></option>
                  </select>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div><!--panel-body-->
    </div><!--panel-dark-->
  </div>

  <script>
    jQuery('#table3').dataTable({
      "sPaginationType": "full_numbers",

      // Using aoColumnDefs
      "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": ['no-sort']
      }]
    });
  </script>
  <?php
} else {
  include_partial("settings/accessdenied");
}
?>