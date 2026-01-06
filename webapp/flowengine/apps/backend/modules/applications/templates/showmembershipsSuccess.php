<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed memberships");
?>

<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Professional Bodies Membership'); ?></h3>
		</div>
		<div class="panel-heading text-right">
                        <a class="btn btn-primary settings-margin42" id="newpage" href="<?php echo url_for('/backend.php/forms/form') ?>" ><?php echo __('New Membership Database'); ?></a>
        </div>

        <div class="panel-group panel-group" id="accordion2">
            <?php
			$count=0;
            foreach($apforms as $form):
                $count++;
                ?>
                <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne<?php echo $count; ?>" class="collapsed">
                        <?php echo $count; ?>:  <?php echo $form->getFormName(); ?>
                    </a>
                    </h4>
                </div>
                <div id="collapseOne<?php echo $count; ?>" class="panel-collapse collapse">
                    <div class="panel-body" style="padding: 20px;">
                        <a class="btn btn-primary btn-lg btn-form" style="margin-right: 10px;" href="<?php echo url_for('/backend.php/applications/showentries?form_id='.$form->getFormId()); ?>"><span class="fa fa-eye"></span> View</a>
                        <a onClick="if(confirm('Are you sure?')){ return true; }else{ return false; }" class="btn btn-danger btn-lg btn-outputs pull-right" style="margin-right: 10px;" href="<?php echo url_for('/backend.php/forms/deleteform?id='.$form->getFormId()); ?>"><span class="fa fa-trash-o"></span> Delete</a>
                    </div>
                </div>
                </div>
                <?php
            endforeach;
            ?>
          </div>
    </div>
</div>
