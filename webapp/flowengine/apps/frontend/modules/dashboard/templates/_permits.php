<?php

/**
 * _permits template.
 *
 * Displays a list of permits
 *
 * @package    frontend
 * @subpackage lastest_applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper("I18N");
$permit_manager = new PermitManager();
$uri = $_SERVER['REQUEST_URI'];
$params = substr($uri, strpos($uri, '?'));
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            Permits (<?php echo count($permits) ?>)

        </h3>

        <div class="row">
            <div class="col-md-2 pull-right">
                <a style="color: white" href="<?php echo url_for('/index.php/dashboard/downloadpermits' . $params) ?>"
                   class="btn btn-primary pull-right">
                    <i class="fa fa-download"></i>
                    Download CSV
                </a>
            </div>

            <div class="col-md-8 pull-right">
                <form method="get" class="form-inline">
                    <label for="field_status">Status</label>
                    <select name="status" style="margin-right: 1rem" id="field_status">
                        <option value="-1">All</option>
                        <option value="1" <?php echo (in_array('status', array_keys($_GET)) and $_GET['status'] == 1) ? "selected" : '' ?>>
                            Approved
                        </option>
                        <option value="3" <?php echo (in_array('status', array_keys($_GET)) and $_GET['status'] == 3) ? "selected" : '' ?>>
                            Cancelled
                        </option>
                    </select>

                    <label for="date_of_issue_gte">From</label>
                    <input style="border-radius: .4rem; border: none; padding: .4rem; margin-right: 1rem"
                           id="date_of_issue_gte" type="date" required
                           name="date_of_issue_gte"
                           value="<?php echo in_array('date_of_issue_gte', array_keys($_GET)) ? $_GET['date_of_issue_gte'] : null; ?>"/>

                    <label for="date_of_issue_lte">To</label>
                    <input style="border-radius: .4rem; border: none; padding: .4rem" id="date_of_issue_lte" type="date"
                           required
                           name="date_of_issue_lte"
                           value="<?php echo in_array('date_of_issue_lte', array_keys($_GET)) ? $_GET['date_of_issue_lte'] : null; ?>"/>

                    <button class="btn btn-info" type="submit">
                        <i class="fa fa-search"></i>
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table mb0" id="permits">
                <thead>
                <tr>
                    <th>User</th>
                    <th>Application</th>
                    <th>Type</th>
                    <th>Date Of Issue</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($permits as $permit): $perm = Doctrine_Query::create()->from("SavedPermit a")->where("id = ? ", $permit['permit_id'])->fetchOne(); ?>
                    <tr>
                        <td>
                            <?php echo $permit['fullname']; ?>
                            <br/>
                            <small><?php echo $permit['email']; ?></small>
                            <br/>
                            <small><?php echo $permit['mobile']; ?></small>
                        </td>
                        <td> <?php echo $permit['a_id']; ?> </td>
                        <td> <?php echo $permit['title']; ?> </td>
                        <td> <?php echo date('d M Y', strtotime($permit['date_of_issue'])); ?>    </td>
                        <td> <?php if ($permit['permit_status'] == 1): ?>
                                <div class="label label-success">Approved</div>
                            <?php else: ?>
                                <div class="label label-danger">Cancelled</div>
                            <?php endif; ?> </td>
                        <td>
                            <?php if ($permit_manager->is_applications_permit_signed($perm) and $permit['permit_status'] == 1): ?>
                                <a href="<?php echo url_for('/index.php/permits/downloadsignedpermit/id/' . $permit['permit_id']) ?>"
                                   class="btn btn-info">
                                    <i class="fa fa-download"></i> Download Permit
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
    $('#permits').dataTable({
        "sPaginationType": "full_numbers",

        // Using aoColumnDefs
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": ['no-sort']}
        ]
    });
</script>
