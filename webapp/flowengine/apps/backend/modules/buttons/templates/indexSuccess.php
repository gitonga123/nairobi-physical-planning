<?php

use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed actions settings");

if($sf_user->mfHasCredential("manageactions"))
{
  $_SESSION['current_module'] = "actions";
  $_SESSION['current_action'] = "index";
  $_SESSION['current_id'] = "";
?>
<table class="table dt-on-steroids mb0" id="table3">
    <thead>
   <tr>
      <th width="60">#</th>
      <th><?php echo __('Title'); ?></th>
      <th width="7%"><?php echo __('Actions'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php
		$count = 1;
	?>
    <?php foreach ($buttonss as $buttons): ?>
    <tr id="row_<?php echo $buttons->getId() ?>">
	    <td><?php echo $count++; ?></td>
      <td><?php echo $buttons->getTitle() ?></td>
      <td>
		<a id="editaction<?php echo $buttons->getId(); ?>" href="#" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
		<a id="deleteaction<?php echo $buttons->getId(); ?>" href="#" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>

    <script language="javascript">
    jQuery(document).ready(function(){
      $( "#editaction<?php echo $buttons->getId(); ?>" ).click(function() {
          $("#loadinner").load("/backend.php/buttons/edit/id/<?php echo $buttons->getId(); ?>/filter/<?php echo $filter; ?>");
      });
      $( "#deleteaction<?php echo $buttons->getId(); ?>" ).click(function() {
        if(confirm('Are you sure you want to delete this button?')){
          $("#loadinner").load("/backend.php/buttons/delete/id/<?php echo $buttons->getId(); ?>/filter/<?php echo $filter; ?>");
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
</div><!--panel-body-->
</div><!--panel-default-->

<?php
}
else
{
  include_partial("accessdenied");
}
?>
