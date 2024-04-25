<?php use_helper('I18N') ?>
<div class="pageheader">
  <h2><?php echo __("Agenda"); ?></h2>
</div>
<div class="contentpanel">
   <div class="panel panel-dark">
	   <div class="panel-heading">
            <h3 class="panel-title"><?php echo __("Application Agenda"); ?></h3>
		</div>
		<div class="panel-heading">
			<a class="btn btn-primary settings-margin42" id="newpage" href="<?php echo url_for('/backend.php/agenda/new') ?>" ><?php echo __('New Agenda Layout'); ?></a>
		</div>
       <div class="panel-body panel-body-nopadding">
		<table class="table">
			<thead>
				<tr>
					<th>Form</th>
					<th>Elements</th>
					<th>Application elements</th>
					<th>Stage</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($agenda_columnss as $agenda):?>
				<tr>
					<td><?php echo $agenda->getFormname() ?></td>
					<td><?php echo html_entity_decode($agenda->getElements()) ?></td>
					<td><?php echo html_entity_decode($agenda->getAppColums()) ?></td>
					<td><?php echo html_entity_decode($agenda->getStagename()) ?></td>
					<td>
						<div class="btn-group">
							<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
								Action <span class="caret"></span>
							</button>
							<ul class="dropdown-menu pull-right">
								<li><a href="<?php echo url_for('/backend.php/agenda/edit?id='.$agenda->getId()) ?>">Edit</a></li>
								<li><a href="<?php echo url_for('/backend.php/agenda/delete?id='.$agenda->getId()) ?>" onClick="return window.confirm('Are you sure?');" >Delete</a></li>
							</ul>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	</div>
</div>
<script>
$(function(){
	$('.table').dataTable();
});
</script>