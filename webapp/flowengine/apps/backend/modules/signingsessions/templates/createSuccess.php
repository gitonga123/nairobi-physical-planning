<?php
use_helper("I18N");

if ($sf_user->mfHasCredential("signingsessions")): ?>
    <div class="contentpanel">
        <form id="stageform" class="form-bordered"
              action="/plan/signingsessions/create" method="post"
              autocomplete="off" data-ajax="false">
            <div class="panel panel-default">

                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __((isset($config) ? 'Edit ' : 'Add ') . 'Signing Sessions'); ?></h3>
                </div>
                <div class="alert alert-success" id="alertdiv" name="alertdiv" style="display: none;">
                    <button type="button" class="close"
                            onClick="document.getElementById('alertdiv').style.display = 'none';" aria-hidden="true">
                        &times;
                    </button>
                    <strong><?php echo __('Well done'); ?>!</strong> <?php echo __('You successfully updated this stage'); ?></a>.
                </div>
                <div class="panel-body padding-1">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label><i class="bold-label"><?php echo __('User'); ?></i></label>
                                <select name='user_id' id='user_id' style="width: 100%">
                                    <option>-- Select a User --</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user->getNid(); ?>" <?php echo (isset($config) and $config['user_id'] == $user->getNid()) ? 'selected' : '' ?>><?php echo $user->getStrFirstName() . ' ' . $user->getStrLastName(); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-sm-4">
                                <label for="total_allowed_pa">Total Allowed P.A</label>
                                <input type="text" name="total_allowed_pa" id="total_allowed_pa" value="<?php echo !isset($config) ?:$config['total_allowed_pa'] ?>">
                            </div>
                        </div>
                        <br/>
                    </div>
                </div>
                <div class="panel-footer">
                    <a class="btn btn-danger mr10"
                       href="/plan/permittemplates/index"><?php echo __('Back'); ?></a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo __('Add Config'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.3/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.3/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(() => {
            $('#user_id').select2();
        });

        $(document).ready(function () {
            $('#options_select').select2({
                ajax: {
                    url: function (params) {
                        return '/plan/siningsessions/filterfieldvalue?search=' + params;
                    },
                    delay: 250,
                    minimumInputLength: 2,

                    results: function (data) {
                        return data;
                    }
                }
            });
        });
    </script>
<?php else: ?>
    Access denied
<?php endif; ?>
