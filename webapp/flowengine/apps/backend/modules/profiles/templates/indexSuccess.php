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

if($sf_user->mfHasCredential("manageusers"))
{
?>
<div class="pageheader">
    <h2><i class="fa fa-user"></i> <?php echo $profile_form->getFormName(); ?> <span><?php echo __('List of profiles'); ?></span></h2>
  <div class="breadcrumb-wrapper" style="margin-top: 10px;">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/backend.php"><?php echo __('Home'); ?></a></li>
      <li class="active"><?php echo $profile_form->getFormName(); ?></li>
    </ol>
  </div>
</div>


<div class="contentpanel">

  <div class="row">

  <div class="alert alert-success" id="notifications" name="notifications" style="display: none;">
    <button type="button" class="close" onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;</button>
    <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this user'); ?>.
  </div>

  </div>

   <div class="panel panel-default">
  <div class="panel-body">
  <?php if ($pager->getResults()): ?>
  <table class="table b-b-0">
      <thead class="form-horizontal">
      <tr>
          <form method="post" action="/backend.php/profiles/index/filter/<?php echo $filter; ?><?php if($filterstatus != ""){ echo "/filterstatus/".$filterstatus; } ?>">
              <th class="b-b-0" style="width:100%;" colspan="2">
                      <input name="search" value="<?php echo $search; ?>" placeholder="<?php echo __('Search'); ?>" type="text" class="form-control p10">
              </th>

          </form>
      </tr>
      <tr>
          <form method="post" action="/backend.php/profiles/index/filter/<?php echo $filter; ?><?php if($filterstatus != ""){ echo "/filterstatus/".$filterstatus; } ?>">
              <th class="b-b-0">
                       <?php 
                        $q = Doctrine_Query::create()
                            ->from('ApFormElements a')
                            ->where('a.form_id = ?', $filter)
                            ->andWhere('a.element_status = ?', 1)
                            ->andWhere('a.element_type LIKE ?', '%select%')
                            ->orderBy('a.element_title ASC');
                        $elements = $q->execute();

                        echo "<select name='form_dropdown_fields' id='form_dropdown_fields' class='form-control'>";
                        echo "<option>Choose a dropdown field...</option>";
                        foreach($elements as $element)
                        {
                            echo "<option value='".$element->getElementId()."'>".$element->getElementTitle()."</option>";
                        }
                        echo "</select>";
                        echo '<script language="javascript">
                        jQuery(document).ready(function(){
                            jQuery("#form_dropdown_fields" ).change(function() {
                                var selecteditem = this.value;
                                $.ajax({url:"/backend.php/profiles/getdropdownvaluefields?formid='.$filter.'&elementid=" + selecteditem,success:function(result){
                                    $("#ajaxdropdownvaluefields").html(result);
                                }});
                            });
                        });
                        </script>';
                       ?>
                       <div id='ajaxdropdownvaluefields' name='ajaxdropdownvaluefields'></div>
              </th>

              <th class="b-b-0 radius-tr">
                <select size="1" name="filter_status" aria-controls="table2"
                        class="select2 form-control"
                        onChange="window.location='/backend.php/profiles/index/filter/<?php echo $filter; ?>/filterstatus/' + this.value;">
                    <option value="1"><?php echo __('Select Status'); ?></option>
                    <option value="0" <?php if ($filterstatus == "1") {
                        echo "selected='selected'";
                    } ?>><?php echo __('Active'); ?>
                    </option>
                    <option value="1" <?php if ($filterstatus == "0") {
                        echo "selected='selected'";
                    } ?>><?php echo __('Inactive'); ?>
                    </option>
                </select>
             </th>

          </form>
      </tr>
      </thead>
  </table>
  <div class="table-responsive">
  <table class="table table-striped table-hover mb0 table-special">
  <thead>
  <tr class="main-tr">
      <th class="b-b-0">#</th>
      <th class="b-b-0"><?php echo $profile_form->getFormName(); ?></th>
      <th class="b-b-0"><?php echo __('Created By'); ?></th>
      <th class="b-b-0"><?php echo __('Created On'); ?></th>
      <th class="b-b-0"><?php echo __('Status'); ?></th>
      <th class="b-b-0 aligncenter"><?php echo __('Action'); ?></th>
  </tr>
  </thead>
  <tbody>
  <?php
  $count = 0;

  if($pager->getPage() > 1)
  {
    $count = 10 * ($pager->getPage() - 1);
  }

  foreach($pager->getResults() as $business)
  {
      ?>
      <tr>
        <td><?php echo $business->getId() ?></td>
        <td><?php echo strtoupper($business->getTitle()) ?></td>
        <td><?php echo $business->getUser()->getProfile()->getFullname(); ?></td>
        <td><?php echo $business->getCreatedAt() ?></td>
        <td><?php echo ($business->getDeleted())?"<span class='label label-danger'>Not Active</span>":"<span class='label label-success'>Active</span>"; ?></td>
        <td>
            <a title="<?php echo __('View Business'); ?>" href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>"><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
        </td>
      </tr>
      <?php
  }
  ?>
  </tbody>
  <tfoot>
  <tr>
      <th colspan="12">
          <p class="table-showing pull-left"><strong><?php echo count($pager) ?></strong> <?php echo __('profiles'); ?>

              <?php if ($pager->haveToPaginate()): ?>
                  - <?php echo __('page'); ?> <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
              <?php endif; ?></p>


          <?php if ($pager->haveToPaginate()): ?>
              <ul class="pagination pagination-sm mb0 mt0 pull-right">
                  <li><a href="/backend.php/profiles/index/page/1<?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?><?php if($dropdown){ echo "/dropdown/".$filter_dropdown."/element/".$filter_element; } ?>">
                          <i class="fa fa-angle-left"></i>
                      </a></li>

                  <li><a href="/backend.php/profiles/index/page/<?php echo $pager->getPreviousPage() ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?><?php if($dropdown){ echo "/dropdown/".$filter_dropdown."/element/".$filter_element; } ?>">
                          <i class="fa fa-angle-left"></i>
                      </a></li>

                  <?php foreach ($pager->getLinks() as $page): ?>
                      <?php if ($page == $pager->getPage()): ?>
                          <li class="active"><a href=""><?php echo $page ?></li></a>
                      <?php else: ?>
                          <li><a href="/backend.php/profiles/index/page/<?php echo $page ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?><?php if($dropdown){ echo "/dropdown/".$filter_dropdown."/element/".$filter_element; } ?>"><?php echo $page ?></a></li>
                      <?php endif; ?>
                  <?php endforeach; ?>

                  <li><a href="/backend.php/profiles/index/page/<?php echo $pager->getNextPage() ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?><?php if($dropdown){ echo "/dropdown/".$filter_dropdown."/element/".$filter_element; } ?>">
                          <i class="fa fa-angle-right"></i>
                      </a></li>

                  <li><a href="/backend.php/profiles/index/page/<?php echo $pager->getLastPage() ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?><?php if($dropdown){ echo "/dropdown/".$filter_dropdown."/element/".$filter_element; } ?>">
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


    </div>

    </div><!-- panel -->


</div>

<?php
}
?>
