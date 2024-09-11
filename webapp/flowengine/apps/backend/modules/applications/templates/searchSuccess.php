<?php
use_helper("I18N");
?>

<div class="pageheader">
  <h2><i class="fa fa-envelope"></i> <?php echo __('Search'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="<?php echo public_path('backend.php') ?>"><?php echo __('Home'); ?></a></li>
      <li><a href="<?php echo public_path('backend.php/applications/search') ?>"><?php echo __('Search'); ?></a></li>
    </ol>
  </div>
</div>

<div class="contentpanel">

<div id='search_adv' name='search_adv' class="g12" style='padding-top: 4px;<?php
	if($_POST['search_posted'] == '1'){ echo "display: none;"; } ?>'>
	<form autocomplete="off" data-ajax="false">
	<fieldset>
	<input type='hidden' name='search_posted' id='search_posted' value='1'>
	</fieldset>
	</form>


  <div class="panel-group" id="accordion">



    <div class="list-group">
              <div class="list-group-item">
					             <?php echo __('Quick Search'); ?>
                </div>
              <div class="list-group-item">
          							<form class="form-bordered" method='post' action="<?php echo public_path('backend.php/applications/search') ?>" autocomplete="off" data-ajax="false">
          							 <div class="form-group">
            							<label class="col-sm-2"><?php echo __('Application No'); ?></label>
            							<div class="col-sm-8">
            							<input class="form-control" type='text' name='applicationid' id='applicationid' style="width:80%;" value="<?php echo $_POST['applicationid']; ?>">
            							</div>
          							</div>
          							 <button class="btn btn-primary" type="submit" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Search'); ?></button>

          							</form>

							<?php
								if($_POST['applicationid'])
								{
									?>
									<div style="border-top:2px solid #d2d2d2;">
									<h3 class="subtitle mt20 ml10"><?php echo __('Search Results'); ?>:</h3>
									<div class="table-responsive">
									<table class="table dt-on-steroids mb0" id="table3">
									<thead>
										<tr>
										<th width="215px;"><?php echo __('Type'); ?></th><th width="86px"><?php echo __('No'); ?></th><th width="135px"><?php echo __('Submitted On'); ?></th><th width="106px"><?php echo __('Submitted By'); ?></th><th width="86px"><?php echo __('Status'); ?></th><th width="38px"><?php echo __('Actions'); ?></th>
										</tr>
									</thead>
									<?php
									    $q = Doctrine_Query::create()
										   ->from('FormEntry a')
										   ->where('a.application_id LIKE ?', '%'.trim($_POST['applicationid']).'%')
										   ->andWhere('a.parent_submission = 0');
										$applications = $q->execute();
										foreach($applications as $application)
										{
										?>
										<tr id="row_<?php echo $application->getId() ?>">

										<td width="215px"><?php
										$q = Doctrine_Query::create()
											 ->from('ApForms a')
											 ->where('a.form_id = ?', $application->getFormId());
										$form = $q->fetchOne();
										if($form)
										{
											echo $form->getFormName();
										}
										else
										{
											echo "-";
										}
										?></td>
										<td width="86px"><?php echo $application->getApplicationId(); ?></td>
										<td width="135px"><?php
											echo $application->getDateOfSubmission();
										?></td>
										<td class="c" width="106px">
										<?php
											$q = Doctrine_Query::create()
											 ->from('sfGuardUserProfile a')
											 ->where('a.user_id = ?', $application->getUserId());
										$userprofile = $q->fetchOne();
										if($userprofile)
										{
											echo $userprofile->getFullname();
										}
										else
										{
											echo "-";
										}
										?>
										</td>
										<td class="c" width="180px">
										<?php
											 $q = Doctrine_Query::create()
												->from('SubMenus a')
												->where('a.id = ?', $application->getApproved());
											$submenu = $q->fetchOne();

											if($submenu)
											{
												echo $submenu->getTitle();
											}
											else
											{
												echo "-";
											}
										?>
										</td>
										<td class="c" width="38px">
										<a title='<?php echo __('View Application'); ?>' href='/plan/applications/view/id/<?php echo $application->getId(); ?>'><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
										</td>
									</tr>
								<?php
								    }
								?>
								</table>
								</div>
								</div>
							<?php
								}
							?>
		    </div>
        </div>






		<div class="list-group">
              <div class="list-group-item">
					             <?php echo __('Advanced Search'); ?>
              </div>
              <div class="list-group-item">
              <div class="panel-body">
							<form class="form-bordered"  method='post' action="<?php echo public_path('backend.php/applications/search') ?>" autocomplete="off" data-ajax="false">

							<label><?php echo __('Type Of Application'); ?></label>
							<br>

							<select id='application_form' name='application_form' onChange="ajaxSearchform1(this.value); $('#adv_search').detach();">
										<option value="0"><?php echo __('Select form'); ?>....</option>
										<?php
												$q = Doctrine_Query::create()
													->from('ApForms a')
													->where('a.form_type <> ?', '2')
													->andWhere('a.form_active = ?', 1)
													->orderBy('a.form_id ASC');
												$forms = $q->execute();

												$count = 0;

												foreach($forms as $form)
												{

													$selected = "";

													if($application_form != "" && $application_form == $form->getFormId())
													{
														$selected = "selected='selected'";
													}


													echo "<option value='".$form->getFormId()."' ".$selected.">".$form->getFormName()."</option>";

													$count++;
												}
											?>
									</select>





							<div id='ajaxsearchform' name='ajaxsearchform'>



					</div></div>
									<div class="panel-footer"><button class="reset btn btn-danger mr10"><?php echo __('Reset'); ?></button><button class="submit btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Search'); ?></button>



							</form>
			<!-- OTB ADD -->
				  <?php if($_POST['application_form']): ?>
					<div id="adv_search">
									<div style="border-top:2px solid #d2d2d2;">
									<h3 class="subtitle mt20 ml10"><?php echo __('Search Results'); ?>:</h3>
									<div class="table-responsive">
									<table class="table dt-on-steroids mb0" id="table3">
									<thead>
										<tr>
										<th width="215px;"><?php echo __('Type'); ?></th>
										<th width="86px"><?php echo __('No'); ?></th>
										<th width="135px"><?php echo __('Submitted On'); ?></th>
										<th width="106px"><?php echo __('Submitted By'); ?></th>
										<th width="86px"><?php echo __('Status'); ?></th>
										<th width="38px"><?php echo __('Actions'); ?></th>
										</tr>
									</thead>
			  
									<?php
										$q="SELECT * FROM ap_form_".$_POST['application_form']." INNER JOIN form_entry ON form_entry.form_id = ".$_POST['application_form']." AND form_entry.entry_id = ap_form_".$_POST['application_form'].".id WHERE";
										$q_arr=[];
										foreach($_POST as $k => $v){
											if(strlen($v) != 0 && $k != 'application_form' && $k != 'submitbuttonname'){
												$q_arr[]=" ".$k." LIKE '%".trim($v)."%'";
											}
										}
										//join the array or queries
										$q.=' '.implode(' || ',$q_arr);
										$q.=" ORDER BY form_entry.id DESC";
										error_log('-------------'.$q.'---------');
										$applications=Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
										error_log('--------------------');
										error_log(print_r($applications,true));
										foreach($applications as $application)
										{
										?>
										<tr id="row_<?php echo $application['id'] ?>">
										<td width="215px"><?php
										$q = Doctrine_Query::create()
											 ->from('ApForms a')
											 ->where('a.form_id = ?', $_POST['application_form']);
										$form = $q->fetchOne();
										if($form)
										{
											echo $form->getFormName();
										}
										else
										{
											echo "-";
										}
										?></td>
										<td width="86px"><?php echo $application['application_id']; ?></td>
										<td width="135px"><?php
											echo $application['date_of_submission'];
										?></td>
										<td class="c" width="106px">
										<?php
											$q = Doctrine_Query::create()
											 ->from('sfGuardUserProfile a')
											 ->where('a.user_id = ?', $application['user_id']);
										$userprofile = $q->fetchOne();
										if($userprofile)
										{
											echo $userprofile->getFullname();
										}
										else
										{
											echo "-";
										}
										?>
										</td>
										<td class="c" width="180px">
										<?php
											 $q = Doctrine_Query::create()
												->from('SubMenus a')
												->where('a.id = ?', $application['approved']);
											$submenu = $q->fetchOne();

											if($submenu)
											{
												echo $submenu->getTitle();
											}
											else
											{
												echo "-";
											}
										?>
										</td>
										<td class="c" width="38px">
										<a title='<?php echo __('View Application'); ?>' href='/plan/applications/view/id/<?php echo $application['id']; ?>'><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
										</td>
										</tr>
									<?php
										}
									?>
									</table>
									</div>
									</div>
						<?php endif; ?>
						<!-- OTB END -->
					</div>
				</div>


</div>

	</div>
</div>

<script language="javascript">
function ajaxSearchform1(formid) {
    var xmlHttpReq1 = false;
    var self1 = this;
    // Mozilla/Safari

    if (window.XMLHttpRequest) {
        self.xmlHttpReq1 = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq1.open('POST', '/plan/applications/formsearch/formid/' + formid, true);
    self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq1.onreadystatechange = function() {
        if (self.xmlHttpReq1.readyState == 4) {
            document.getElementById('ajaxsearchform').innerHTML = self.xmlHttpReq1.responseText;
        }
        else
        {
            document.getElementById('ajaxsearchform').innerHTML = '<img src="/asset_pics/loading.gif">';

        }
    }

    self.xmlHttpReq1.send('formid=' + formid);

}
</script>