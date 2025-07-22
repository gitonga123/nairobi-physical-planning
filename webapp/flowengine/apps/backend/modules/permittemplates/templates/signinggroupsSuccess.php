<?php
use_helper("I18N");
$conn = Doctrine_Manager::getInstance()->getCurrentConnection();
?>
<div class="contentpanel">

    <form id="stageform" class="form-bordered"
          action="/plan/permittemplates/signinggroups/id/<?php echo $template->getId(); ?>" method="post"
          autocomplete="off" data-ajax="false">
        <div class="panel panel-default">

            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $template->getTitle(); ?></h3>
                <?php echo __('Group access'); ?>
            </div>
            <div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
                <button type="button" class="close"
                        onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">&times;
                </button>
                <strong><?php echo __('Well done'); ?>
                    !</strong> <?php echo __('You successfully updated this stage'); ?></a>.
            </div>
            <div class="panel-body padding-0">
                <div class="form-group">
                    <label class="col-sm-4"><i class="bold-label"><?php echo __('Group access'); ?></i></label>
                    <div class="col-sm-8">
                        <select name='allowed_groups[]' id='allowed_groups' multiple>
                            <?php
                            $selected = "";
                            $q = Doctrine_Query::create()
                                ->from("MfGuardPermission a")
                                ->where("a.name = ?", $prefix);
                            $permission = $q->fetchOne();

                            foreach ($groups as $group) {
                                $selected = "";
                                if ($permission and
                                    Doctrine_Query::create()
                                        ->from('MfGuardGroupPermission')
                                        ->where("group_id = ? AND permission_id = ?", [$group->getId(), $permission->getId()])
                                        ->fetchOne()) {
                                    $selected = "selected";
                                }
                                echo "<option value='" . $group->getId() . "' " . $selected . ">" . $group->getName() . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <a class="btn btn-danger mr10" href="/plan/permittemplates/index"><?php echo __('Back'); ?></a>
                <button type="submit" class="btn btn-primary" name="submitbuttonname" id="submitbuttonname"
                        value="submitbuttonvalue"><?php echo __('Submit'); ?></button>
            </div>
        </div>

    </form>
</div>

<script type="text/javascript" src="/assets_backend/js/jquery.bootstrap-duallistbox.js"></script>
<script>
    jQuery(document).ready(function () {
        var groups = $('[id="allowed_groups"]').bootstrapDualListbox();
    });
</script>
