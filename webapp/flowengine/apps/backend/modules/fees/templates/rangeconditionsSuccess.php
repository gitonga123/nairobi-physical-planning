<?php use_helper("I18N"); 
if($sf_user->mfHasCredential("managefees")):
$otb_helper = new OTBHelper();
?>
<div class="panel panel-default">
<div class="panel-heading">
	<h3 class="panel-title"><?php echo __('Fee Range Conditions'); ?></h3>
</div>
<div class="panel-heading text-right">
	<a class="btn btn-primary settings-margin42" id="newrangecondition" href="#newrangecondition"><?php echo __('New Fee Range Condition'); ?></a>
            <script language="javascript">
            jQuery(document).ready(function(){
              $( "#newrangecondition" ).click(function() {
                  $("#loadrangeconditions").load("<?php echo url_for('/backend.php/fees/rangeconditionform/filter/'.$filter) ?>");
              });
            });
            </script>
</div>

<?php use_helper("I18N"); ?>
<?php if($sf_user->hasFlash('save_notice')): ?>
	<div><?php echo $sf_user->getFlash('save_notice') ?></div>
<?php endif; ?>
  <div class="pageheader">
       <h2><i class="fa fa-edit"></i><?php echo __('Fee Range Conditions'); ?></h2>
      <div class="breadcrumb-wrapper">
        
        <ol class="breadcrumb">
          <li><a href=""><?php echo __('Fee Range Conditions'); ?></a></li>
          <li class="active"><?php echo __('This page list all the fee range conditions that will be applied to fee range'); ?></li>
        </ol>
      </div>
    </div>
		<div class="panel-body panel-body-nopadding">
			<div class="table-responsive">
				<table class="table dt-on-steroids mb0" id="table_fee_range">
					<thead>
						<th><?php echo __('Condition Field') ?></th>
						<th><?php echo __('Condition Operator') ?></th>
						<th><?php echo __('Condition Value') ?></th>
						<th><?php echo __('Action') ?></th>
					</thead>
					<tbody>
						<?php foreach($fee_range_conditions as $fee_range_condition):
								if ($fee_range_condition->getConditionOperator() == 1){
									$operator = "is equal to";
								}else if($fee_range_condition->getConditionOperator() == 2){
									$operator = "is less than";
								}else if($fee_range_condition->getConditionOperator() == 3){
									$operator = "is greater than";
								}else if($fee_range_condition->getConditionOperator() == 4){
									$operator = "is less than or equal to";
								}else if($fee_range_condition->getConditionOperator() == 5){
									$operator = "is greater than or equal to";
								}else if($fee_range_condition->getConditionOperator() == 6){
									$operator = "is not equal";
								}else if($fee_range_condition->getConditionOperator() == 7){
									$operator = "is like";
								}else{
									$operator = false;
								}
							?>
							<tr class="unread">
								<td><?php
									$q = Doctrine_Query::create()
									->from('FeeRange a')
									->where('a.id = ?', $filter)
									->orderBy('a.id ASC');
									$fee_range = $q->fetchOne();

									echo $otb_helper->getFieldLabel($fee_range->getFee()->getInvoicetemplates()->getApplicationform(), $fee_range_condition->getConditionField());
								?></td>
								<td><?php echo $operator ?></td>
								<td><?php echo $fee_range_condition->getConditionValue() ?></td>
								<td>                
								<a id="editrangecondition<?php echo $fee_range_condition->getId(); ?>" href="#editrangecondition" title="<?php echo __('Edit'); ?>"><span class="badge badge-primary"><i class="fa fa-pencil"></i></span></a>
								<a id="deleterangecondition<?php echo $fee_range_condition->getId(); ?>" href="#deleterangecondition<?php echo $fee_range_condition->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="badge badge-primary"><i class="fa fa-trash-o"></i></span></a>
								<script language="javascript">
								jQuery(document).ready(function(){
								  $( "#editrangecondition<?php echo $fee_range_condition->getId() ?>" ).click(function() {
									console.log("the link ### <?php echo url_for('/backend.php/fees/rangeconditionform/id/'.$fee_range_condition->getId().'/filter/'.$filter) ?>");
									  $("#loadrangeconditions").load("<?php echo url_for('/backend.php/fees/rangeconditionform/id/'.$fee_range_condition->getId().'/filter/'.$filter) ?>");
								  });
								  $( "#deleterangecondition<?php echo $fee_range_condition->getId() ?>" ).click(function() {
									  if(confirm('Are you sure you want to delete this condition?')){
										$("#loadrangeconditions").load("<?php echo url_for('/backend.php/fees/deleterangecondition/id/'.$fee_range_condition->getId().'/filter/'.$filter) ?>");
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