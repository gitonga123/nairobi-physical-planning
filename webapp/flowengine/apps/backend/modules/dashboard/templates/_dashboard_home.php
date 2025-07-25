<?php

/**
 * _dashboard_home.php partial.
 *
 * Displays basic overrall stats as well as a panel for available and pending tasks
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');
?>
<div class="pageheader">
  <h2><i class="fa fa-home"></i> <?php echo __('Dashboard'); ?> <span><?php echo __('Summary of site activities'); ?></span></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="<?php echo public_path("plan"); ?>"><?php echo __('Home'); ?></a></li>
      <li class="active"><?php echo __('Dashboard'); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">

  <div class="row">

    <!-- Statistics on number of applications that have submitted today -->
    <a href="/plan/dashboard/index/current/queued">
      <div class="col-sm-6 col-md-3">
        <div class="panel panel-primary panel-stat panel-mytasks">
          <div class="panel-heading">
            <div class="stat">
              <div class="row">
                <div class="col-xs-8">
                  <small class="stat-label">My Tasks</small>
                  <h1><?php echo $my_tasks; ?></h1>
                </div>
                <div class="col-xs-4 stats-icon">
                  <span class="fa fa-clock-o" style="color:#81C14B"></span>
                </div>
              </div><!-- row -->
            </div><!-- stat -->
          </div><!-- panel-heading -->
        </div><!-- panel -->
      </div><!-- col-sm-6 -->
    </a>
    <a href="/plan/dashboard/index/current/completed">
      <!-- Statistics on number of tasks that have issued today -->
      <div class="col-sm-6 col-md-3">
        <div class="panel panel-primary panel-stat panel-completed">
          <div class="panel-heading">
            <div class="stat">
              <div class="row">
                <div class="col-xs-8">
                  <small class="stat-label">Completed Tasks</small>
                  <h1><?php echo $completed_tasks; ?></h1>
                </div>
                <div class="col-xs-4 stats-icon">
                  <span class="fa fa-history" style="color:#D63AF9"></span>
                </div>
              </div><!-- row -->
            </div><!-- stat -->

          </div><!-- panel-heading -->
        </div><!-- panel -->
      </div><!-- col-sm-6 -->
    </a>
    <a href="/plan/dashboard/index/current/messages">
      <!-- Statistics on number of invoices that have issued today -->
      <div class="col-sm-6 col-md-3">
        <div class="panel panel-primary panel-stat panel-messages">
          <div class="panel-heading">
            <div class="stat">
              <div class="row">
                <div class="col-xs-8">
                  <small class="stat-label">New Messages</small>
                  <h1><?php echo $new_messages; ?></h1>
                </div>
                <div class="col-xs-4 stats-icon">
                  <span class="fa fa-comments" style="color:#2F1847"></span>
                </div>
              </div><!-- row -->
            </div><!-- stat -->
          </div><!-- panel-heading -->
        </div><!-- panel -->
      </div><!-- col-sm-6 -->
    </a>
    <!-- Items in signing queue -->
    <div class="col-sm-6 col-md-3">
      <div class="panel panel-primary panel-stat panel-messages">
        <div class="panel-heading">
          <div class="stat">
            <div class="row">
              <div class="col-xs-8">
                <small class="stat-label">Signing Items</small>
                <h1><?php echo $signing_tasks; ?></h1>
              </div>
              <div class="col-xs-4 stats-icon">
                <span class="fa fa-pencil" style="color:#9d70b6"></span>
              </div>
            </div><!-- row -->
          </div><!-- stat -->
        </div><!-- panel-heading -->
      </div><!-- panel -->
    </div><!-- col-sm-6 -->
  </div>

  <div class="panel-body">
    <?php if ($signing_tasks) : ?>
      <?php include_partial('signing/signer', ['signing_tasks' => $signing_tasks]); ?>
    <?php endif; ?>
  </div>


  <div class="row">

    <div class="col-sm-12">

      <div class="panel with-nav-tabs panel-default">
        <div class="panel-heading">
          <ul class="nav nav-tabs">
            <li <?php if ($current_tab == "available") { ?>class="active" <?php } ?>><a href="/plan/dashboard"><span class="fa fa-bars"></span> <?php echo __("Applications"); ?></a></li>

            <?php
            //OTB HIDE SBP FUNCTIONALITY
            if ($sf_user->mfHasCredential("can_inspect") && Functions::client_can_add_businesses()) {
            ?>
              <li <?php if ($current_tab == "inspections") { ?>class="active" <?php } ?>><a href="/plan/dashboard/index/current/inspections"><span class="fa fa-bars"></span> <?php echo __("Available Inspections"); ?></a></li>
            <?php
            }
            ?>

            <li <?php if ($current_tab == "queued") { ?>class="active" <?php } ?>><a href="/plan/dashboard/index/current/queued"><span class="fa  fa-inbox"></span> <?php echo __("My Tasks"); ?></a></li>
            <li <?php if ($current_tab == "completed") { ?>class="active" <?php } ?>><a href="/plan/dashboard/index/current/completed"><span class="fa fa-clock-o"></span> <?php echo __("Completed Tasks"); ?></a></li>
            <li <?php if ($current_tab == "messages") { ?>class="active" <?php } ?>><a href="/plan/dashboard/index/current/messages"><span class="fa fa-comments"></span> <?php echo __("New Messages"); ?></a></li>
          </ul>
        </div>
        <div class="panel-body">
          <div class="tab-content">
            <?php if ($current_tab == "available") { ?>
              <div class="tab-pane fade in active" id="available">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("List of Applications"); ?></h3>
                    <div class="pull-right" style="margin-top: -30px;">
                      <select onChange="window.location='/plan/dashboard/index/current/available/filter/' + this.value;" class="form-control">
                        <option><?php echo __('Filter'); ?>...</option>
                        <?php
                        $categories = Functions::get_allowed_category_services();
                        foreach ($categories as $category) {
                          echo "<optgroup label='" . $category->getTitle() . "'>";
                          foreach ($category->getMenus() as $service) {
                            if ($sf_user->mfHasCredential("accessmenu" . $service->getId())) {
                              echo "<optgroup label='" . $service->getTitle() . "'>";

                              $stages = Functions::get_allowed_stage_models($service->getId());

                              foreach ($stages as $stage) {
                                $selected = "";

                                if ("/filter/" . $stage->getId() == $filter) {
                                  $selected = "selected='selected'";
                                  $stage_id = $stage->getId();
                                  $stage_type = $stage->getStageType();
                                }

                                echo "<option value='" . $stage->getId() . "' " . $selected . ">" . $stage->getTitle() . " (" . $stage->getNoOfApplcations() . ") </option>";
                              }

                              echo "</optgroup>";
                            }
                          }
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="panel-body p-0">
                    <?php if ($stage_id && $stage_type && $stage_type == 11) : ?>
                      <div>
                        <p class="text-muted"><a href="<?php echo url_for('/plan/agenda/showbystage?stage=' . $stage_id) ?>" class="btn btn-primary">View agenda</a></p>
                      </div>
                    <?php endif; ?>
                    <?php include_partial('list_available_tasks', array('current_paginator' => $current_paginator, 'page' => $page, 'filter' => $filter)) ?>
                  </div>
                </div>
              </div>
            <?php } ?>
            <?php if ($current_tab == "inspections") { ?>
              <div class="tab-pane fade in active" id="available">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("List of inspections available for you to work on"); ?></h3>
                  </div>
                  <div class="panel-body p-0">
                    <?php include_partial('list_available_inspections', array('current_paginator' => $current_paginator, 'page' => $page)) ?>
                  </div>
                </div>
              </div>
            <?php } ?>
            <?php if ($current_tab == "queued") { ?>
              <div class="tab-pane fade in active" id="available">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("List of tasks you are currently working on"); ?></h3>
                  </div>
                  <div class="panel-body p-0">
                    <?php include_partial('list_my_tasks', array('current_paginator' => $current_paginator, 'page' => $page)) ?>
                  </div>
                </div>
              </div>
            <?php } ?>
            <?php if ($current_tab == "completed") { ?>
              <div class="tab-pane fade in active" id="available">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("List of tasks you have completed"); ?></h3>
                  </div>
                  <div class="panel-body p-0">
                    <?php include_partial('list_completed_tasks', array('current_paginator' => $current_paginator, 'page' => $page)) ?>
                  </div>
                </div>
              </div>
            <?php } ?>
            <?php if ($current_tab == "messages") { ?>
              <div class="tab-pane fade in active" id="available">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __("List of new messages sent to you"); ?></h3>
                  </div>
                  <div class="panel-body p-0">
                    <?php include_partial('list_new_messages', array('current_paginator' => $current_paginator, 'page' => $page)) ?>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>

    </div>

  </div>
</div><!-- contentpanel -->