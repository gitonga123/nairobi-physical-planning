<?php use_helper('I18N') ?>
<div class="pageheader">
  <h2><?php echo __("Agenda"); ?></h2>
</div>
<div class="contentpanel">
   <div class="panel panel-dark">
	   <div class="panel-heading">
            <h3 class="panel-title"><?php echo __("Agenda set for stage"); ?></h3>
			<div class="pull-right">
			   <a href="<?php echo url_for('/plan/dashboard/index') ?>" class="btn btn-info-alt settings-margin42"><?php echo __('Back to Dashboard') ?></a>
			</div>
		</div>
       <div class="panel-body panel-body-nopadding">
		<table class="table">
			<thead>
				<tr>
					<th>Form</th>
					<th>Stage</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($agenda_column as $agenda):?>
				<tr>
					<td><?php echo $agenda->getFormname() ?></td>
					<td><?php echo html_entity_decode($agenda->getStagename()) ?></td>
					<td>
					<a href="<?php echo url_for('/plan/agenda/show?id='.$agenda->getId()) ?>" class="btn btn-primary">Generate Agenda</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	</div>
</div>