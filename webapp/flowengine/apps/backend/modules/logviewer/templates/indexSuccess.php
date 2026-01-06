<?php use_helper("I18N", "Url") ?>

<?php if ($sf_user->mfHasCredential('manage_system_technical_logs')): ?>
    <div class="contentpanel">
        <div class="panel panel-default" style="padding: 15px; margin: auto;">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Technical System Log Files') ?></h3>
            </div>

            <div class="panel-body">
                <div class="row">

                    <!-- Left Pane: Log File Selector -->
                    <div class="col-md-3">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <?php echo __('Log Files') ?>
                                    <a href="/backend.php/logviewer/new" class="btn btn-xs btn-success pull-right">
                                        <i class="glyphicon glyphicon-plus"></i> <?php echo __('Add') ?>
                                    </a>
                                </h4>
                            </div>
                            <div class="list-group">
                                <?php foreach ($logPaths as $log): ?>
                                    <div class="list-group-item">
                                        <div class="clearfix">
                                            <div class="pull-left">
                                                <a href="<?php echo "/backend.php/logviewer/index" . '?id=' . urlencode($log['id']) ?>"
                                                    class="<?php echo $fileKey == $log['id'] ? 'text-white' : 'text-white' ?>">
                                                    <i class="<?php echo $fileKey == $log['id'] ? ' fa fa-check' : '' ?>">
                                                    </i><?php echo htmlspecialchars($log['title']) ?>
                                                </a>
                                            </div>
                                            <div class="pull-right">
                                                <a href="<?php echo '/backend.php/logviewer/edit?id=' . urlencode($log['id']) ?>"
                                                    class="btn btn-xs btn-info" title="<?php echo __('Edit') ?>">
                                                    <i class="glyphicon glyphicon-pencil"></i>
                                                </a>
                                                <a href="<?php echo '/backend.php/logviewer/delete?id=' . urlencode($log['id']) ?>"
                                                    class="btn btn-xs btn-danger" title="<?php echo __('Delete') ?>"
                                                    onclick="return confirm('<?php echo __('Are you sure?') ?>')">
                                                    <i class="glyphicon glyphicon-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Pane: Log Viewer -->
                    <div class="col-md-9">
                        <div class="panel panel-default">
                            <div class="panel-heading clearfix">
                                <div class="pull-left">
                                    <strong><?php echo __('Viewing Log:') ?></strong>
                                    <?php
                                    $selectedLog = null;
                                    foreach ($logPaths as $log) {
                                        if ($log['id'] == $fileKey) {
                                            $selectedLog = $log;
                                            break;
                                        }
                                    }
                                    echo $selectedLog ? htmlspecialchars($selectedLog['title']) : __('None Selected');
                                    ?>
                                </div>
                                <?php if ($selectedLog): ?>
                                    <div class="pull-right">
                                        <form method="get" class="form-inline"
                                            action="<?php echo '/backend.php/logviewer/index' ?>">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($fileKey) ?>">
                                            <select name="severity" class="form-control input-sm" onchange="this.form.submit()">
                                                <option value=""><?php echo __('All Severities') ?></option>
                                                <?php foreach (['Notice', 'Warning', 'Error', 'Fatal', 'Deprecated'] as $sev): ?>
                                                    <option value="<?php echo $sev ?>" <?php echo $sev === $currentSeverity ? ' selected' : '' ?>>
                                                        <?php echo $sev ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="panel-body">
                                <div class="table-responsive">

                                    <?php
                                    if (count($lines) == 0) { ?>
                                        <div class="alert alert-info">
                                            <strong>Info:</strong> Troubleshooting steps for <code>file_exists()</code> not
                                            detecting Apache log files
                                            <ul class="m-t-10">
                                                <li>✅ Add <code>www-data</code> to the <code>adm</code> group:
                                                    <pre>sudo usermod -aG adm www-data</pre>
                                                </li>
                                                <li>✅ Restart your server or re-login session to apply group changes:
                                                    <pre>sudo reboot</pre>
                                                </li>
                                                <li>✅ Confirm <code>www-data</code> is now part of the <code>adm</code> group:
                                                    <pre>groups www-data</pre>
                                                </li>
                                                <li>✅ Ensure Apache/PHP is running as <code>www-data</code>:
                                                    <pre>ps aux | grep apache</pre>
                                                </li>
                                                <li>✅ Verify parent folder permissions:
                                                    <pre>ls -ld /var /var/log /var/log/apache2</pre>
                                                    <ul>
                                                        <li>If blocked, allow access with:</li>
                                                        <pre>sudo chmod o+x /var/log/apache2</pre>
                                                    </ul>
                                                </li>
                                                <li>✅ Restart Apache service after group/user changes:
                                                    <pre>sudo service apache2 restart</pre>
                                                </li>
                                                <li>✅ Optional – test with a standalone PHP script:
                                                    <pre>&lt;?php
                                                                $path = '/var/log/apache2/uasin_error.log';
                                                                echo file_exists($path) ? "File exists." : "Not accessible";
                                                            </pre>
                                                </li>
                                            </ul>
                                        </div>

                                    <?php }
                                    ?>
                                    <?php include_partial('logviewer/log_table', ['lines' => $lines]) ?>
                                </div>
                            </div>

                            <?php if ($totalPages > 1): ?>
                                <div class="panel-footer text-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <a href="<?php echo ('/backend.php/logviewer/index') ?>?id=<?php echo urlencode($fileKey) ?>&page=<?php echo $i ?>&severity=<?php echo urlencode($currentSeverity) ?>"
                                            class="btn btn-xs <?php echo $i === $page ? 'btn-primary' : 'btn-default' ?>">
                                            <?php echo $i ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div> <!-- row -->
            </div> <!-- panel-body -->
        </div> <!-- panel -->
    </div> <!-- contentpanel -->

<?php else: ?>
    <?php include_partial("settings/accessdenied"); ?>
<?php endif; ?>