<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed Workflow Category");
?>
<div class="pageheader">
    <h2><i class="fa fa-home"></i> <?php echo __("Service Category Creation"); ?></h2>
    <div class="breadcrumb-wrapper">
        <span class="label"><?php echo __("You are here"); ?>:</span>
        <ol class="breadcrumb">
            <li><a href="<?php echo public_path("/backend.php/dashboard"); ?>"><?php echo __("Home"); ?></a></li>
            <li class="active"><?php echo __("Workflow Category"); ?></li>
        </ol>
    </div>
</div>
<div class="contentpanel">
    <div class="row">
		    <div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title"><?php echo __("Service Category Creation"); ?></h4>
					</div>
					<div class="panel-body panel-body-nopadding">
						<form class="form-horizontal form-bordered" method="post" action="<?php echo public_path("backend.php/workflow/postCategory/id/".$form->getObject()->getId()); ?>">
							<div>
							<?php echo $form->renderGlobalErrors() ?>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label"><h5>Category Title</h5></label>
								<div class="col-sm-8">
									<?php echo $form['title']->render(array('class' => 'form-control')) ?>
									<span><?php echo $form['title']->renderError() ?></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label"><h5>Category Description</h5></label>
								<div class="col-sm-8">
									<?php echo $form['description']->render(array('class' => 'form-control')) ?>
									<span><?php echo $form['description']->renderError() ?></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label"><h5>Service</h5></label>
								<div class="col-sm-8">
									<select multiple name="service[]" id="service_sel">
										<?php
											$menus=Doctrine_Core::getTable('Menus')->createQuery('m')->execute();
											foreach($menus as $menu):
										?>
											<option value="<?php echo $menu->getId() ?>" <?php if($menu->getCategoryId() == $form->getObject()->getId()):?>selected<?php endif; ?>><?php echo $menu->getTitle() ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<?php echo $form->renderHiddenFields() ?>
							<div class="panel-footer">
								<a href="<?php echo url_for('/backend.php/workflow/indexCategory') ?>" class="btn btn-warning"><?php echo __('Back'); ?></a>
								<input type="submit" class="btn btn-success pull-right" value="<?php echo __("Submit"); ?>" />
							</div>
						</form>
					</div>
				</div>
			</div>
	</div>
</div>
<script>
	$(function(){
		$('#service_sel').bootstrapDualListbox();
		
	});
</script>