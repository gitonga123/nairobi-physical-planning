<?php

/**
 * indexSuccess.php template.
 *
 * Displays list of reviewer departments
 *
 * @package    backend
 * @subpackage users
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

if ($sf_user->mfHasCredential("access_reviewers") || $sf_user->mfHasCredential("has_hod_access")) {
?>

  <div class="pageheader">
    <h2><i class="fa fa-user"></i> <?php echo __('Reviewers'); ?> <span>List of backend reviewers</span></h2>
    <div class="breadcrumb-wrapper" style="margin-top: 10px;">
      <span class="label"><?php echo __('You are here'); ?>:</span>
      <ol class="breadcrumb">
        <li><a href="/backend.php"><?php echo __('Home'); ?></a></li>
        <li class="active"><?php echo __('Reviewers'); ?></li>
      </ol>
    </div>
  </div>


  <div class="contentpanel">

    <div class="row">
      <?php
      if ($sf_user->hasFlash('notice')) {
      ?>
        <div class="alert alert-success">
          <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
          <?php echo $sf_user->getFlash('notice', ESC_RAW); ?>.
        </div>
      <?php
      }
      ?>

      <div class="alert alert-success" id="notifications" name="notifications" style="display: none;">
        <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
        <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this user'); ?>.
      </div>

    </div>


    <div class="panel panel-default">

      <div class="panel-heading">
        <h3 class="panel-title">Review
        </h3> Manage reviewers
      </div>

      <div class="panel-heading text-right">
        <?php
        if ($sf_user->mfHasCredential("access_reviewers")) {
          if ($department_filter) {
        ?>

            <a href="/backend.php/users/edituser" class="btn btn-primary tooltips table-btn" data-original-title="New Reviewer" data-toggle="tooltip"> <span class="hidden-xs">+ <?php echo __('Add Reviewer'); ?></span></a>


          <?php
          } else {
          ?>

            <a href="/backend.php/department/new" class="btn btn-primary tooltips table-btn" data-original-title="New Department" data-toggle="tooltip"> <span class="hidden-xs">+ <?php echo __('Add Department'); ?></span></a>


        <?php
          }
        }
        ?>

      </div>

      <div class="panel-heading text-right">
        <table class="table b-b-0 m-b-0">
          <thead class="form-horizontal">
            <tr>

              <form method="post" action="#">


                <th class="b-b-0" style="width:50%;">
                  <input name="search" value="<?php echo $filter; ?>" type="text" class="form-control p10">
                </th>

                <th class="b-b-0 radius-tr">
                  <select size="1" name="filter_status" aria-controls="table2" class="select2 form-control" onChange="window.location='/backend.php/users/index<?php if ($department_filter) {
                                                                                                                                                                  echo "/department_filter/" . $department_filter;
                                                                                                                                                                } ?>/filterstatus/' + this.value;">
                    <option value="1"><?php echo __('Select Status'); ?></option>
                    <option value="0" <?php if ($filterstatus == "0") {
                                        echo "selected='selected'";
                                      } ?>><?php echo __('Active'); ?>
                    </option>
                    <option value="1" <?php if ($filterstatus == "1") {
                                        echo "selected='selected'";
                                      } ?>><?php echo __('Inactive'); ?>
                    </option>
                  </select>
                </th>
              </form>
            </tr>
          </thead>
        </table>

      </div>

      <div class="panel-body">
        <div class="table-responsive">


          <?php
          if ($department_filter == false && empty($filter)) {
          ?>
            <table class="table table-striped table-hover table-special">
              <thead>
                <tr class="main-tr">
                  <th class="b-b-0" width="60px">#</th>
                  <th class="b-b-0"><?php echo __('Department Name'); ?></th>
                  <th class="b-b-0"><?php echo __('Users'); ?></th>
                  <th class="text-right b-b-0"><?php echo __('Action'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($departments as $department) {
                  $q = Doctrine_Query::create()
                    ->from("CfUser a")
                    ->where("a.strdepartment = ?", $department->getId())
                    ->andWhere("a.bdeleted = 0");
                  $stats = $q->count();
                ?>
                  <tr>
                    <td><?php echo $department->getId(); ?></td>
                    <td><?php echo $department->getDepartmentName(); ?></td>
                    <td><span class="label label-primary"><?php echo $stats; ?></span></td>
                    <td class="text-right">
                      <?php
                      if ($sf_user->mfHasCredential("access_reviewers")) {
                      ?>
                        <a title="View Reviewer" href="/backend.php/users/index/department_filter/<?php echo $department->getId(); ?>"><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
                        <a title="Edit Reviewer" href="/backend.php/department/edit/id/<?php echo $department->getId(); ?>"><span class="label label-primary"><i class="fa fa-edit"></i></span></a>
                      <?php
                      }
                      ?>
                    </td>
                  </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
          <?php
          } else {
          ?>
            <table class="table table-striped table-hover table-special">
              <thead>
                <?php
                if ($department) {
                ?>
                  <tr>
                    <th colspan="7" style="text-align: left;">
                      <h4 style="font-size:16px;margin:0; padding:10px 0;text-transform:uppercase"><?php echo $department->getDepartmentName(); ?></h4>
                    </th>
                  </tr>
                <?php
                }
                ?>
                <tr class="main-tr">
                  <th class="b-b-0">#</th>
                  <th class="b-b-0"><?php echo __('Full Name'); ?></th>
                  <th class="b-b-0"><?php echo __('Email Address'); ?></th>
                  <th class="b-b-0"><?php echo __('Username'); ?></th>
                  <th class="b-b-0"><?php echo __('Status'); ?></th>
                  <th class="b-b-0"><?php echo __('Current Tasks'); ?></th>
                  <th class="text-right b-b-0"><?php echo __('Action'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $count = 0;

                if ($pager->getPage() > 1) {
                  $count = 10 * ($pager->getPage() - 1);
                }

                foreach ($pager->getResults() as $reviewer) {
                  $count++;
                ?>
                  <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo strtoupper($reviewer->getStrfirstname() . " " . $reviewer->getStrlastname()); ?></td>
                    <td><?php echo $reviewer->getStremail(); ?></td>
                    <td><?php echo $reviewer->getStruserid(); ?></td>
                    <?php
                    if ($reviewer->getBdeleted() == 1) {
                    ?>
                      <td>
                        <span class="label label-danger"><?php echo __('Inactive'); ?></span>
                      </td>
                    <?php
                    } else {
                    ?>
                      <td>
                        <span class="label label-success"><?php echo __('Active'); ?></span>
                      </td>
                    <?php
                    }

                    $q = Doctrine_Query::create()
                      ->from("Task a")
                      ->where("a.owner_user_id = ?", $reviewer->getNid())
                      ->andWhere("a.status = 1 OR a.status = 2");
                    $stats = $q->count();
                    ?>
                    <td><span class="label label-primary"><?php echo $stats; ?></span></td>

                    <td class="text-right">
                      <a title="View Reviewer" href="/backend.php/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>"><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
                      <?php
                      if ($sf_user->mfHasCredential("access_reviewers")) {
                        if ($reviewer->getBdeleted() == 1) {
                      ?>
                          <a title="Activate Reviewer" onclick="if(confirm('Are you sure you want to restore this user?')){ return true; }else{ return false; }" href="/backend.php/users/restore/id/<?php echo $reviewer->getNid(); ?>"><span class="label label-primary"><i class="fa fa-check-circle"></i></span></a>
                        <?php
                        } else {
                        ?>
                          <a title="Deactivate Reviewer" onclick="if(confirm('Are you sure you want to deactivate this user?')){ return true; }else{ return false; }" href="/backend.php/users/delete/id/<?php echo $reviewer->getNid(); ?>"><span class="label label-danger"><i class="fa fa-times" aria-hidden="true"></i></span></a>
                      <?php
                        }
                      }
                      ?>
                    </td>
                  </tr>
                <?php
                }
                ?>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="12">
                    <p class="table-showing pull-left"><strong><?php echo count($pager) ?></strong> <?php echo __('Reviewers'); ?>

                      <?php if ($pager->haveToPaginate()) : ?>
                        - page <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
                      <?php endif; ?></p>

                    <?php
                    $translation = new Translation();
                    ?>
                    <?php if ($pager->haveToPaginate()) : ?>
                      <ul class="pagination pagination-sm mb0 mt0 pull-right">
                        <li><a href="/backend.php/users/index/page/1<?php if ($filter) {
                                                                      echo "/filter/" . $filter;
                                                                    } ?><?php if ($department_filter) {
                                                                                                                      echo "/department_filter/" . $department_filter;
                                                                                                                    } ?><?php if ($fromdate) {
                                                                                                                                                                                                        echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                                                                                                      } ?>">
                            <?php
                            if ($translation->IsLeftAligned()) {
                            ?>
                              <i class="fa fa-angle-left"></i>
                            <?php
                            } else {
                            ?>
                              <i class="fa fa-angle-right"></i>
                            <?php
                            }
                            ?>
                          </a></li>

                        <li><a href="/backend.php/users/index/page/<?php echo $pager->getPreviousPage() ?><?php if ($department_filter) {
                                                                                                            echo "/department_filter/" . $department_filter;
                                                                                                          } ?><?php if ($filter) {
                                                                                                                                                                                              echo "/filter/" . $filter;
                                                                                                                                                                                            } ?><?php if ($fromdate) {
                                                                                                                                                                                                                                              echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                                                                                                                                            } ?>">
                            <?php
                            if ($translation->IsLeftAligned()) {
                            ?>
                              <i class="fa fa-angle-left"></i>
                            <?php
                            } else {
                            ?>
                              <i class="fa fa-angle-right"></i>
                            <?php
                            }
                            ?>
                          </a></li>

                        <?php foreach ($pager->getLinks() as $page) : ?>
                          <?php if ($page == $pager->getPage()) : ?>
                            <li class="active"><a href=""><?php echo $page ?></li></a>
                          <?php else : ?>
                            <li><a href="/backend.php/users/index/page/<?php echo $page ?><?php if ($department_filter) {
                                                                                            echo "/department_filter/" . $department_filter;
                                                                                          } ?><?php if ($filter) {
                                                                                                                                                                              echo "/filter/" . $filter;
                                                                                                                                                                            } ?><?php if ($fromdate) {
                                                                                                                                                                                                                              echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                                                                                                                            } ?>"><?php echo $page ?></a></li>
                          <?php endif; ?>
                        <?php endforeach; ?>

                        <li><a href="/backend.php/users/index/page/<?php echo $pager->getNextPage() ?><?php if ($department_filter) {
                                                                                                        echo "/department_filter/" . $department_filter;
                                                                                                      } ?><?php if ($filter) {
                                                                                                                                                                                          echo "/filter/" . $filter;
                                                                                                                                                                                        } ?><?php if ($fromdate) {
                                                                                                                                                                                                                                          echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                                                                                                                                        } ?>">
                            <?php
                            if ($translation->IsLeftAligned()) {
                            ?>
                              <i class="fa fa-angle-right"></i>
                            <?php
                            } else {
                            ?>
                              <i class="fa fa-angle-left"></i>
                            <?php
                            }
                            ?>
                          </a></li>

                        <li><a href="/backend.php/users/index/page/<?php echo $pager->getLastPage() ?><?php if ($department_filter) {
                                                                                                        echo "/department_filter/" . $department_filter;
                                                                                                      } ?><?php if ($filter) {
                                                                                                                                                                                          echo "/filter/" . $filter;
                                                                                                                                                                                        } ?><?php if ($fromdate) {
                                                                                                                                                                                                                                          echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                                                                                                                                        } ?>">
                            <?php
                            if ($translation->IsLeftAligned()) {
                            ?>
                              <i class="fa fa-angle-right"></i>
                            <?php
                            } else {
                            ?>
                              <i class="fa fa-angle-left"></i>
                            <?php
                            }
                            ?>
                          </a>
                        </li>
                      </ul>
                    <?php endif; ?>
                  </th>
                </tr>
              </tfoot>
            </table>
          <?php
          }
          ?>
        </div><!-- table-responsive -->
      </div>

    </div>
  <?php
} else {
  include_partial("accessdenied");
}
  ?>