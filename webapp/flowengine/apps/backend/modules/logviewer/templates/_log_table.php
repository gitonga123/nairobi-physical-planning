<!-- apps/frontend/modules/logViewer/templates/_log_table.php -->

<table class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th>#</th>
            <th>Log Entry</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($lines)): ?>
            <tr>
                <td colspan="2">No log entries found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($lines as $i => $line): ?>
                <tr>
                    <td><?php echo $i + 1 ?></td>
                    <td><code><?php echo $line; ?></code></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>