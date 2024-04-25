<?php
/**
 * indexSuccess.php template.
 */
use_helper("I18N");
$agency_manager = new AgencyManager();//OTB - Managing agency access
?>

<div class="pageheader">
  <h2><i class="fa fa-home"></i><?php echo __('Billing'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="<?php echo public_path('/'); ?>backend.php"><?php echo __('Home'); ?></a></li>
      <li class="active"><?php echo __('Billing'); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
	<div class="panel panel-bordered radius-all">
		<div class="panel-body panel-body-nopadding">
			<table class="table" width="100%">
				<tbody>
					<tr>
						<td class="form-group border-bottom-0">        
							<select size="1" name="table2_length" aria-controls="table2" class="select2" onChange="window.location='/backend.php/invoices/index/filter/' + this.value;">
        						<option value="0">Select Product/Service</option>
								<?php 
									foreach($form_options as $option){
										echo html_entity_decode($option);
									} 
								?>
							</select>
						</td>
						<td style="width:10%; border:0px;" class="form-group border-bottom-0 radius-tr">
						<form style="margin:0" action="#" method="post">
							<?php if($filter): ?>
								<input type="hidden" name="filter" value="<?php echo $filter ?>"/>
							<?php endif; ?>
							<?php if($filter_status): ?>
								<input type="hidden" name="filter_status" value="<?php echo $filter_status ?>"/>
							<?php endif; ?>
							<?php if($fromdate): ?>
								<input type="hidden" name="fromdate" value="<?php echo $fromdate ?>"/>
							<?php endif; ?>
							<?php if($todate): ?>
								<input type="hidden" name="todate" value="<?php echo $todate ?>"/>
							<?php endif; ?>
							<input type='hidden' name='export' id='export' value='1'><button class="btn btn-default btn-xs table-billing-btn btn-excell" type="submit">E<span class="hidden-xs">xport</span></button>
						</form>
						</td>
					</tr>
				</tbody>
			</table>
	<table class="table table-striped table-hover mb0 border-top-0 border-left-0 border-right-0 panel-table" width="100%">
		<thead class="form-horizontal">
		  	<tr>
				<form method="post" action="/backend.php/invoices/index">
					<?php if($filter): ?>
						<input type="hidden" name="filter" value="<?php echo $filter ?>"/>
					<?php endif; ?>
					<th class="border-bottom-1" style="width:40%;">
						<select style="width:40%;" size="1" name="filter_status" aria-controls="table2" class="select2">
							<option value="1" <?php if($filter_status == "1"){ echo "selected='selected'"; } ?>><?php echo __('Pending') ?></option>
							<option value="15" <?php if($filter_status == "15"){ echo "selected='selected'"; } ?>><?php echo __('Part Payment') ?></option>
							<option value="2" <?php if($filter_status == "2"){ echo "selected='selected'"; } ?>><?php echo __('Paid') ?></option>
							<option value="3" <?php if($filter_status == "3"){ echo "selected='selected'"; } ?>><?php echo __('Cancelled') ?></option>
						</select>
					</th>
					<th class="border-bottom-1">
						<input name="fromdate" value="<?php echo $fromdate; ?>" type="text" class="form-control  datepicker p10" placeholder="Starting From">
					</th>
					<th class="border-bottom-1">

						<input name="todate" value="<?php echo $todate; ?>" type="text" class="form-control datepicker p10" placeholder="Ending">
					</th>
					<th style="width:10%;" class="border-bottom-1">
						<button type="submit" class="btn table-billing-btn btn-default">G<span class="hidden-xs">O</span></button>
					</th>
			   </form>
		  </tr>
		</thead>
	</table>
			<table class="table" id="invoices" width="100%">
				<thead>
					<tr>
						<th>#</th>
						<th><?php echo __('Form Title') ?></th>
						<th><?php echo __('Application Id') ?></th>
						<th><?php echo __('Applicant') ?></th>
						<th><?php echo __('Stage') ?></th>
						<th><?php echo __('Invoice No.') ?></th>
						<th><?php echo __('Date registered') ?></th>
						<th><?php echo __('Date paid') ?></th>
						<th><?php echo __('Date of expiry') ?></th>
						<th><?php echo __('Payee') ?></th>
						<th><?php echo __('Amount') ?></th>
						<th><?php echo __('Currency') ?></th>
						<th><?php echo __('Status') ?></th>
						<th><?php echo __('Action') ?></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	<div>
</div>

<script>
	$(function(){
		$('#invoices').DataTable({
			"processing": true,
			"serverSide" : true,
			"ajax": {
				url:"#",
				type: "get",
				error:function(){
							//$(".employee-grid-error").html("");
							//$("#apps_tbl").append('<tbody class="tasks_inbox-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
							$("#invoices_processing").css("display","none");
				},				
				complete:function(){
							$("#invoices_processing").css("display","none");
				}
			},
			"fnDrawCallback": function (oSettings) {
				$('tbody > tr', $(this)).removeClass('hide');
			},
			"fnPreDrawCallback": function (oSettings) {
				$('tbody > tr', $(this)).addClass('hide');
				return true;
			},
			"rowCallback":function(row,data,index){
				var btn='<a title="<?php echo __('View Invoice') ?>" href="'+window.location.protocol+'//'+window.location.hostname+':'+window.location.port+'/backend.php/invoices/view/id/'+data.inv_id+'"><span class="badge badge-primary"><i class="fa fa-eye"></i></span></a>';
				$('td:eq(13)',row).html(btn);
				var link='<a title="<?php echo __('View Application') ?>" href="'+window.location.protocol+'//'+window.location.hostname+':'+window.location.port+'/backend.php/applications/view/id/'+data.app_id+'">'+data.application_id+'</a>';
				$('td:eq(2)',row).html(link);
			},
			columns: [
				{ data: 'id'},
				{ data: 'form_name'},
				{ data: 'application_id'},
				{ data: 'user'},
				{ data: 'stage'},
				{ data: 'inv_no'},
				{ data: 'date_created'},
				{ data: 'updated_date'},
				{ data: 'expiry_date'},
				{ data: 'payee'},
				{ data: 'amount'},
				{ data: 'currency'},
				{ data: 'status'},
				{ data: 'id'}
			]
		});
		$(".datepicker").datepicker();
	});
	</script>