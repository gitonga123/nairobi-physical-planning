<?php
/**
 * viewSuccess.php template.
 *
 * Displays business profile
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

?>
<div class="pageheader">
  <h2><i class="fa fa-envelope"></i> <?php echo __('Users'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/backend.php"><?php echo __('Home'); ?></a></li>
      <li><a href="/backend.php/frusers/index"><?php echo __('Users'); ?></a></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
    <div class="row">

		<div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title">Transfer <?php echo $business->getTitle(); ?> to another user</h3>

            <div class="pull-right">
                <a class="btn btn-primary" id="newpage" href="/backend.php/profiles/index/filter/<?php echo $business->getFormId(); ?>" ><?php echo __('Back to list'); ?></a>
            </div>
        </div>

		<div class="panel-body" style="margin: 0px; padding: 0px;">

        <form method="post" action="#" class="form-bordered">

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Enter email address of the user'); ?></label><br>
                <div class="col-sm-12">
                    <input type="text" name="email" id="email" class="form-control">
                </div>
            </div>

            <div class="form-group" style="padding: 10px;">
                <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
            </div>
        </form>

		</div>

    </div>
</div>