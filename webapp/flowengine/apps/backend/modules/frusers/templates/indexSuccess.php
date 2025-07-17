<?php
use_helper("I18N");

if ($sf_user->mfHasCredential("manageusers")) {
    $_SESSION['current_module'] = "users";
    $_SESSION['current_action'] = "index";
    $_SESSION['current_id'] = "";
?>
    <?php
    /**
     * indexSuccess.php template.
     *
     * Displays list of all registered clients
     *
     * @package    backend
     * @subpackage frusers
     * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
     */
    ?>
    <div class="pageheader">
        <h2><i class="fa fa-home"></i><?php echo __('Users'); ?><span>List of registered users</span></h2>
        <div class="breadcrumb-wrapper">
            <span class="label"><?php echo __('You are here'); ?>:</span>
            <ol class="breadcrumb">
                <li><a href="/plan"><?php echo __('Home'); ?></a></li>
                <li class="active"><?php echo __('Users'); ?></li>
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




                <?php if ($pager->getResults()) : ?>
                    <table class="table b-b-0">
                        <thead>
                            <tr>
                                <th style="width:10%;" class="b-b-0">
                                    <?php
                                    if ($sf_user->mfHasCredential("manageusers")) {
                                    ?>
                                        <a href="/plan/frusers/new" class="btn btn-primary tooltips table-btn" data-original-title="New User" data-toggle="tooltip"><span class="hidden-xs">+ <?php echo __('Add User'); ?></span></a>
                                    <?php
                                    }
                                    ?>
                                </th>
                                <form method="post" action="/plan/frusers/index/filter/<?php echo $filter; ?><?php if ($filterstatus != "") {
                                                                                                                        echo "/filterstatus/" . $filterstatus;
                                                                                                                    } ?>">
                                    <th class="b-b-0" style="width:50%;">
                                        <input name="search" value="<?php echo $filter; ?>" placeholder="<?php echo __('Search'); ?>" type="text" class="form-control p10">
                                    </th>

                                    <?php
                                    if (!sfConfig::get('app_sso_secret')) {
                                    ?>
                                        <th class="b-b-0 radius-tr">
                                            <select size="1" name="filter_status" aria-controls="table2" class="select2" onChange="window.location='/plan/frusers/index/filterstatus/' + this.value;">
                                                <option value="1"><?php echo __('Select Status'); ?></option>
                                                <option value="1" <?php if ($filterstatus == "1") {
                                                                        echo "selected='selected'";
                                                                    } ?>><?php echo __('Active'); ?>
                                                </option>
                                                <option value="0" <?php if ($filterstatus == "0") {
                                                                        echo "selected='selected'";
                                                                    } ?>><?php echo __('Inactive'); ?>
                                                </option>
                                            </select>
                                        </th>
                                    <?php
                                    }
                                    ?>
                                </form>
                            </tr>
                        </thead>
                    </table>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-special">
                            <thead>
                                <tr class="main-tr">
                                    <th class="b-b-0">#</th>
                                    <th class="b-b-0"><?php echo __('Full Name'); ?></th>
                                    <th class="b-b-0"><?php echo __('Email Address'); ?></th>
                                    <th class="b-b-0"><?php echo __('User ID'); ?></th>
                                    <th class="b-b-0"><?php echo __('Created On'); ?></th>
                                    <th class="b-b-0"><?php echo __('Last Login'); ?></th>
                                    <?php
                                    if (sfConfig::get('app_enable_categories') == "yes") {
                                    ?>
                                        <th><?php echo __('Status'); ?></th>
                                    <?php
                                    }
                                    ?>
                                    <th class="aligncenter b-b-0"><?php echo __('Action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;

                                if ($pager->getPage() > 1) {
                                    $count = 10 * ($pager->getPage() - 1);
                                }

                                foreach ($pager->getResults() as $user) {
                                    $userprofile = $user->getProfile();
                                    if ($userprofile) {
                                        $count++;
                                ?>
                                        <tr>
                                            <td><?php echo $count; ?></td>
                                            <td><?php echo $userprofile->getFullname(); ?></td>
                                            <td><?php echo $userprofile->getEmail(); ?></td>
                                            <td><?php echo $user->getUsername(); ?></td>
                                            <td><?php echo $user->getCreatedAt(); ?></td>
                                            <td><?php echo $user->getLastLogin(); ?></td>
                                            <?php
                                            if (sfConfig::get('app_enable_categories') == "yes") {
                                                if ($user->getIsActive() != 1) {
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
                                                ?>
                                            <?php
                                            }
                                            ?>
                                            <td class="aligncenter">
                                                <a title="View User" href="/plan/frusers/show/id/<?php echo $user->getId(); ?>"><span class="label label-primary"><i class="fa fa-eye"></i></span></a>
                                                <?php
                                                if ($sf_user->mfHasCredential("manageusers") && sfConfig::get('app_enable_categories') == "yes") {
                                                    if ($user->getIsActive() != 1) {
                                                ?>
                                                        <a title="Activate User" href="/plan/frusers/activate/id/<?php echo $user->getId(); ?>"><span class="label label-primary"><i class="fa fa-check-circle"></i></span></a>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <a title="Deactivate User" href="/plan/frusers/deactivate/id/<?php echo $user->getId(); ?>"><span class="label label-primary"><i class="fa fa-times-circle-o"></i></span></a>
                                                    <?php
                                                    }

                                                    if ($user->getIsSuperAdmin() != 1) {
                                                    ?>
                                                        <a title="Validate Email" href="/plan/frusers/validate/id/<?php echo $user->getId(); ?>"><span class="label label-primary"><i class="fa fa-check-circle"></i></span></a>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <a title="UnValidate Email" href="/plan/frusers/unvalidate/id/<?php echo $user->getId(); ?>"><span class="label label-primary"><i class="fa fa-times-circle-o"></i></span></a>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="12">
                                        <p class="table-showing pull-left"><strong><?php echo count($pager) ?></strong> <?php echo __('Users'); ?>

                                            <?php if ($pager->haveToPaginate()) : ?>
                                                - page <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
                                            <?php endif; ?></p>


                                        <?php if ($pager->haveToPaginate()) : ?>
                                            <ul class="pagination pagination-sm mb0 mt0 pull-right">
                                                <li><a href="/plan/frusers/index/page/1<?php if ($filter) {
                                                                                                    echo "/filter/" . $filter;
                                                                                                } ?><?php if ($fromdate) {
                                                                                                        echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                    } ?>">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a></li>

                                                <li><a href="/plan/frusers/index/page/<?php echo $pager->getPreviousPage() ?><?php if ($filter) {
                                                                                                                                        echo "/filter/" . $filter;
                                                                                                                                    } ?><?php if ($fromdate) {
                                                                                                                                            echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                                        } ?>">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a></li>

                                                <?php foreach ($pager->getLinks() as $page) : ?>
                                                    <?php if ($page == $pager->getPage()) : ?>
                                                        <li class="active"><a href=""><?php echo $page ?></li></a>
                                                    <?php else : ?>
                                                        <li><a href="/plan/frusers/index/page/<?php echo $page ?><?php if ($filter) {
                                                                                                                            echo "/filter/" . $filter;
                                                                                                                        } ?><?php if ($fromdate) {
                                                                                                                                echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                            } ?>"><?php echo $page ?></a></li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <li><a href="/plan/frusers/index/page/<?php echo $pager->getNextPage() ?><?php if ($filter) {
                                                                                                                                    echo "/filter/" . $filter;
                                                                                                                                } ?><?php if ($fromdate) {
                                                                                                                                        echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                                    } ?>">
                                                        <i class="fa fa-angle-right"></i>
                                                    </a></li>

                                                <li><a href="/plan/frusers/index/page/<?php echo $pager->getLastPage() ?><?php if ($filter) {
                                                                                                                                    echo "/filter/" . $filter;
                                                                                                                                } ?><?php if ($fromdate) {
                                                                                                                                        echo "/fromdate/" . $fromdate . "/todate/" . $todate;
                                                                                                                                    } ?>">
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
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table dt-on-steroids mb0">
                            <tbody>
                                <tr>
                                    <td>
                                        <?php echo __('No Records Found'); ?>
                                    </td>
                                </tr>
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
} else {
    include_partial("accessdenied");
}
?>