<?php use_helper("I18N"); 
if($sf_user->mfHasCredential("managefees")):
?>
<div class="panel panel-default">
<div class="panel-heading">
	<h3 class="panel-title"><?php echo __('Fee Ranges'); ?></h3>
</div>
<div class="panel-heading text-right">
	<a class="btn btn-primary settings-margin42" id="newrange" href="#newrange"><?php echo __('New Fee Range'); ?></a>
            <script language="javascript">
            jQuery(document).ready(function(){
              $( "#newrange" ).click(function() {
                  $("#loadranges").load("<?php echo url_for('/backend.php/fees/feerange/filter/'.$filter) ?>");
              });
            });
            </script>
</div>

<?php use_helper("I18N"); ?>
<?php if($sf_user->hasFlash('save_notice')): ?>
	<div><?php echo $sf_user->getFlash('save_notice') ?></div>
<?php endif; ?>
  <div class="pageheader">
       <h2><i class="fa fa-edit"></i><?php echo __('Fee Ranges'); ?></h2>
      <div class="breadcrumb-wrapper">
        
        <ol class="breadcrumb">
          <li><a href=""><?php echo __('Fee Ranges'); ?></a></li>
          <li class="active"><?php echo __('This page list all the fee ranges that will be applied to applications'); ?></li>
        </ol>
      </div>
    </div>
		<div class="panel-body panel-body-nopadding">
			<div class="table-responsive">
				<table class="table dt-on-steroids mb0" id="table_fee_range">
					<thead>
						<th><?php echo __('Range Name') ?></th>
						<!--<th><?php echo __('Min value') ?></th>
						<th><?php echo __('Max value') ?></th>-->
						<th><?php echo __('Result') ?></th>
						<th><?php echo __('Action') ?></th>
					</thead>
					<tbody>
						<?php foreach($fee_ranges as $fee_range): ?>
							<tr class="unread">
								<td><?php echo $fee_range->getName() ?></td>
								<!--<td><?php echo $fee_range->getRange_1() ?></td>
								<td><?php echo $fee_range->getRange_2() ?></td>-->
								<td><?php echo $fee_range->getResultValue() ?></td>
								<td>                
								<a id="editrange<?php echo $fee_range->getId() ?>" href="#editrange" title="<?php echo __('Edit'); ?>"><span class="badge badge-primary"><i class="fa fa-pencil"></i></span></a>
								<a id="deleterange<?php echo $fee_range->getId() ?>" href="#deleterange" title="<?php echo __('Delete'); ?>"><span class="badge badge-primary"><i class="fa fa-trash-o"></i></span></a>
								<script language="javascript">
								jQuery(document).ready(function(){
								  $( "#editrange<?php echo $fee_range->getId() ?>" ).click(function() {
									  $("#loadranges").load("<?php echo url_for('/backend.php/fees/feerange/id/'.$fee_range->getId().'/filter/'.$filter) ?>");
								  });
								  $( "#deleterange<?php echo $fee_range->getId() ?>" ).click(function() {
									  if(confirm('Are you sure you want to delete this range?')){
										$("#loadranges").load("<?php echo url_for('/backend.php/fees/deleterange/id/'.$fee_range->getId().'/filter/'.$filter) ?>");
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
		</div>
</div>
	<script>
		jQuery('#table_fee_range').dataTable();
	</script>
<?php else:
	include_partial("settings/accessdenied");
endif; ?>