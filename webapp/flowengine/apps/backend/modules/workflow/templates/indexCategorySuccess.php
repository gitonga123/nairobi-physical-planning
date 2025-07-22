<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed Services Categories");
?>

<div class="contentpanel panel-email">
    <div class="panel panel-dark">

        <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Service Categories'); ?></h3>

        </div>
		<div class="pull-heading">
			<a class="btn btn-primary settings-margin42" id="newpage" href="<?php echo url_for('/plan/workflow/newCategory') ?>" ><?php echo __('New Service Category'); ?></a>
		</div>
        <div class="panel-group panel-group" id="accordion2">
		<?php $notice=$sf_user->getAttribute('delete_cat_notice',''); 
			if(strlen($notice)):
		?>
			<div class="alert alert-success">
				<?php echo $notice ?>
			</div>
		<?php 
		endif;
		$sf_user->getAttributeHolder()->remove('delete_cat_notice');
		?>
            <?php
            $count = 0;
            foreach($services as $service):
                $count++;
                ?>
                <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne<?php echo $count; ?>" class="collapsed">
                        <?php echo $count; ?>:  <?php echo $service->getTitle(); ?>
                    </a>
                    </h4>
                </div>
                <div id="collapseOne<?php echo $count; ?>" class="panel-collapse collapse">
                    <div class="panel-body" style="padding: 20px;">
                        <a class="btn btn-primary btn-lg btn-form" style="margin-right: 10px;" href="<?php echo url_for('/plan/workflow/editCategory/id/'.$service->getId()); ?>"><span class="fa fa-edit"></span> Edit</a>
                        <a onClick="if(confirm('Are you sure?')){ return true; }else{ return false; }" class="btn btn-danger btn-lg btn-outputs pull-right" style="margin-right: 10px;" href="<?php echo url_for('/plan/workflow/deleteCategory/id/'.$service->getId()); ?>"><span class="fa fa-trash-o"></span> Delete</a>
                    </div>
                </div>
                </div>
                <?php
            endforeach;
            ?>
          </div>
    </div>
</div>
