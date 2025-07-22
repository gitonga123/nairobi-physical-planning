<?php
use_helper("I18N");
if($sf_user->mfHasCredential("access_reports"))
{
	$formoptions = "";

	$formoptions.="<optgroup label='".__("Application Forms")."'>";

	$formoptions.="<option value=''>".__("Choose form")."...</option>";

	$q = Doctrine_Query::create()
		->from('ApForms a')
		->where('a.form_type = ? or a.form_type = ?', array(1, 0))
		->andWhere('a.form_active = ?', 1)
		->orderBy('a.form_name ASC');
	$forms = $q->execute();

	foreach($forms as $form)
	{

		$formoptions.="<option value='".$form->getFormId()."'>".$form->getFormCode()." - ".$form->getFormName()."</option>";

	}

	$formoptions.="</optgroup>";

	$serviceoptions = "";

	$serviceoptions.="<optgroup label='".__("Application Forms")."'>";

	$serviceoptions.="<option value=''>".__("Choose form")."...</option>";

	$q = Doctrine_Query::create()
		->from('Menus a')
		->orderBy('a.title ASC');
	$services = $q->execute();

	foreach($services as $service)
	{

		$serviceoptions.="<option value='".$service->getId()."'>".$service->getTitle()."</option>";

	}

	$serviceoptions.="</optgroup>";

	$menuoptions = "";
	$q = Doctrine_Query::create()
		->from('Menus a')
		->orderBy('a.order_no ASC');
	$stagegroups = $q->execute();
	foreach($stagegroups as $stagegroup)
	{
		$menuoptions.="<div class=\"col-xs-12 col-sm-6 col-md-3\">
					<div class=\"blog-item pt20 pl20 pr20 pb20\" style=\"background:white; border: 1px solid #dddddd\">
					<h4 class=\"panel-title mb10 mt10\">".$stagegroup->getTitle()."</h4>";
		$q = Doctrine_Query::create()
			->from('SubMenus a')
			->where('a.menu_id = ?', $stagegroup->getId())
			->andWhere('a.deleted = 0')
			->orderBy('a.order_no ASC');
		$stages = $q->execute();

		foreach($stages as $stage)
		{
			$selected = "";

			if($filter != "" && $filter == $stage->getId())
			{
				$selected = "selected";
			}

			$menuoptions.="<div class=\"checkbox block\">
							<label>
								<input type='checkbox' name='pending_stage[".$stage->getId()."]' id='pending_stage_".$stage->getId()."' value='".$stage->getId()."'>
								".$stage->getTitle()."
							</label>
						</div>";

		}
		$menuoptions.= '</div></div>';
	}

	$menuoptions2 = "";
	$q = Doctrine_Query::create()
		->from('Menus a')
		->orderBy('a.order_no ASC');
	$stagegroups = $q->execute();
	foreach($stagegroups as $stagegroup)
	{
		$menuoptions2.="<optgroup label='".$stagegroup->getTitle()."'>";
		$q = Doctrine_Query::create()
			->from('SubMenus a')
			->where('a.menu_id = ?', $stagegroup->getId())
			->andWhere('a.deleted = 0')
			->orderBy('a.order_no ASC');
		$stages = $q->execute();

		foreach($stages as $stage)
		{
			$selected = "";

			if($filter != "" && $filter == $stage->getId())
			{
				$selected = "selected";
			}

			$menuoptions2.="<option value='".$stage->getId()."' ".$selected.">".$stage->getTitle()."</option>";

		}
		$menuoptions2.="</optgroup>";
	}


	$formoptions2 = "";
	$formoptions2.="<optgroup label='".__("Application Forms")."'>";

	foreach($groups as $group)
	{
		$formoptions2.="<optgroup label='".$group->getGroupName()."'>";

		$q = Doctrine_Query::create()
		->from('ApForms a')
		->leftJoin('a.ApFormGroups b')
		->where('a.form_id = b.form_id')
		->andWhere('b.group_id = ?', $group->getGroupId())
		->andWhere('a.form_id = 60 OR a.form_id = 47 OR a.form_id = 48 OR a.form_id = 49');
		$forms = $q->execute();

		foreach($forms as $form)
		{
			$selected = "";

			if($application_form != "" && $application_form == $form->getFormId())
			{
				$selected = "selected";
				$_GET['form'] = $application_form;
			}

			$formoptions2.="<option value='".$form->getFormId()."' ".$selected.">".$form->getFormDescription()."</option>";
		}

		$formoptions2.="</optgroup>";
	}


	$formoptions2.="</optgroup>";

	$mdaoptions = "";
	$mdaoptions .= "<option id='1'>".sfConfig::get('app_mda_branch')."</option>";

	$pspoptions = "";
	$pspoptions .= "<option id='pesapal'>PesaPal</option>";
?>

<div class="pageheader">
  <h2><i class="fa fa-file-text"></i><?php echo __('Reports'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/plan/dashboard"><?php echo __('Home'); ?></a></li>
      <li><?php echo __('Reports'); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">

  	<div class="panel-group" id="accordion">

		<!-- Built-In report 1: Report of all applications that have been submitted within a specified time period and their status -->
		<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#collapse3t">
					<?php echo __('Time Table'); ?>
				</a>
				</h4>
			</div>
			<div id="collapse3t" class="panel-collapse collapse">
			<div class="panel-body padding-0" id="report1_div">

				<form id="report1_form" name="report1_form" class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/timetablereport?tr=1" autocomplete="off" data-ajax="false">
					<div class="panel-heading-inline panel-heading-inline-gray">
					<h3 class="panel-title"><?php echo __('Report that uses the date and time fields in an application form to create a schedule for submitted applications'); ?></h3>
					</div>
				<div class="form-group">
				<label class="col-sm-3 control-label"><?php echo __('Type Of Application'); ?></label>
				<div class="col-sm-9">
					<select id='application_form_timetable' name='application_form' required>
						<?php
							echo $formoptions;
						?>
					</select>
				</div>
			</div>
			<div class="form-group">
							<label class="col-sm-3 control-label"><?php echo __('Select Date Field'); ?></label>
					<div class="col-sm-9">
						<div id='ajaxdatefields' name='ajaxdatefields' >
						</div>
					</div>
			</div>
			<div class="form-group">
							<label class="col-sm-3 control-label"><?php echo __('Select Time Field'); ?></label>
					<div class="col-sm-9">
						<div id='ajaxtimefields' name='ajaxtimefields' >
						</div>
					</div>
			</div>
			<div class="form-group">
						<label class="col-sm-3 control-label"><?php echo __('Status'); ?></label>
				<div class="col-sm-6">
					<select id='application_status' name='application_status'>
					<option value="0"><?php echo __('Filter By Stage'); ?></option>
					<?php
						echo $menuoptions2;
					?>
				</select>
				</div>
			</div>
			</div>
			<div class="panel-footer">
				<button type="reset" class="btn btn-danger mr5"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
			</div>
		</form>
		</div>
	</div>


	<!-- Built-In report 2: Report of all applications that have been approved within a specific time period and their status. -->
	<div class="panel panel-default">
	<div class="panel-heading">
		<h4 class="panel-title">
			<a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
				<?php echo __('Filter Applications By Dropdown'); ?>
			</a>
		</h4>
	</div>
	<div id="collapseFilter" class="panel-collapse collapse">
	<div class="panel-body padding-0">
	<form class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/reportfilter" autocomplete="off" data-ajax="false">
		<div class="panel-heading-inline panel-heading-inline-gray">
			<h3 class="panel-title"><?php echo __('Report of all applications within a time period filtered by a dropdown'); ?></h3>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label"><?php echo __('Type Of Application'); ?></label>
							<div class="col-sm-6">
									<select id='application_form_filter' name='application_form_filter' required>
											<?php
													echo $formoptions;
											?>
									</select>
							</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label"><?php echo __('From Date'); ?></label>
						<div class="col-sm-6">
												<div class="input-group">
							<input type='text' name='from_date_filter' id='from_date1' class="form-control" required>
													<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
												</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label"><?php echo __('To Date'); ?></label>
						<div class="col-sm-6">
											<div class="input-group">
							<input type='text' name='to_date_filter' id='to_date1' class="form-control" required>
													<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
												</div>
						</div>
					</div>

						<div class="form-group">
										<label class="col-sm-3 control-label"><?php echo __('Select Dropdown Field'); ?></label>
								<div class="col-sm-9">
									<div id='ajaxdropdownfields' name='ajaxdropdownfields' >
									</div>
								</div>
						</div>

						<div class="form-group">
										<label class="col-sm-3 control-label"><?php echo __('Filter By Dropdown Option'); ?></label>
								<div class="col-sm-9">
									<div id='ajaxdropdownvaluefields' name='ajaxdropdownvaluefields' >
									</div>
								</div>
						</div>
				</div>

			<div class="panel-footer">
			<button type="reset" class="btn btn-danger mr5"><?php echo __('Reset'); ?></button>
			<button type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
		</div>
	</form>
		</div>
	</div>

		<!-- Built-In report 1: Report of all applications that have been submitted within a specified time period and their status -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
					  <?php echo __('Submissions Report'); ?>
				  </a>

                </h4>
              </div>
              <div id="collapse3" class="panel-collapse collapse">
              <div class="panel-body padding-0" id="report1_div">

			<form id="report1_form" name="report1_form" class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/report1" autocomplete="off" data-ajax="false">
            <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of all applications that have been submitted within a specified time period and their status'); ?></h3>
            </div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Type Of Application'); ?></label>
					<div class="col-sm-9">
						<select id='application_form' name='application_form' onChange="ajaxSearchform(this.value);" required>
							<?php
								echo $formoptions;
							?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo __('From Date'); ?></label>
					<div class="col-sm-9">
                        <div class="input-group">
						<input type='text' name='from_dateblt1' id='from_dateblt1' class="form-control" required>
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo __('To Date'); ?></label>
					<div class="col-sm-9">
                         <div class="input-group">
						<input type='text' name='to_date' id='to_date' class="form-control" required>
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>

					</div>
				 <div id='ajaxsearchform' name='ajaxsearchform' >
				 </div>
				</div>
                </div>
				<div class="panel-footer">
					<button type="reset" class="btn btn-danger mr5"><?php echo __('Reset'); ?></button>
					<button type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
				</div>
			</form>
         </div>
        </div>

		<!-- Built-In report 2: Report of all applications that have been approved within a specific time period and their status. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse4">
					  <?php echo __('Approvals Report'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse4" class="panel-collapse collapse">
              <div class="panel-body padding-0">
		<form class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/report2" autocomplete="off" data-ajax="false">
        <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of all applications that have been approved within a specific time period and their status'); ?></h3>
            </div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Type Of Application'); ?></label>
                    <div class="col-sm-6">
                        <select id='application_form' name='application_form' onChange="ajaxSearchform(this.value,1);" required>
                            <?php
                                echo $formoptions;
                            ?>
                        </select>
                    </div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo __('From Date'); ?></label>
					<div class="col-sm-6">
                       <div class="input-group">
						<input type='text' name='from_date1' id='from_date_approval' class="form-control" required>
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo __('To Date'); ?></label>
					<div class="col-sm-6">
                    <div class="input-group">
						<input type='text' name='to_date1' id='to_date_approval' class="form-control" required>
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>
					</div>
				 <div id='ajaxsearchform1' name='ajaxsearchform1'></div>
				</div>
               </div>

					<div class="panel-footer">
					<button type="reset" class="btn btn-danger mr5"><?php echo __('Reset'); ?></button>
					<button type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
				</div>
			</form>
         </div>
        </div>

        <!-- Built-In report 3: Report of all applications that are pending at a particular stage of the workflow. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse5">
					  <?php echo __('Pending Stages Report'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse5" class="panel-collapse collapse">
              <div class="panel-body padding-0">
		<form class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/report3" autocomplete="off" data-ajax="false">
         <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of all applications that are pending at a particular stage of the workflow'); ?></h3>
            </div>
			 <div class="form-group">
						<?php
							echo $menuoptions;
						?>
				</div>
                  </div>
				<div class="panel-footer">
					<button type="reset" class="btn btn-danger mr5"><?php echo __('Reset'); ?></button>
					<button onclick="if(atLeastOneCheckbox()){ return true; }else{ alert('Please choose one or more options first.'); window.location = '/plan/reports/list'; return false; }" type="submit "class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
				</div>
			</form>
         </div>
        </div>

        <!-- Built-In report 4: Report of all applications that have exceeded their designated time limit within the various workflow stages. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse6">
					  <?php echo __('Overdue Applications Reports'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse6" class="panel-collapse collapse">
              <div class="panel-body padding-0">
		<form class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/report4" autocomplete="off" data-ajax="false">
         <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of all applications that have exceeded their designated time limit within the various workflow stages'); ?></h3>
            </div>
			 <div class="form-group">

						<?php
							echo $menuoptions;
						?>
				</div>
            </div>
			 <div class="panel-footer">
            	<button type="reset" class="btn btn-danger mr5"><?php echo __('Reset'); ?></button>
                <button onclick="if(atLeastOneCheckbox()){ return true; }else{ alert('Please choose one or more options first.'); return false; }" class="btn btn-primary" type="submit" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
            </div>
			</form>
         </div>
        </div>

        <!-- Built-In report 5: Report of all applications pending action from the requestor (developer/architect). -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse7">
					  <?php echo __('Pending Action Report'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse7" class="panel-collapse collapse">
              <div class="panel-body padding-0">
		<form class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/report5" autocomplete="off" data-ajax="false">
        <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of all applications pending action from the client e.g. declined applications that need to be resubmitted or invoices that need payment'); ?></h3>
            </div>
			 <div class="form-group">
						<?php
							echo $menuoptions;
						?>
				</div>
             </div>

			 <div class="panel-footer">
				<button type="reset" class="btn btn-danger mr5"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
			</div>
			</form>
         </div>
        </div>

        <!-- Built-In report 6: Report of all applications that are pending at a particular stage of the workflow. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse8">
					 <?php echo __('Approvals/Rejections Report'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse8" class="panel-collapse collapse">
              <div class="panel-body padding-0">
		<form class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/report6" autocomplete="off" data-ajax="false">
        <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of all applications sent to a particular stage and the name of the person that sent it there'); ?></h3>
            </div>
			 <div class="form-group">
						<?php
							echo $menuoptions;
						?>
				</div>
             </div>
			 <div class="panel-footer">
				<button type="reset" class="btn btn-danger mr5"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
			</div>
			</form>
         </div>
        </div>

        <!-- Built-In report 7: Report of all notifications for an application that have been sent. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse9">
					  <?php echo __('Mail/SMS Notifications Report'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse9" class="panel-collapse collapse">
              <div class="panel-body padding-0">
		<form class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/report7" autocomplete="off" data-ajax="false">
        <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of all notifications for an application that have been sent'); ?></h3>
            </div>
			 <div class="form-group">
						<?php
							echo $menuoptions;
						?>
				</div>
             </div>

			 <div class="panel-footer">
				<button type="reset" class="btn btn-default mr5"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
			</div>
			</form>
         </div>
        </div>

        <!-- Built-In report 10: Report of all activities with a certain period of time. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse12">
					  <?php echo __('Audit Trail Report'); ?>
				  </a>
                </h4>
              </div>

			<div id="collapse12" class="panel-collapse collapse">
			<div class="panel-body padding-0">
			<form class="form-horizontal fh-special form-bordered" method="post" action="/plan/reports/report10" autocomplete="off" data-ajax="false">
            <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of all activities with a certain period of time'); ?></h3>
            </div>

			<div class="form-group">
			<label class="col-sm-3 control-label"><?php echo __('From Date'); ?></label>
			<div class="col-sm-6">
				<div class="input-group">
					<input type='text' name='from_date10' id='from_date10' class="form-control">
					<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
					</div>
				</div>
			</div>

			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('To Date'); ?></label>
				<div class="col-sm-6">
					<div class="input-group">
						<input type='text' name='to_date10' id='to_date10' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
					</div>
				</div>
			</div>

			<div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Reviewer'); ?></label>
				<div class="col-sm-6">
					<div class="input-group">
						<select name="reviewer" id="reviewer">
							<?php 
								$q = Doctrine_Query::create()
									->from("CfUser a")
									->orderBy("a.Strfirstname ASC");
								$reviewers = $q->execute();

								foreach($reviewers as $reviewer)
								{
									echo "<option value='".$reviewer->getNid()."'>".$reviewer->getStrfirstname()." ".$reviewer->getStrlastname()."</option>";
								}
							?>
						</select>
					</div>
				</div>
			</div>

			</div>
			 <div class="panel-footer">
				<button type="reset" class="btn btn-danger"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
			</div>
			</form>

         </div>
        </div>

        <!-- Built-In report 12: Report of the income from confirmed payments. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse14">
					  <?php echo __('Financial Report: By Forms'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse14" class="panel-collapse collapse">
              <div class="panel-body padding-0">
			<form class="form-horizontal form-bordered fh-special" method="post" action="/plan/invoices/index/export/1" autocomplete="off" data-ajax="false">
            <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of the income from confirmed payments per form'); ?></h3>
            </div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Type Of Application'); ?></label>
			  	<div class="col-sm-6">
				<select id='application_form' name='filter' >
			    <?php
					echo $formoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('From Date'); ?></label>
					<div class="col-sm-6">
                       <div class="input-group">
						<input type='text' name='fromdate' id='from_date12' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>
					</div>
				</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('To Date'); ?></label>
					<div class="col-sm-6">
                     <div class="input-group">
						<input type='text' name='todate' id='to_date12' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                         </div>
					</div>
				</div>
                </div>
			 <div class="panel-footer">
				<button type="reset" class="btn btn-danger"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="export" value="export"><?php echo __('Submit'); ?></button>
			</div>
			</form>
         </div>
        </div>

		<!-- Built-In report 12: Report of the income from confirmed payments. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse14a">
					  <?php echo __('Financial Report: By Service'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse14a" class="panel-collapse collapse">
              <div class="panel-body padding-0">
			<form class="form-horizontal form-bordered fh-special" method="post" action="/plan/invoices/report/filter/service" autocomplete="off" data-ajax="false">
            <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of the income from confirmed payments per service/workflow'); ?></h3>
            </div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Select a Service'); ?></label>
			  	<div class="col-sm-6">
				<select id='application_service' name='application_service' >
			    <?php
					echo $serviceoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('From Date'); ?></label>
					<div class="col-sm-6">
                       <div class="input-group">
						<input type='text' name='fromdate' id='from_date12a' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>
					</div>
				</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('To Date'); ?></label>
					<div class="col-sm-6">
                     <div class="input-group">
						<input type='text' name='todate' id='to_date12a' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                         </div>
					</div>
				</div>
                </div>
			 <div class="panel-footer">
				<button type="reset" class="btn btn-danger"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="export" value="export"><?php echo __('Submit'); ?></button>
			</div>
			</form>
         </div>
        </div>

		<?php 
		/**
		*
		*  Reports for each profile and their renewable services
		*
		**/
		$q = Doctrine_Query::create()
           ->from("SfGuardUserCategories a")
           ->where("a.formid <> 0")
           ->orderBy("a.name ASC");
        $profiles = $q->execute();

		foreach($profiles as $profile)
		{
			$business_serviceoptions = "";

			$business_serviceoptions.="<optgroup label='".__("Services")."'>";

			$q = Doctrine_Query::create()
				->from('Menus a')
				->where('a.service_type = ?', 2)
				->andWhere('a.service_form = ?', $profile->getFormId())
				->orderBy('a.title ASC');
			$services = $q->execute();

			foreach($services as $service)
			{

				$business_serviceoptions.="<option value='".$service->getId()."'>".$service->getTitle()."</option>";

			}

			$business_serviceoptions.="</optgroup>";
		?>
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $profile->getId(); ?>prof">
					  <?php echo $profile->getForm()->getFormName().": ".__('Renewed Services'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse<?php echo $profile->getId(); ?>prof" class="panel-collapse collapse">
				<div class="panel-body padding-0">
				<form class="form-horizontal form-bordered fh-special" method="post" action="/plan/profiles/report/filter/service" autocomplete="off" data-ajax="false">
					<input type="hidden" name="profile_id" value="<?php echo $profile->getId(); ?>">
					<div class="panel-heading-inline panel-heading-inline-gray">
					<h3 class="panel-title" style="padding-left: 15px;"><?php echo __('Report of renewed services'); ?></h3>
					</div>
					<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo __('Select a Service'); ?></label>
						<div class="col-sm-6">
						<select id='application_service' name='application_service' >
							<?php
								echo $business_serviceoptions;
							?>
					</select>
					</div>
					</div>
					<div class="form-group">
					<label class="col-sm-3 control-label"><?php echo __('Filter by Status'); ?></label>
						<div class="col-sm-6">
						<select id='application_status' name='application_status' >
							<option value='0'><?php echo __('All'); ?></option>
							<option value='1'><?php echo __('Renewed'); ?></option>
							<option value='2'><?php echo __('Not Renewed'); ?></option>
						</select>
						</div>
					</div>
					</div>
					<div class="panel-footer">
						<button type="reset" class="btn btn-danger"><?php echo __('Reset'); ?></button>
						<button type="submit" class="btn btn-primary" name="export" value="export"><?php echo __('Submit'); ?></button>
					</div>
				</form>
			</div>
        </div>

		<div class="panel panel-default">
			<div class="panel-heading">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $profile->getId(); ?>finance">
					<?php echo $profile->getForm()->getFormName().": ".__('Financial Reports'); ?>
				</a>
			</h4>
			</div>
			<div id="collapse<?php echo $profile->getId(); ?>finance" class="panel-collapse collapse">
				<div class="panel-body padding-0">
					<form class="form-horizontal form-bordered fh-special" method="post" action="/plan/profiles/finance/filter/service" autocomplete="off" data-ajax="false">
						<input type="hidden" name="profile_id" value="<?php echo $profile->getId(); ?>">

						<?php 
						foreach($services as $service)
						{
						?>
						<input type="hidden" name="service_id" value="<?php echo $service->getId(); ?>">
						<?php 
						}
						?>

						<div class="panel-heading-inline panel-heading-inline-gray">
						<h3 class="panel-title" style="padding-left: 15px;"><?php echo __('Finance report of renewed services'); ?></h3>
						</div>
						<div class="form-group">
						<label class="col-sm-3 control-label"><?php echo __('Select a Service'); ?></label>
							<div class="col-sm-6">
							<select id='application_service' name='application_service' >
								<?php
									echo $business_serviceoptions;
								?>
						</select>
						</div>
						</div>

						<div class="form-group">
						<label class="col-sm-3 control-label"><?php echo __('Filter by Fees'); ?></label>
							<div class="col-sm-6">
							<?php 
							    $main_fee_title = "";

								$q = Doctrine_Query::create()
								   ->from("ApFormElements a")
								   ->where("a.element_id = ?", $service->getServiceFeeField())
								   ->andWhere("a.form_id = ?", $service->getServiceForm());
								$element = $q->fetchOne();

								if($element)
								{
									$main_fee_title = $element->getElementTitle();
									?>
									<input type="hidden" name="fee_filters_other" value="<?php echo $main_fee_title; ?>">
									<?php
								}
							?>
							
							<select id='fee_filters' name='fee_filters' >
								<?php 
								if($main_fee_title)
								{
								?>
								<option value='main_fee'><?php echo $main_fee_title." ".__('Fee'); ?></option>
								<?php 
								}

								foreach($services as $service)
								{
									$q = Doctrine_Query::create()
										->from("MoreFees a")
										->where("a.service_id = ?", $service->getId())
										->orderBy("a.fee_title ASC");

									foreach($q->execute() as $fee)
									{
									?>
									<option value='<?php echo $fee->getFeeTitle(); ?>'><?php echo $fee->getFeeTitle(); ?></option>
									<?php 
									}
								}
								?>
							</select>
							</div>
						</div>

						<?php
						if(sfConfig::get("app_business_filters"))
						{
							$filters = explode("/", sfConfig::get("app_business_filters"));

							foreach($filters as $filter)
							{
								$q = Doctrine_Query::create()
									->from("ApFormElements a")
									->where("a.form_id = ?", $service->getServiceForm())
									->andWhere("a.element_id = ?", $filter);
								$field = $q->fetchOne();

								if($field)
								{
									$filter_js = "";

									$q = Doctrine_Query::create()
										->from("ApDropdownFilters a")
										->where("a.form_id = ? AND a.element_id = ?", array($service->getServiceForm(), $filter));

									if($q->count() > 0)
									{
										$filter_option = $q->fetchOne();

										$filter_js = "onChange='filter_dropdown(".$service->getServiceForm().", ".$filter.", ".$filter_option->getLinkId().", this.value);'";
									}
									?>
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php echo __('Filter by ').$field->getElementTitle(); ?></label>
										<input type="hidden" name="field_names[]" value="<?php echo $filter; ?>">
										<div class="col-sm-6" id="li_<?php echo $filter ?>_filter">
											<select id="financial_element_<?php echo $filter; ?>" name='extra_filters[]' <?php echo $filter_js; ?>>
												<option value="0">All</option>
												<?php 
													$q = Doctrine_Query::create()
														->from("ApElementOptions a")
														->where("a.form_id = ?", $service->getServiceForm())
														->andWhere("a.element_id = ?", $filter)
														->orderBy("a.option_text ASC");
													$options = $q->execute();

													foreach($options as $option)
													{
														echo "<option value='".$option->getOptionId()."'>".$option->getOptionText()."</option>";
													}
												?>
											</select>
										</div>
									</div>
									<?php
								}
							}
						}
						?>
						<div class="panel-footer">
							<button type="reset" class="btn btn-danger"><?php echo __('Reset'); ?></button>
							<button type="submit" class="btn btn-primary" name="export" value="export"><?php echo __('Submit'); ?></button>
						</div>
					</form>
				</div>
			</div>
        </div>
		<?php 
			break;
		}
		?>

        <!-- Built-In report 19: Report of the income from confirmed payments. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse19">
					  <?php echo __('Convenience Fee Report'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse19" class="panel-collapse collapse">
              <div class="panel-body padding-0">
			<form class="form-horizontal form-bordered fh-special" method="post" action="/plan/invoices/convenience" autocomplete="off" data-ajax="false">
            <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('Report of the income from confirmed payments'); ?></h3>
            </div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Type Of Application'); ?></label>
			  	<div class="col-sm-6">
				<select id='application_form' name='application_form' >
			    <?php
					echo $formoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('From Date'); ?></label>
					<div class="col-sm-6">
                       <div class="input-group">
						<input type='text' name='fromdate' id='from_date19' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>
					</div>
				</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('To Date'); ?></label>
					<div class="col-sm-6">
                     <div class="input-group">
						<input type='text' name='todate' id='to_date19' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                         </div>
					</div>
				</div>
                </div>
			 <div class="panel-footer">
				<button type="reset" class="btn btn-danger"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="export" value="export"><?php echo __('Submit'); ?></button>
			</div>
			</form>
         </div>
        </div>

        <!-- Built-In report 17: Detail Transaction Report. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse17">
					  <?php echo __('Detailed Transactions Report'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse17" class="panel-collapse collapse">
              <div class="panel-body padding-0">
			<form class="form-horizontal form-bordered fh-special" method="post" action="/plan/reports/report17" autocomplete="off" data-ajax="false">
            <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('This report gives details per transaction processed by PSPs. The users should be able to query the report based on the following criteria'); ?></h3>
            </div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('MDA'); ?></label>
			  	<div class="col-sm-6">
				<select id='payment_mda' name='payment_mda' >
			    <?php
					echo $mdaoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Type of PSP'); ?></label>
			  	<div class="col-sm-6">
				<select id='payment_psp' name='payment_psp' >
			    <?php
					echo $pspoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Service Code'); ?></label>
			  	<div class="col-sm-6">
				<select id='application_form' name='application_form' >
			    <?php
					echo $formoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Period - From Date'); ?></label>
					<div class="col-sm-6">
                       <div class="input-group">
						<input type='text' name='fromdate' id='from_date17' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>
					</div>
				</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Period - To Date'); ?></label>
					<div class="col-sm-6">
                     <div class="input-group">
						<input type='text' name='todate' id='to_date17' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                         </div>
					</div>
				</div>
                </div>
			 <div class="panel-footer">
				<button type="reset" class="btn btn-danger"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="export" value="export"><?php echo __('Submit'); ?></button>
			</div>
			</form>
         </div>
        </div>

        <!-- Built-In report 18: Summary Collection and Remittance Report. -->
	    <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse18">
					  <?php echo __('Summary Collection and Remittance Report'); ?>
				  </a>
                </h4>
              </div>
              <div id="collapse18" class="panel-collapse collapse">
              <div class="panel-body padding-0">
			<form class="form-horizontal form-bordered fh-special" method="post" action="/plan/reports/report18" autocomplete="off" data-ajax="false">
            <div class="panel-heading-inline panel-heading-inline-gray">
            <h3 class="panel-title"><?php echo __('The report will be used for remittances to MDA banks. It should aggregate all transactions per service code. The users should be able to query the report based on the following criteria'); ?></h3>
            </div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('MDA'); ?></label>
			  	<div class="col-sm-6">
				<select id='payment_mda' name='payment_mda' >
			    <?php
					echo $mdaoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Type of PSP'); ?></label>
			  	<div class="col-sm-6">
				<select id='payment_psp' name='payment_psp' >
			    <?php
					echo $pspoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Service Code'); ?></label>
			  	<div class="col-sm-6">
				<select id='application_form' name='application_form' >
			    <?php
					echo $formoptions;
				?>
			   </select>
			</div>
			</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Period - From Date'); ?></label>
					<div class="col-sm-6">
                       <div class="input-group">
						<input type='text' name='fromdate' id='from_date18' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                       </div>
					</div>
				</div>
			 <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo __('Period - To Date'); ?></label>
					<div class="col-sm-6">
                     <div class="input-group">
						<input type='text' name='todate' id='to_date18' class="form-control">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                         </div>
					</div>
				</div>
                </div>
			 <div class="panel-footer">
				<button type="reset" class="btn btn-danger"><?php echo __('Reset'); ?></button>
				<button type="submit" class="btn btn-primary" name="export" value="export"><?php echo __('Submit'); ?></button>
			</div>
			</form>
         </div>
        </div>

		<?php
        $q = Doctrine_Query::create()
			  ->from('Reports a')
			  ->orderBy('a.title ASC');
	    $reports = $q->execute();

		$count = 0;
		foreach($reports as $report)
		{
			$count++;
			?>
            <?php $count; ?>
            <div class="panel panel-default">
                  <div class="panel-heading">
                    <h4 class="panel-title">
                      <a data-toggle="collapse" data-parent="#accordion" href="#collapsecj<?php echo $report->getId(); ?>">
                          <?php echo $report->getTitle(); ?>
                      </a>
                    </h4>
                  </div>
                  <div id="collapsecj<?php echo $report->getId(); ?>" class="panel-collapse collapse">
                  <div class="panel-body padding-0">
                    <form class="form-horizontal form-bordered fh-special" method="post" action="/plan/jsonreports/view?id=<?php echo $report->getId(); ?>&page=1" autocomplete="off" data-ajax="false">

						<div class="form-group">
							<label class="col-sm-3 control-label"><?php echo __('From Date'); ?></label>
							<div class="col-sm-6">
													<div class="input-group">
								<input type='text' name='from_date_filter' id='from_date_json<?php echo $report->getId(); ?>' class="form-control" required>
														<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
													</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"><?php echo __('To Date'); ?></label>
							<div class="col-sm-6">
												<div class="input-group">
								<input type='text' name='to_date_filter' id='to_date_json<?php echo $report->getId(); ?>' class="form-control" required>
														<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
													</div>
							</div>
						</div>

                         <div class="panel-footer">
                            <button class="btn btn-primary" type="submit"><?php echo __('View Report'); ?></button>
                        </div>
                    </form>

                 </div>
                </div>

            </div>
            <script>
                jQuery(document).ready(function(){
                  jQuery('#from_date_json<?php echo $report->getId(); ?>').datepicker();
                  jQuery('#to_date_json<?php echo $report->getId(); ?>').datepicker();
                });
            </script>
			<?php
		}
		?>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
	</div>


<!-- The following functions ensure that for forms containing checkboxes or radiobuttons, atleast one option
    must be selected before the form can be submitted.
 -->
<script language="javascript">
function atLeastOneRadio() {
        return ($('input[type=radio]:checked').size() > 0);
}
</script>
<script language="javascript">
function atLeastOneCheckbox() {
        return ($('input[type=checkbox]:checked').size() > 0);
}
</script>

<script language="javascript">
	jQuery(document).ready(function(){
			jQuery("#application_form_timetable" ).change(function() {
					var selecteditem = this.value;
					$.ajax({url:"/plan/reports/getdatefields?formid=" + selecteditem,success:function(result){
					$("#ajaxdatefields").html(result);
					}});
					$.ajax({url:"/plan/reports/gettimefields?formid=" + selecteditem,success:function(result){
						$("#ajaxtimefields").html(result);
					}});
			});
	});
</script>

<script language="javascript">
	jQuery(document).ready(function(){
			jQuery("#application_form_filter" ).change(function() {
					var selecteditem = this.value;
					$.ajax({url:"/plan/reports/getdropdownfields?formid=" + selecteditem,success:function(result){
						$("#ajaxdropdownfields").html(result);
					}});
			});

			jQuery("#form_dropdown_fields" ).change(function() {
					var selecteditem = this.value;
					$.ajax({url:"/plan/reports/getdropdownvaluefields?elementid=" + selecteditem,success:function(result){
						$("#ajaxdropdownvaluefields").html(result);
					}});
			});
			
	});
</script>

<script language="javascript">
    function filter_dropdown(form_id, element_id, link_id, value) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (xhttp.readyState == 4 && xhttp.status == 200) {
                document.getElementById("li_" + link_id + "_filter").innerHTML = xhttp.responseText;
            }
        };
        xhttp.open("GET", "/plan/reports/filterdropdown?form_id=" + form_id + "&element_id=" + element_id + "&link_id=" + link_id + "&option_id=" + value, true);
        xhttp.send();
    }
</script>

<?php
}
else
{
	include_partial("settings/accessdenied");
}
?>
