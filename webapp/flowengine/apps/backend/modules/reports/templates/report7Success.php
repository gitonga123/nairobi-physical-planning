<?php
use_helper("I18N");
?>
<div class="contentpanel">
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo __('Mail/SMS Notifications Report'); ?></h3>
        </div>

        <div class="panel-heading text-right">
              <a class="btn btn-primary" id="export" href="/backend.php/reports/printreport7" ><?php echo __('Export'); ?></a>
              <a class="btn btn-primary" id="newpage" href="/backend.php/reports/list" ><?php echo __('Back to Reports'); ?></a>
        </div>
        <div class="panel-body">

            <div class="table-responsive">
                <table class="table dt-on-steroids mb0" id="table3">
                    <thead>
                    <tr>
                        <th class="no-sort">#</th>
                        <?php 
                        foreach($columns as $column)
                        {
                        ?>
                        <th nowrap><?php echo $column; ?></th>
                        <?php 
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 0;

                        foreach($records as $record)
                        {
                            $count++;

                            echo "<tr>";
                                echo "<td>".$count."</td>";

                                foreach($record as $record_columns)
                                {
                                    echo "<td>".$record_columns."</td>";
                                }

                            echo "</tr>";
                        }
                        ?>
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
</script>