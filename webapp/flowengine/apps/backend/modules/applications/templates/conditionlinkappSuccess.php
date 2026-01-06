<?php
/**
 * showSuccess.php template.
 *
 * Displays full client details
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="pageheader">
  <h2><i class="fa fa-envelope"></i> <?php echo __('Condition of Approval'); ?></h2>
</div>

<div class="contentpanel">
<div class="row">
<form action="<?php echo url_for('/backend.php/applications/conditionlinkapp') ?>" method="post" class="form-bordered form-horizontal">
<?php if($form->hasGlobalErrors()): ?>
<div class="alert alert-warning" >
	<?php echo $form->renderGlobalErrors() ?>
</div>
<?php endif; ?>
<div class="form-group">
<div class="col-sm-8 rogue-input">
<p>Permit: <?php echo $form['permit_id']->render() ?> <span><?php echo $form['permit_id']->renderError() ?></span></p>
</div>
</div>
<div class="form-group">
<div class="col-sm-8 rogue-input">
<p>Short Name: <?php echo $form['short_name']->render() ?> <span><?php echo $form['description']->renderError() ?></span></p>
</div>
</div>
<div class="form-group">
<div class="col-sm-8 rogue-input">
<p>Description: <?php echo $form['description']->render() ?> <span><?php echo $form['description']->renderError() ?></span></p>
</div>
</div>
<div class="form-group">
<div class="col-sm-8 rogue-input">
<p>Department: <?php echo $form['department_id']->render() ?> <span><?php echo $form['department_id']->renderError() ?></span></p>
</div>
</div>
<?php echo $form->renderHiddenFields() ?>
<div class="form-group">
<div class="col-sm-8 rogue-input">
<p><input type="submit" value="Submit" class="btn btn-primary"/></p>
</div>
</div>
</form>

</div>
</div>