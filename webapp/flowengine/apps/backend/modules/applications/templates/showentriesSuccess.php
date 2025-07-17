<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed membership form");
?>

<div class="contentpanel panel-email">
    <div class="panel panel-dark">

        <div class="panel-heading">
                <h3 class="panel-title"><?php echo $apform[0]['form_name'] ?></h3>
		</div>
        <div class="panel-heading text-right">
                        <a class="btn btn-primary settings-margin42" href="<?php echo url_for('/plan/applications/new?form_id='.$apform[0]['form_id']) ?>">New <?php echo ucfirst($apform[0]['form_name']) ?> Entry</a>
        </div>

		<div class="panel panel-body panel-body-nopadding ">

		<div class="table-responsive">
		<table class="table dt-on-steroids mb0" id="table3">
			<thead>
		   <tr>
			  <th>#</th>
			  <?php foreach($form_elements as $element): ?>
			  <th><?php echo $element['element_title'] ?></th>
			  <?php endforeach; ?>
			  <th class="no-sort"><?php echo __('Actions'); ?></th>
			</tr>
		  </thead>
		  <tbody>
			<?php foreach($entries as  $entry): ?>
			<tr>
				<td><?php echo $entry['id']; ?></td>
				<?php foreach($form_elements as $e): ?>
				<td>
				<?php if(strlen($entry['element_'.$e['element_id']])): ?>
					<?php echo $entry['element_'.$e['element_id']]; ?>
				<?php else: ?>
					<?php for($i=1;$i<=20;$i++): ?>
						<?php echo $entry['element_'.$e['element_id'].'_'.$i]; ?>
					<?php endfor; ?>
				<?php endif; ?>
				</td>
				<?php endforeach; ?>
			   <td>
				<a title="<?php echo __('Edit'); ?>" href="<?php echo url_for('/plan/applications/editentries?form_id='.$apform[0]['form_id'].'&id='.$entry['id']) ?>"><span class="badge badge-primary"><i class="fa fa-pencil"></i></span></a>
				</td>
			</tr>

			<?php endforeach; ?>

		</tbody>
		</table>
		</div>
		</div><!--panel-body-->
    </div>
</div>
<script>
  jQuery(document).ready(function() {
      jQuery('#table3').dataTable({
          "sPaginationType": "full_numbers",

          // Using aoColumnDefs
          "aoColumnDefs": [
          	{ "bSortable": false, "aTargets": [ 'no-sort' ] }
        	]
        });
    });
</script>
