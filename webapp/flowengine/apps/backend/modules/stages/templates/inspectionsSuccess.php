<?php
  use_helper("I18N");
?>
<div class="contentpanel">
  <div class="panel panel-default">
    <script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>

    <form id="stageform" class="form-bordered" action="/plan/stages/inspections/id/<?php echo $stage->getId(); ?>" method="post" autocomplete="off" data-ajax="false">

      <div class="panel-heading">
        <h3 class="panel-title"><?php echo $stage->getTitle().' -&gt; Edit Inspections'; ?></h3>
      </div>
      <div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
        <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
        <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this stage'); ?></a>.
      </div>
      <div class="panel-body padding-0">
        <div class="form-group">
          <label class="col-sm-4"><i class="bold-label"><?php echo __('Departments that should perform inspection?'); ?></i></label>
          <div class="col-sm-8">
            <select name='allowed_departments[]' id='allowed_departments' multiple="multiple" class="form-control">
              <option>Choose a department...</option>
              <?php
              $choosen_task = "";

              $q = Doctrine_Query::create()
                 ->from("Department a")
                 ->orderBy("a.department_name ASC");
              $departments = $q->execute();

              foreach($departments as $department)
              {
                  $selected = false;

                  $q = Doctrine_Query::create()
                     ->from("ServiceInspections a")
                     ->where("a.department_id = ?", $department->getId())
                     ->andWhere("a.stage_id = ?", $stage->getId());
                  if($q->count())
                  {
                    $selected = true;
                  }
                ?>
                <option value="<?php echo $department->getId(); ?>" <?php if($selected){ ?>selected='selected'<?php } ?>><?php echo $department->getDepartmentName(); ?></option>
                <?php 
              }
              ?>
            </select>
          </div>
        </div>
        
          <div class="form-group">
            <label class="col-sm-4"><i class="bold-label"><?php echo __('Inspection Sheets'); ?></i></label>
            <div class="col-sm-8">
              <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                  <tr>
                    <th width="10">#</th>
                    <th class="no-sort" width="300"><?php echo __('Form'); ?></th>
                    <th class="no-sort"><?php echo __('Department'); ?></th>
                    <th class="no-sort" width="150"><?php echo __('Actions'); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $q = Doctrine_Query::create()
                       ->from("ApForms a")
                       ->where("a.form_active = 1")
                       ->andWhere("a.form_type = 2")
                       ->andWhere("a.form_department_stage = ?", $stage->getId());
                    $available_comment_sheets = $q->execute();

                    $count = 0;
                    foreach($available_comment_sheets as $comment_sheet)
                    {
                      $count++;
                        ?>
                        <tr>
                          <td><?php echo $count; ?></td>
                          <td><?php echo $comment_sheet->getFormName(); ?></td>
                          <td>
                          <?php
                            $department_id = $comment_sheet->getFormDepartment();
                            if($department_id == 0)
                            {
                              echo "All Departments";
                            }
                            else {
                              $q = Doctrine_Query::create()
                                ->from("Department a")
                                ->where("a.id = ?", $department_id);
                              $department = $q->fetchOne();
                              if($department)
                              {
                                echo $department->getDepartmentName();
                              }
                            }
                          ?>
                          </td>
                          <td><a target="_blank" href="/plan/forms/editform?id=<?php echo $comment_sheet->getFormId(); ?>">Edit Form</a></td>
                        </tr>
                        <?php
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
         
      </div>
      <div class="panel-footer">
           <a class="btn btn-danger mr10" href="/plan/stages/index/filter/<?php echo $stage->getMenuId(); ?>"><?php echo __('Back to Workflow'); ?></a><button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
     </div>
    </form>
  </div>
</div>

<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script>
jQuery(document).ready(function(){
  var groups = $('[id="allowed_departments"]').bootstrapDualListbox();
});
</script>