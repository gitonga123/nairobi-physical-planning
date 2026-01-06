<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed site config settings");

if($sf_user->mfHasCredential("managewebpages"))
{
?>
<div class="contentpanel panel-email">
<div class="panel panel-default">
<div class="panel-heading">
  <h3 class="panel-title"><?php echo __('Edit Site Configurations'); ?></div>
  <form id="apsettingform" name="apsettingform" class="form-bordered form-horizontal" action="/backend.php/siteconfig/update" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>

    <?php
    if($success == true && $success != null)
    {
    ?>
    <div class="alert alert-success" id="alertdiv" name="alertdiv">
      <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
      <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this record'); ?></a>.
    </div>
    <?php
    }
    elseif($success == false && $success != null)
    {
    ?>
    <div class="alert alert-danger" id="alertdiv" name="alertdiv">
      <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
      <strong><?php echo __('Sorry'); ?>!</strong> <?php echo __('Could not update this record. Try again.'); ?></a>.
    </div>
    <?php
    }
    ?>

    <div class="panel-body padding-0">

      <?php if (!$form->getObject()->isNew()): ?>
        <input type="hidden" name="sf_method" value="post" />
      <?php endif; ?>
      <?php echo $form->renderGlobalErrors() ?>
        <?php if(isset($form['_csrf_token'])): ?>
            <?php echo $form['_csrf_token']->render(); ?>
          <?php endif; ?>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Organisation Name'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['organisation_name']->renderError() ?>
              <?php echo $form['organisation_name'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Organisation Description'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['organisation_description']->renderError() ?>
              <?php echo $form['organisation_description'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Organisation Email'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['organisation_email']->renderError() ?>
              <?php echo $form['organisation_email'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Organisation Logo'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['admin_image_url']->renderError() ?>
              <?php echo $form['admin_image_url'] ?>
              <?php
              if(!$form->getObject()->isNew() && $form->getObject()->getAdminImageUrl())
              {
                  ?>
                  <img src="<?php echo $form->getObject()->getUploadDirWeb().$form->getObject()->getAdminImageUrl(); ?>">
                  <?php
              }
              ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Upload Directory'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['upload_dir']->renderError() ?>
              <?php echo $form['upload_dir'] ?>
            </div>
          </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Uploads Web URL (e.g. http://fileserver/). Must end with a trailing slash /'); ?></i></label>
            <div class="col-sm-8">
                <?php echo $form['upload_dir_web']->renderError() ?>
                <?php echo $form['upload_dir_web'] ?>
            </div>
        </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Data Directory'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['data_dir']->renderError() ?>
              <?php echo $form['data_dir'] ?>
            </div>
          </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Data Web URL (e.g. http://fileserver/). Must end with a trailing slash /'); ?></i></label>
            <div class="col-sm-8">
                <?php echo $form['data_dir_web']->renderError() ?>
                <?php echo $form['data_dir_web'] ?>
            </div>
        </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Help Info'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['organisation_help']->renderError() ?>
              <?php echo $form['organisation_help'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('Sidebar Info'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['organisation_sidebar']->renderError() ?>
              <?php echo $form['organisation_sidebar'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('SMTP Enable'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['smtp_enable']->renderError() ?>
              <?php echo $form['smtp_enable'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('SMTP Host'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['smtp_host']->renderError() ?>
              <?php echo $form['smtp_host'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('SMTP Port'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['smtp_port']->renderError() ?>
              <?php echo $form['smtp_port'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('SMTP Username'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['smtp_username']->renderError() ?>
              <?php echo $form['smtp_username'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('SMTP Password'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['smtp_password']->renderError() ?>
              <?php echo $form['smtp_password'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('SMTP Auth'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['smtp_auth']->renderError() ?>
              <?php echo $form['smtp_auth'] ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="bold-label"><?php echo __('SMTP Secure'); ?></i></label>
            <div class="col-sm-8">
              <?php echo $form['smtp_secure']->renderError() ?>
              <?php echo $form['smtp_secure'] ?>
            </div>
          </div>

        </div><!--panel-body-->

        <div class="panel-footer">
         <button class="btn btn-danger mr10"><?php echo __('Reset'); ?></button><button id="submitbuttonname" type="submit" class="btn btn-primary" name="submitbuttonname" value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
        </div>
    </form>
  </div><!--panel-body-->
</div>
</div>
<?php
}
else
{
  include_partial("settings/accessdenied");
}
?>
