<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<div class="contentpanel">
    <div class="row">
		    <div class="col-md-12">
				<div class="panel panel-dark">
					<div class="panel-heading">
						<h4 class="panel-title"><?php echo __("Application Agenda Layout Setup"); ?></h4>
					</div>
					<div class="panel-body panel-body-nopadding">

						<form action="<?php echo url_for('/plan/agenda/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
						<?php if (!$form->getObject()->isNew()): ?>
						<input type="hidden" name="sf_method" value="put" />
						<?php endif; ?>
						  <table class="table">
							<tfoot>
							  <tr>
								<td colspan="2">
								  <?php echo $form->renderHiddenFields(false) ?>
								  &nbsp;<a href="<?php echo url_for('/plan/agenda/index') ?>" class="btn btn-warning">Back to list</a>
								  <?php if (!$form->getObject()->isNew()): ?>
									&nbsp;<?php //echo link_to('Delete', 'agenda/delete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) ?>
								  <?php endif; ?>
								  <input type="submit" value="Save" class="btn btn-primary"/>
								</td>
							  </tr>
							</tfoot>
							<tbody>
							  <?php echo $form->renderGlobalErrors() ?>
							  <tr>
								<th><?php echo $form['form_id']->renderLabel(null,array('class' => 'col-sm-4')) ?></th>
								<td>
								  <?php echo $form['form_id']->renderError() ?>
								  <?php echo $form['form_id']->render(array('class' => 'form-control')) ?>
								</td>
							  </tr>
							  <tr>
								<th><?php echo $form['element_id']->renderLabel(null,array('class' => 'col-sm-4')) ?></th>
								<td>
								  <?php echo $form['element_id']->renderError() ?>
								  <?php echo $form['element_id']->render(array('class' => 'form-control')) ?>
								</td>
							  </tr>
							  <tr>
								<th><?php echo $form['entry_column']->renderLabel('Application',array('class' => 'col-sm-4')) ?></th>
								<td>
								  <?php echo $form['entry_column']->renderError() ?>
								  <?php echo $form['entry_column']->render(array('class' => 'form-control')) ?>
								</td>
							  </tr>
							  <tr>
								<th><?php echo $form['stage']->renderLabel(null,array('class' => 'col-sm-4')) ?></th>
								<td>
								  <?php echo $form['stage']->renderError() ?>
								  <?php echo $form['stage']->render(array('class' => 'form-control')) ?>
								</td>
							  </tr>
							</tbody>
						  </table>
						</form>
						<div class="panel-body">
							<h4>Order of Agenda&nbsp;<small>Drag to reorder</small></h4>
							<ol id="sortable">
							</ol>
						</div>
					</div>
				</div>
			</div>
	</div>
</div>
<script>
$(function(){
	$('#agenda_columns_form_id').change(function(){
		$('#agenda_columns_element_id').children().remove();
		$('#sortable').children().remove();
		$.ajax({
			url: '<?php echo url_for('/plan/usercategories/updatememeberfieldsagenda')?>',
			data: {form:$(this).val()},
			type: "POST",
			dataType: "json",
		}).done(function(reps){
			$.each(reps.all,function(i,x){
				if(i != 0){
					var selected='';
					<?php if(!$form->getObject()->isNew()): ?>
					if(<?php echo $form->getObject()->getElementId() ?>.indexOf(i) != -1 && <?php echo $form->getObject()->getFormId() ?> == $('#agenda_columns_form_id').val()){
						//alert('some alert');
						selected='selected';
					}
					<?php endif; ?>
					$('#agenda_columns_element_id').append('<option value="'+i+'" '+selected+'>'+x+'</option>');
				}
			});
		}).fail(function(xhr,status,errorThrown){
			//alert( "Sorry, there was a problem!" );
			console.log( "Error: " + errorThrown );
			console.log( "Status: " + status );
			console.dir( xhr );
		});
	});
$('#agenda_columns_element_id').change(function(e){
	$.each($(this).val(),function(i,x){
		if($('#'+x).length == 0){
			$.ajax({
				url: '<?php echo url_for('/plan/usercategories/updatememeberfieldsagenda')?>',
				data: {form:$('#agenda_columns_form_id').val()},
				type: "POST",
				dataType: "json",
			}).done(function(reps){
				if(reps.all[x] !== undefined){
					$('#sortable').append('<li id="'+x+'" style="background:#f5f5f0; width:50%;">'+reps.all[x]+'&nbsp;<a href="#" onClick="$(this).parent().remove();return false;" style="float:right">x</a></li>');
				}
				
			});
		}
	});
});
$('#agenda_columns_entry_column').change(function(e){
	$.each($(this).val(),function(i,x){
		if($('#'+x).length == 0){
			$.ajax({
				url: '<?php echo url_for('/plan/agenda/agendaapparray') ?>',
				type: "GET",
				dataType: 'json',
			}).done(function(resp){
				if($('#'+x).length == 0){
					$('#sortable').append('<li id="'+x+'" style="background:#f5f5f0; width:50%;">'+resp[x]+'&nbsp;<a href="#" onClick="$(this).parent().remove();return false;" style="float:right">x</a></li>');
				}
			}).fail(function(xhr,status,errorThrown){
				//alert( "Sorry, there was a problem!" );
				console.log( "Error: " + errorThrown );
				console.log( "Status: " + status );
				console.dir( xhr );
			});
		}
	});
});
<?php if(!$form->getObject()->isNew()): ?>
$('#agenda_columns_form_id').trigger('change');
$('#agenda_columns_entry_column').children('option').each(function(i,e){
	if(<?php echo $form->getObject()->getEntryColumn() ?>.indexOf($(e).attr('value')) != -1){
		$(e).prop('selected',true);
	}
});
var positions=<?php echo $form->getObject()->getPosition() ?>;
if(positions.length > 0){
	$.ajax({
		url: '<?php echo url_for('/plan/agenda/agendaarrays') ?>',
		type: "POST",
		dataType: 'json',
		data: {form: $('#agenda_columns_form_id').val()}
	}).done(function(resp){
		$.each(positions,function(i,x){
			$('#sortable').append('<li id="'+x+'" style="background:#f5f5f0; width:50%;">'+resp[x]+'&nbsp;<a href="#" onClick="$(this).parent().remove();return false;" style="float:right">x</a></li>');
		});
		
	}).fail(function(xhr,status,errorThrown){
		//alert( "Sorry, there was a problem!" );
		console.log( "Error: " + errorThrown );
		console.log( "Status: " + status );
		console.dir( xhr );
	});
}
<?php endif; ?>
    $( "#sortable" ).sortable({
      revert: true
    });
	$( "#sortable" ).disableSelection();
	$('form').submit(function(e){
		var position=[];
		$('#sortable').children('li').each(function(i,x){
			if($('#agenda_columns_element_id').val().indexOf($(x).attr('id')) != -1 || $('#agenda_columns_entry_column').val().indexOf($(x).attr('id')) != -1){
				position.push($(x).attr('id')) ;
			}
		});
		//console.log(JSON.stringify(position));
		if(position.length){
			$('#agenda_columns_position').val(JSON.stringify(position));
		}
	});
});
</script>
