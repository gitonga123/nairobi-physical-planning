<?php
use_helper("I18N");

if($sf_user->mfHasCredential("managewebpages"))
{
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Web Pages'); ?></h3>
        </div>

        <div class="panel-heading text-right">
                <a class="btn btn-primary" id="newpage" href="/plan/content/new" > <?php echo __('+ Add WebPage'); ?></a>
        </div>
        <div class="panel-body">
        <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort">#</th>
                    <th width="60%"><?php echo __('Menu Title'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Order'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Published'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <?php foreach($contents as $content): ?>
                    <tr id="row_<?php echo $content->getId() ?>">
                        <td><?php echo $content->getId(); ?></td>
                        <td><?php echo $content->getMenuTitle();  ?></td>
                        <td>
                            <a href="/plan/content/orderup/id/<?php echo $content->getId(); ?>"><span class="glyphicon glyphicon-circle-arrow-up"></span></a>
                            <a href="/plan/content/orderdown/id/<?php echo $content->getId(); ?>"><span class="glyphicon glyphicon-circle-arrow-down"></span></a>
                        </td>
                        <td align="center"><a id="publish<?php echo $content->getId(); ?>" href="<?php echo '/plan/content/index?ptoggle='.$content->getId() ?>">
                        <?php
                        if($content->getPublished() == "1")
                        {
                            echo "<span class='badge-round badge-success'><span class='fa fa-check'></span></span>";
                        }
                        else
                        {
                            echo "<span class='badge-round badge-danger'><span class='fa fa-times'></span></span>";
                        }

                        ?>
                        </a></td>
                        <td align="center">
                            <a id="editpage<?php echo $content->getId(); ?>" href="/plan/content/edit/id/<?php echo $content->getId(); ?>" title="<?php echo __('Edit'); ?>"><span class="label label-primary"><i class="fa fa-pencil"></i></span></a>
                            <?php
                            if($content->getPublished() == 0)
                            {
                            ?>
                                <a id="deletepage<?php echo $content->getId(); ?>"  onClick="if(confirm('Are you sure you want to delete this item?')){ return true; }else{ return false; }" href="/plan/content/delete/id/<?php echo $content->getId(); ?>" title="<?php echo __('Delete'); ?>"><span class="label label-danger"><i class="fa fa-trash-o"></i></span></a>
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
      </div>
    </div>
</div>

<script language='javascript'>
  jQuery('#table3').dataTable({
      "sPaginationType": "full_numbers",

      // Using aoColumnDefs
      "aoColumnDefs": [
      	{ "bSortable": false, "aTargets": [ 'no-sort' ] }
    	]
    });

   function updateOrder(pageid, order)
   {
        var xmlHttpReq1 = false;
        var self = this;
        // Mozilla/Safari

        if (window.XMLHttpRequest) {
            self.xmlHttpReq1 = new XMLHttpRequest();
        }
        // IE
        else if (window.ActiveXObject) {
            self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
        }
        self.xmlHttpReq1.open('POST', '/plan/content/updateorder', true);
        self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        self.xmlHttpReq1.onreadystatechange = function() {
            if (self.xmlHttpReq1.readyState == 4) {
                document.getElementById('page_' + pageid + '_order_status').innerHTML = 'Done!';
            }
            else
            {
                document.getElementById('page_' + pageid + '_order_status').innerHTML = 'Updating...';
            }
        }

        self.xmlHttpReq1.send('pageid=' + pageid + '&order=' + order);
   }
</script>

<?php
}
else
{
  include_partial("settings/accessdenied");
}
?>
