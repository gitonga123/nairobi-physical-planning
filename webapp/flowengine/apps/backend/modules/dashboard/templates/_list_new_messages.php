<table class="table table-striped table-hover table-special" id="apps_tbl">
    <?php
    if (count($current_paginator->getResults()) > 0) {
    ?>
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo  __("Sent On."); ?></th>
                <th><?php echo __("Sent By"); ?></th>
                <th><?php echo __("Message"); ?></th>
                <th><?php echo __("Actions"); ?></th>
            </tr>
        </thead>
    <?php } ?>
    <tbody>
        <?php
        $count = 1;
        foreach ($current_paginator->getResults() as $message) { ?>
            <tr>
                <th><?php echo $count; ?> </th>
                <td>
                    <?php echo $message->getActionTimestamp(); ?>
                </td>

                <td>
                    <?php if ($message->getArchitectId() != "") {
                        $q = Doctrine_Query::create()
                            ->from('SfGuardUser a')
                            ->where('a.id = ?', $message->getArchitectId());
                        $architect = $q->fetchOne();

                        $fullname = $architect->getProfile()->getFullname();
                    }
                    echo $fullname; ?>
                </td>
                <td>
                    <?php
                    $words = explode(" ", html_entity_decode($message->getContent()));
                    echo implode(" ", array_splice($words, 0, 30)) . "....";
                    ?>
                </td>
                <td>
                    <a title="view message" class="btn btn-sm btn-primary" href="/backend.php/applications/view/id/<?php echo $message->getApplicationId(); ?>/current_tab/messages">
                        <?php echo __('View'); ?> <span class="fa fa-eye"></span></a>
                    </a>
                </td>
            </tr>
        <?php
            $count = $count + 1;
        }
        ?>
        <div align="center">
            <?php
            if (count($current_paginator->getResults()) == 0) {
            ?>
                <h4>No Messages Found</h4>
            <?php
            }
            ?>
        </div>
    </tbody>

</table>