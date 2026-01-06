<?php
use_helper("I18N");
?>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo $subcounty->getName(); ?></h3>
  </div>

  <div class="panel-heading text-right">
    <a class="btn btn-primary" id="newpage" href="/backend.php/subcounty/index"><?php echo __('Back to List'); ?></a>
  </div>

  <div class="panel-body padding-0">
    <div class="form-group">
      <label class="col-sm-4 control-label"><?php echo __('Sub County'); ?></label><br>
      <div class="col-sm-12">
        <input type="text" name="subcounty[name]" value="<?php echo $subcounty->getName() ?>" id="subcounty_name"
          disabled />
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label"><?php echo __('Wards'); ?></label><br>
      <div class="col-sm-12">
        <ul class="list-group">
          <?php foreach ($subcounty->getWard() as $ward) { ?>
            <li class="list-group-item"><?php echo $ward->getName() ?></li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>
</div>