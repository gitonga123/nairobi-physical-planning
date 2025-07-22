<?php
	use_helper("I18N");
?>
<form  action="/plan/services/<?php echo ($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if(isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew()?__('New Service'):__('Edit Service'));?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

		<div class="panel-heading text-right">
					<a class="btn btn-primary" id="newpage" href="/plan/services/index" ><?php echo __('Back to List'); ?></a>
		</div>

    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Title'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['title']->renderError() ?>
            <?php echo $form['title'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Type of Service'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['service_type']->renderError() ?>
            <?php echo $form['service_type'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Workflow/Service Category'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['category_id']->renderError() ?>
            <?php echo $form['category_id'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Select Service Form'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['service_form']->renderError() ?>
            <?php echo $form['service_form'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Service Number'); ?>
        <br><small>(<?php echo __('application number of first application to be submitted'); ?>)</small></label><br>
        <div class="col-sm-12">
            <?php echo $form['service_number']->renderError() ?>
            <?php echo $form['service_number'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Group'); ?></label><br>
        <div class="col-sm-12">
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
                if(!$form->getObject()->isNew())
                {
                  $q = Doctrine_Query::create()
                     ->from("MfGuardPermission a")
                     ->where("a.name = ?", "accessmenu".$form->getObject()->getId());
                  $permission = $q->fetchOne();
                  if($permission->getId() == $grouppermission->getId())
                  {
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
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
 </div><!-- panel-default -->
</form>

<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script>
	jQuery(document).ready(function(){
		var list1 = jQuery('select[id="allowed_groups"]').bootstrapDualListbox();
	});
</script>
