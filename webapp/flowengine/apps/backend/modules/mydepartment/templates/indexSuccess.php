<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managereviewers"))
{
?>
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

?>
<div class="pageheader">
    <h2><i class="fa fa-user"></i> <?php echo __('My Department'); ?> <span><?php echo __('List of reviewers in your department'); ?></span></h2>
  <div class="breadcrumb-wrapper" style="margin-top: 10px;">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/backend.php"><?php echo __('Home'); ?></a></li>
      <li class="active"><?php echo __('My Department'); ?></li>
    </ol>
  </div>
</div>


<div class="contentpanel">



  <div class="row">
      <?php
      if($sf_user->hasFlash('notice'))
      {
          ?>
          <div class="alert alert-success">
              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
              <?php echo $sf_user->getFlash('notice',ESC_RAW); ?>.
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

  <div class="panel-body">
      <div class="table-responsive">
          <?php if ($pager->getResults()): ?>
          <table class="table b-b-0">
              <thead class="form-horizontal">
              <tr>
                  <form method="post" action="/backend.php/mydepartment/index/filter/<?php echo $filter; ?><?php if($filterstatus != ""){ echo "/filterstatus/".$filterstatus; } ?>">

                    <th class=" b-b-0" style="width:60%;">
                            <input name="search" value="<?php echo $filter; ?>" type="text" class="form-control p10">
                    </th>

                    <th  class="b-b-0" class="border-bottom-1" style="width:33%;">
                        <input name="filter_date" id="filter_date" value="<?php echo $filter_date; ?>" type="text" class="form-control p10 pull-left" style="width: 290px;">
                        <button type="submit" class="btn btn-primary pull-right" style="margin-top: 3px;">GO</button>
                    </th>
                  </form>
              </tr>
              </thead>
          </table>

          <table  class="table table-striped table-hover table-special">
              <thead>
              <tr class="main-tr">
                  <th class="b-b-0">#</th>
                  <th class="b-b-0"><?php echo __('Full Name'); ?></th>
                  <th class="b-b-0"><?php echo __('Email Address'); ?></th>
                  <th class="b-b-0"><?php echo __('Username'); ?></th>
                  <th class="b-b-0" width="130"><?php echo __('Tasks Done Today'); ?></th>
                  <th class="text-right b-b-0"><?php echo __('Action'); ?></th>
              </tr>
              </thead>
              <tbody>
              <?php
              $count = 0;

              if($pager->getPage() > 1)
              {
                  $count = 10 * ($pager->getPage() - 1);
              }

              foreach($pager->getResults() as $reviewer)
              {
                $count++;

                $q = Doctrine_Query::create()
                   ->from("Task a")
                   ->where("a.owner_user_id = ?", $reviewer->getNid())
                   ->andWhere("a.status = 25")
                   ->andWhere("a.end_date LIKE ?", "%".$filter_date."%");
                $tasks = $q->count();
                ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td><?php echo strtoupper($reviewer->getStrfirstname()." ".$reviewer->getStrlastname()); ?></td>
                    <td><?php echo $reviewer->getStremail(); ?></td>
                    <td><?php echo $reviewer->getStruserid(); ?></td>
                    <td align="center">
                    <span class="label label-success"><?php echo $tasks; ?></span>
                    </td>
                    <td class="aligncenter">
                        <a title="View Reviewer" href="/backend.php/mydepartment/viewuser/userid/<?php echo $reviewer->getNid(); ?>"><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
                        <a title="View Reviewer" href="/backend.php/users/viewuser/userid/<?php echo $reviewer->getNid(); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
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

                          <?php if ($pager->haveToPaginate()): ?>
                              - <?php echo __('page'); ?> <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
                          <?php endif; ?></p>


                      <?php if ($pager->haveToPaginate()): ?>
                          <ul class="pagination pagination-sm mb0 mt0 pull-right">
                              <li><a href="/backend.php/mydepartment/index/page/1<?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>">
                                      <i class="fa fa-angle-left"></i>
                                  </a></li>

                              <li><a href="/backend.php/mydepartment/index/page/<?php echo $pager->getPreviousPage() ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>">
                                      <i class="fa fa-angle-left"></i>
                                  </a></li>

                              <?php foreach ($pager->getLinks() as $page): ?>
                                  <?php if ($page == $pager->getPage()): ?>
                                      <li class="active"><a href=""><?php echo $page ?></li></a>
                                  <?php else: ?>
                                      <li><a href="/backend.php/mydepartment/index/page/<?php echo $page ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>"><?php echo $page ?></a></li>
                                  <?php endif; ?>
                              <?php endforeach; ?>

                              <li><a href="/backend.php/mydepartment/index/page/<?php echo $pager->getNextPage() ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>">
                                      <i class="fa fa-angle-right"></i>
                                  </a></li>

                              <li><a href="/backend.php/mydepartment/index/page/<?php echo $pager->getLastPage() ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>">
                                      <i class="fa fa-angle-right"></i>
                                  </a>
                              </li>
                          </ul>
                      <?php endif; ?>
                  </th>
              </tr>
              </tfoot>
          </table>
      </div><!-- table-responsive -->
      <?php else: ?>
          <div class="table-responsive">
              <table class="table dt-on-steroids mb0">
                  <tbody>
                  <tr><td>
                          <?php echo __('No Records Found'); ?>
                      </td></tr>
                  </tbody>
              </table>
          </div>
      <?php endif; ?>
  </div>

</div>
<script>
jQuery(document).ready(function(){
	// Date Picker
	jQuery('#filter_date').datepicker();
});
</script>
<?php
}
else
{
  include_partial("accessdenied");
}
?>
