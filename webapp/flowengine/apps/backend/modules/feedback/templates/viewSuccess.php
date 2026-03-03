<?php use_helper('I18N', 'Date') ?>
<div class="pageheader">
  <h2><i class="fa fa-envelope"></i> <?php echo __('Feedback Messages') ?> <span><?php echo __('View Message') ?></span></h2>
  <div class="breadcrumb-wrapper">
    <span class="label">You are here:</span>
    <ol class="breadcrumb">
      <li><a href="<?php echo public_path('plan'); ?>"><?php echo __('Home') ?></a></li>
      <li><a href="<?php echo public_path('plan/feedback/index'); ?>"><?php echo __('View Feedback Messages') ?></a></li>
      <li class="active"><?php echo __('View Message') ?></li>
    </ol>
  </div>
</div>
<div class="contentpanel panel-email">

  <div class="panel panel-dark">
    <div class="panel-body">

      <div class="col-md-8">


        <div class="inbox-content">
          <div class="inbox-header inbox-view-header">
            <h1 class="pull-left"> <?php echo __('Feedback message from client') ?>
            </h1> <br />

          </div>
          <br />
          <?php foreach ($message as $msg) : ?>
            <div class="inbox-view-info">
              <div class="row">
                <div class="col-md-9">
                  <img src="/assets_frontend/images/avatar.png" class="img-circle" style="height: 30px;">
                  <span class="bold">
                    <?php echo $msg['element_1_1'] . " " . $msg['element_1_2'] ?>
                  </span>
                  <span>
                    &#60;<?php echo $msg['element_2'] ?>&#62; </span>

                  <?php echo $msg['date_created'] ?>
                </div>
                <div class="col-md-3 inbox-info-btn">
                  <div class="btn-group">
                    <a href="<?php echo public_path('plan/feedback/index'); ?>"><button class="btn btn-primary blue">
                        <i class="fa fa-inbox"></i> <?php echo __('Feeback Inbox') ?> </button> </a>

                  </div>
                </div>
              </div>
              <div class="card-body mt-3">
                <p> <?php echo $msg['element_4'] ?> </p>
              </div>

            </div>
          <?php endforeach; ?>
        </div>

      </div>
    </div>
  </div>
</div>