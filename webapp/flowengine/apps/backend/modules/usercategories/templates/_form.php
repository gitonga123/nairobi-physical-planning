<?php
use_helper("I18N");
?>
<form  action="/plan/usercategories/<?php echo ($form->getObject()->isNew() ? 'create' : 'update') . (!$form->getObject()->isNew() ? '?id=' . $form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" '?>   autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()): ?>
  <input type="hidden" name="sf_method" value="put" />
  <?php endif;?>

  <?php if (isset($form['_csrf_token'])): ?>
  <?php echo $form['_csrf_token']->render(); ?>
  <?php endif;?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew() ? __('New User Category') : __('Edit User Category')); ?></h3>
      <?php echo $form->renderGlobalErrors() ?>

      <div class="pull-right">
          <a class="btn btn-primary" id="newpage" href="/plan/usercategories/index" ><?php echo __('Back to List'); ?></a>
      </div>
    </div>

    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Name'); ?></label><br>
        <div class="col-sm-12">
            <?php echo $form['name']->renderError() ?>
            <?php echo $form['name'] ?>
          </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Description'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['description']->renderError() ?>
          <?php echo $form['description'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Profile Form'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['formid']->renderError() ?>
          <?php echo $form['formid'] ?>
        </div>
      </div>
			  <!--OTB Start - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc. -->
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('What is the Membership Association for this set of users?'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_association_name']->renderError() ?>
				  <?php echo $form['member_association_name'] ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('External Membership Association Number Validation Field'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_no_element_id']->renderError() ?>
				  <?php echo $form['member_no_element_id'] ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Active Single Use Membership Number Field?'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_database_member_one_single_use']->renderError() ?>
				  <?php echo $form['member_database_member_one_single_use'] ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Membership Number and Email must match in membership database?'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['membership_email_match']->renderError() ?>
				  <?php echo $form['membership_email_match'] ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Field for Membership Validation Email to match with'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['validation_email_element_id']->renderError() ?>
				  <?php echo $form['validation_email_element_id'] ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Field for Membership address'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_address']->renderError() ?>
				  <?php echo $form['member_address'] ?>
				</div>
			  </div>
			<script language="javascript">
			 jQuery(document).ready(function(){
				 $('#sf_guard_user_categories_formid').change(function(){
					var value = this.value ;
					 $.ajax({
						url: '<?php echo url_for('/plan/usercategories/changefield/form_id/'); ?>'+value,
						cache: false,
						type: 'POST',
						data : $('#bform').serialize(),
						success: function(json) {
							$('#sf_guard_user_categories_member_no_element_id').empty().append(json);
							$('#sf_guard_user_categories_member_address').empty().append(json);
							$('#sf_guard_user_categories_member_database_member_one_single_use').empty().append(json);
							<?php if (!$form->getObject()->isNew()): ?>
							$('#sf_guard_user_categories_member_no_element_id').val(<?php echo $form->getObject()->getMemberNoElementId() ?>);
							$('#sf_guard_user_categories_member_address').val(<?php echo $form->getObject()->getMemberAddress() ?>);
							$('#sf_guard_user_categories_member_database_member_one_single_use').val(<?php echo $form->getObject()->getMemberDatabaseMemberOneSingleUse() ?>);
							<?php endif;?>
						}
					});

					 $.ajax({
						url: '<?php echo url_for('/plan/usercategories/changefield/form_id/'); ?>'+value+'/email/1',
						cache: false,
						type: 'POST',
						data : $('#bform').serialize(),
						success: function(json) {
							$('#sf_guard_user_categories_validation_email_element_id').empty().append(json);
							<?php if (!$form->getObject()->isNew()): ?>
							$('#sf_guard_user_categories_validation_email_element_id').val(<?php echo $form->getObject()->getValidationEmailElementId() ?>);
							<?php endif;?>
						}
					});
				}).trigger('change');
		     });
			 </script>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('On registration, send verification to each members email in association database?'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['send_verification_email']->renderError() ?>
				  <?php echo $form['send_verification_email'] ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Verification email template'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_email_verification_message']->renderError() ?>
				  <?php echo $form['member_email_verification_message'] ?>
				</div>
			  </div>
			  <!--OTB End - User Membership  Database validation e.g. Boraqs, Engineers Association, Planner's association etc. -->
			  <!-- OTB MEMBER DB SELECTION -->
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Membership database'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_database']->renderError() ?>
				  <?php echo $form['member_database'] ?>
				</div>
			  </div>
			  <script>
			  $(function(){
				  $('#sf_guard_user_categories_member_database').change(function(){
					  var member_form=$(this).val();
						$('#sf_guard_user_categories_member_database_member_no_field').children().remove();
						$('#sf_guard_user_categories_member_database_member_email_field').children().remove();
						$('#sf_guard_user_categories_member_database_member_name_field').children().remove();
					  if(member_form != 0){
						  $.ajax({
							  url: "<?php echo url_for('/plan/usercategories/updatememeberfields'); ?>",
							  data:{ form: member_form},
							  type: "POST",
							  dataType: "json",
						  }).done(function(resp){
							//Populate
							$.each(resp.all,function(i,x){
							$('#sf_guard_user_categories_member_database_member_no_field').append('<option value="'+i+'">'+x+'</option>');
							$('#sf_guard_user_categories_member_database_member_name_field').append('<option value="'+i+'">'+x+'</option>');
							});
							$.each(resp.email,function(i,x){
							$('#sf_guard_user_categories_member_database_member_email_field').append('<option value="'+i+'">'+x+'</option>');
							});
							<?php if (!$form->getObject()->isNew()): ?>
							$('#sf_guard_user_categories_member_database_member_no_field').val(<?php echo $form->getObject()->getMemberDatabaseMemberNoField() ?>);
							$('#sf_guard_user_categories_member_database_member_email_field').val(<?php echo $form->getObject()->getMemberDatabaseMemberEmailField() ?>);
							$('#sf_guard_user_categories_member_database_member_name_field').val(<?php echo $form->getObject()->getMemberDatabaseMemberNameField() ?>);
							<?php endif;?>
						  }).fail(function( xhr, status, errorThrown ) {
							//alert( "Sorry, there was a problem!" );
							console.log( "Error: " + errorThrown );
							console.log( "Status: " + status );
							console.dir( xhr );
						  });
					  }

				  }).trigger('change');
			  });
			  </script>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Membership database member no field'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_database_member_no_field']->renderError() ?>
				  <?php echo $form['member_database_member_no_field'] ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Membership database email field'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_database_member_email_field']->renderError() ?>
				  <?php echo $form['member_database_member_email_field'] ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Membership database member name field'); ?></i></label>
				<div class="col-sm-8 rogue-input">
				  <?php echo $form['member_database_member_name_field']->renderError() ?>
				  <?php echo $form['member_database_member_name_field'] ?>
				</div>
			  </div>
			  <!-- OTB END -->
	<div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Allowed Forms'); ?></label><br>
        <div class="col-sm-12">
          <select name='islinkedto[]' id='islinkedto' class='form-control' multiple>
						<?php
$q = Doctrine_Query::create()
	->from("ApForms a")
	->where("a.form_type = 1")
	->orderBy("a.form_name ASC");
$forms = $q->execute();

foreach ($forms as $apform) {
	$selected = "";
	if (!$form->getObject()->isNew()) {

		$q = Doctrine_Query::create()
			->from("SfGuardUserCategoriesForms a")
			->where("a.categoryid = ?", $form->getObject()->getId())
			->andWhere("a.formid = ?", $apform->getFormId());
		$catforms = $q->execute();
		if (sizeof($catforms) > 0) {
			$selected = "selected";
		}
	}
	echo "<option value='" . $apform->getFormId() . "' " . $selected . ">" . $apform->getFormName() . "</option>";
}
?>
					</select>
        </div>
      </div>
			<div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Order'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['orderid']->renderError() ?>
          <?php echo $form['orderid'] ?>
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
		var list1 = jQuery('select[name="islinkedto[]"]').bootstrapDualListbox();
	});
</script>
