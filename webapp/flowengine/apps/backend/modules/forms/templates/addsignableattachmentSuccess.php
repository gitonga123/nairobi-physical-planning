<?php
use_helper("I18N");
?>
<div class="contentpanel panel-email">

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"> Add Signing Members For <strong><?php echo $element['title'] ?></strong></h3>
            <small>Members Selected will be allowed to sign these attachments</small>
        </div>

        <form method="post">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select class="form-control" name="user_ids[]" id="allowed_users" multiple>
                                <?php foreach ($mf_users as $user): ?>
                                    <option value="<?php echo $user['id'] ?>" <?php echo in_array($user['id'], $sf_data->getRaw('selected_user_ids')) ? 'selected' : '' ?> ><?php echo $user['fullname'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="group_id">Group</label>
                            <select class="form-control" name="group_ids[]" id="allowed_groups" multiple>
                                <?php foreach ($mf_groups as $group): ?>
                                    <option value="<?php echo $group['id'] ?>" <?php echo in_array($group['id'], $sf_data->getRaw('selected_group_ids')) ? 'selected' : '' ?> ><?php echo $group['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

    </div>
    <div class="panel-footer">
        <button class="btn btn-success" type="submit">Submit</button>
    </div>
    </form>
</div>
</div>
<script>
    jQuery(document).ready(function () {
        var groups = $('[id="allowed_groups"]').bootstrapDualListbox();
        var users = $('[id="allowed_users"]').bootstrapDualListbox();
    });
</script>
