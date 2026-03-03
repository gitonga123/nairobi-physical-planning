<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managefees"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Fee Zones'); ?></h3>
        </div>


        <div class="panel-heading text-right">
            <a class="btn btn-warning"  id="update_zone" href="<?php echo url_for('/plan/zones/updatezones') ?>" ><?php echo __('Update Zones'); ?></a>
			<script>
			$(document).ready(function(){
				$('#update_zone').click(function(e){
					$.ajax({
						url: "<?php echo url_for('/plan/zones/updatezones') ?>",
						type: "GET",
						dataType: "json",
					}).done(function(resp){
						console.log(JSON.stringify(resp));
					}).fail(function( xhr, status, errorThrown ) {
						//alert( "Sorry, there was a problem!" );
						console.log( "Error: " + errorThrown );
						console.log( "Status: " + status );
						console.dir( xhr );
					});
					return false;
				});
				$('#preloader').ajaxStart(function(){
					$(this).show();
				}).ajaxStop(function(){
					$(this).hide();
					location.reload(true);
				});
			});
			</script>
            <a class="btn btn-primary" id="newpage" href="/plan/zones/new" ><?php echo __('+ Add Fee Zone'); ?></a>
        </div>

        <div class="panel-body">
        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th ><?php echo __('Zone id'); ?></th>
                    <th ><?php echo __('Name'); ?></th>
                    <th ><?php echo __('Sub County'); ?></th>
                    <th class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($zones as $zone): ?>
                    <tr id="row_<?php echo $zone->getId() ?>">
                        <td><?php echo $zone->getId(); ?></td>
                        <td><?php echo $zone->getZoneId(); ?></td>
                        <td><?php echo $zone->getName(); ?></td>
                        <td><?php echo $zone->getSubCounty();  ?></td>
                        <td align="center">
                            <a id="zoneedit<?php echo $zone->getId(); ?>" href="/plan/zones/edit/id/<?php echo $zone->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <a id="zonedelete<?php echo $zone->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/zones/delete/id/<?php echo $zone->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
      </div>
    </div>
</div>

<script language='javascript'>
  jQuery('#table3').dataTable({
      "sPaginationType": "full_numbers",

      // Using aoColumnDefs
      "aoColumnDefs": [
      	{ "bSortable": false, "aTargets": [ 'no-sort' ] }
    	]
    });
</script>

<?php
}
else
{
  include_partial("settings/accessdenied");
}
?>
