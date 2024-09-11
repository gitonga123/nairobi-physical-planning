<?php
/**
 * edituserSuccess.php template.
 *
 * Edit reviewer details
 *
 * @package    backend
 * @subpackage users
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="pageheader">
	<h2><i class="fa fa-envelope"></i><?php echo __('Reviewers'); ?></h2>
	<div class="breadcrumb-wrapper">
		<span class="label"><?php echo __('You are here'); ?>:</span>
		<ol class="breadcrumb">
			<li>
				<a href=""><?php echo __('Home'); ?></a>
			</li>
			<li>
				<a href=""><?php echo __('Reviewers'); ?></a>
			</li>
			<li class="active"><?php echo __('new'); ?></li>
		</ol>
	</div>
</div>

<div class="contentpanel">
	<div class="row">



		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<?php

					if (empty($strFirstName) && empty($strLastName)) {
						echo __("New Reviewer");
					} else {
						echo $strFirstName . " " . $strLastName;
					}

					?> <?php echo __('Details'); ?>
				</h3>
			</div>

			<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>

			<div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
				<button type="button" class="close"
					onClick="document.getElementById('alertdiv').style.display = 'none';"
					aria-hidden="true">&times;</button>
				<strong><?php echo __('Well done'); ?></strong>
				<?php echo __('You successfully updated reviewer'); ?></a>.
			</div>

			<div class="panel-body padding-0">


				<div id="basicWizard" class="basic-wizard">


					<div class="tab-content tab-content-nopadding">


						<div class="tab-pane active" id="tabs-1">
							<form id="reviewerform" class="form-bordered form-horizontal">
								<div class="panel mb0">
									<div class="panel-body padding-0">

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('First Name'); ?></i></label>

											<div class="col-sm-8">


												<input id="strFirstName" Name="strFirstName" type="text"
													class="form-control" value="<?php echo $strFirstName; ?>"
													required="required">
											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Last Name'); ?></i></label>

											<div class="col-sm-8">

												<input id="strLastName" Name="strLastName" type="text"
													class="form-control" value="<?php echo $strLastName; ?>"
													required="required">

											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Email Address'); ?></i></label>

											<div class="col-sm-8">

												<input id="strEMail" Name="strEMail" type="text" class="form-control"
													<?php if ($strEMail) {
														echo "disabled='disabled'";
													} ?>
													value="<?php echo $strEMail; ?>" required="required">


											</div>

										</div>

										<div id="emailresult" name="emailresult"></div>

										<script language="javascript">
											$('document').ready(function () {
												$('#strEMail').change(function () {
													$.ajax({
														type: "POST",
														url: "/plan/users/checkemail",
														data: {
															'email': $('input:text[name=strEMail]').val()
														},
														dataType: "text",
														success: function (msg) {
															//Receiving the result of search here
															$("#emailresult").html(msg);
														}
													});
												});
											});
										</script>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Username'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" Name="UserName" id="UserName" class="form-control"
													<?php if ($strUserId) {
														echo "disabled='disabled'";
													} ?>
													value="<?php echo $strUserId; ?>" required="required">


											</div>

										</div>

										<div id="usernameresult" name="usernameresult"></div>

										<script language="javascript">
											$('document').ready(function () {
												$('#UserName').change(function () {
													$.ajax({
														type: "POST",
														url: "/plan/users/checkuser",
														data: {
															'username': $('input:text[name=UserName]').val()
														},
														dataType: "text",
														success: function (msg) {
															//Receiving the result of search here
															$("#usernameresult").html(msg);
														}
													});
												});
											});
										</script>

										<?php if ($strUserId) { ?>
											<div class="form-group"><label class="col-sm-2 control-label"
													for="text_field"><i
														class="bold-label"><?php echo __('Password'); ?></i></label>

												<div class="col-sm-8">
													<button class="btn btn-primary mr20" type="button"
														onClick="window.location='/plan/users/reset/email/<?php echo $strEMail; ?>'">Reset
														Password</button>
												</div>

											</div>
										<?php
										} else {
											?>

											<div class="form-group"><label class="col-sm-2 control-label"
													for="text_field"><i
														class="bold-label"><?php echo __('Password'); ?></i></label>

												<div class="col-sm-8">

													<input type="password" Name="Password1" id="Password1"
														class="form-control" value="" required="required">


												</div>

											</div>

											<div class="form-group"><label class="col-sm-2 control-label"
													for="text_field"><i
														class="bold-label"><?php echo __('Confirm Password'); ?></i></label>

												<div class="col-sm-8">

													<input type="password" Name="Password2" id="Password2"
														class="form-control" value="" required="required">


												</div>

											</div>

											<div id="passwordresult" name="passwordresult"></div>
											<?php
										}
										?>
										<script language="javascript">
											$('document').ready(function () {
												$('#Password1').change(function () {
													if ($('#Password1').val() == $('#Password2').val() && $('#Password1').val() != "") {
														$('#passwordresult').html('<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords match!</strong></div>');
													}
													else {
														$('#passwordresult').html('<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords don\'t match!</strong> Try again.</div>');
													}
												});
												$('#Password2').change(function () {
													if ($('#Password1').val() == $('#Password2').val() && $('#Password1').val() != "") {
														$('#passwordresult').html('<div class="alert alert-success"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords match!</strong></div>');
													}
													else {
														$('#passwordresult').html('<div class="alert alert-danger"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><strong>Passwords don\'t match!</strong> Try again.</div>');
													}
												});
											});
										</script>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Department'); ?></i></label>

											<div class="col-sm-8">
												<select id="IN_department" name="IN_department" required="required">
													<option value=''><?php echo __('Select Department'); ?>...</option>
													<?php
													$q = Doctrine_Query::create()
														->from('Department a');
													$departments = $q->execute();
													foreach ($departments as $this_department) {
														$selected = "";
														if ($department == $this_department->getId()) {
															$selected = "selected";
														}
														echo "<option value='" . $this_department->getId() . "' " . $selected . ">" . $this_department->getDepartmentName() . "</option>";
													}
													?>
												</select>


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Groups'); ?></i></label>

											<div class="col-sm-8">

												<select id="groups" name="groups[]" multiple class="form-control"
													required="required">
													<?php
													$q = Doctrine_Query::create()
														->from('MfGuardGroup a')
														->orderBy('a.name ASC');
													$groups = $q->execute();
													foreach ($groups as $group) {
														$selected = "";
														$q = Doctrine_Query::create()
															->from('MfGuardUserGroup a')
															->where('a.user_id = ?', $userid)
															->andWhere('a.group_id = ?', $group->getId());
														$usergroup = $q->execute();

														if (sizeof($usergroup) > 0) {
															$selected = "selected";
														}

														?>
														<option value='<?php echo $group->getId(); ?>' <?php echo $selected; ?>><?php echo $group->getName(); ?></option>
														<?php
													}
													?>
												</select>


											</div>
										</div>

										<script language="javascript">
											jQuery(document).ready(function () {

												var demo1 = $('[id="groups"]').bootstrapDualListbox();

											});
										</script>


										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Street'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_street" name="IN_street" class="form-control"
													value="<?php echo $street ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i class="bold-label"><?php echo __('Zip Code'); ?>
												</i></label>

											<div class="col-sm-2">

												<input type="text" id="IN_zipcode" name="IN_zipcode"
													class="form-control" value="<?php echo $zipcode ?>">
											</div>

											<div class="col-sm-6">

												<input type="text" id="IN_city" name="IN_city" class="form-control"
													value="<?php echo $city ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Country'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_country" name="IN_country"
													class="form-control" value="<?php echo $country ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Phone 1'); ?></i></label>

											<div class="col-sm-8">
												<input type="text" id="IN_phone_main1" name="IN_phone_main1"
													class="form-control" value="<?php echo $phone_main1 ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Phone 2'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_phone_main2" name="IN_phone_main2"
													class="form-control" value="<?php echo $phone_main2 ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Mobile Number'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_phone_mobile" name="IN_phone_mobile"
													class="form-control" value="<?php echo $phone_mobile ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Fax Number'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_fax" name="IN_fax" class="form-control"
													value="<?php echo $fax ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Organization'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_organisation" name="IN_organisation"
													class="form-control" value="<?php echo $organisation ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Cost Center'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_cost_center" name="IN_cost_center"
													class="form-control" value="<?php echo $cost_center ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Designation'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_userdefined1_value"
													name="IN_userdefined1_value" class="form-control"
													value="<?php echo $userdefined1_value ?>">


											</div>

										</div>

										<div class="form-group"><label class="col-sm-2 control-label"
												for="text_field"><i
													class="bold-label"><?php echo __('Man Number'); ?></i></label>

											<div class="col-sm-8">

												<input type="text" id="IN_userdefined2_value"
													name="IN_userdefined2_value" class="form-control"
													value="<?php echo $userdefined2_value ?>">

											</div>

										</div>
									</div>
									<div class="panel-footer">
										<button class="btn btn-danger mr20"><?php echo __('Reset'); ?></button><button
											type="submit" class="btn btn-primary" name="submitbuttonname"
											id="submitbutton"
											value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
										<input type="hidden" value="<?php echo $userid; ?>" id="userid" name="userid">
									</div>
							</form>
						</div>

					</div><!--tabs-2-->

				</div><!--basicWizard-->

			</div><!--Panel-body-->

		</div><!--panel-default-->





	</div>
</div>
<script language="javascript">
	jQuery(document).ready(function () {
		$("#submitbutton").click(function () {
			var submit_form = true;
			$('form#reviewerform').find('input').each(function () {
				if ($(this).prop('required') && $(this).val() == "") {
					alert('Required field missing');
					$(this).focus();
					submit_form = false;
				}
			});
			$('form#reviewerform').find('textarea').each(function () {
				if ($(this).prop('required') && $(this).val() == "") {
					alert('Required field missing');
					$(this).focus();
					submit_form = false;
				}
			});
			if (submit_form) {
				$.ajax({
					url: '/plan/users/writeuser',
					cache: false,
					type: 'POST',
					data: $('#reviewerform').serialize(),
					success: function (json) {
						console.log('------' + json);
						if (json == 'Success') {
							$('#alertdiv').attr("style", "display: block;");
							$("html, body").animate({ scrollTop: 0 }, "slow");
						}
						if (json == 'Failed') {
							alert('Reviewer creation failed!');
							$('#alertdiv').attr("style", "display: none;");
							$("#strEMail").trigger('change');
							$("#UserName").trigger('change');
							$("html, body").animate({ scrollTop: 0 }, "slow");
						}
					}
				});
			}
			return false;
		});
	});
</script>