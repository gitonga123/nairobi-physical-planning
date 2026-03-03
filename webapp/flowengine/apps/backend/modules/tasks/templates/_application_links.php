<?php
    $q = Doctrine_Query::create()
        ->from("FormEntryLinks a")
        ->where("a.formentryid = ? AND a.entry_id <> ?", array($application->getId(),0));
    $links = $q->execute();
    $count = 0;
    foreach($links as $link):
        $count++;
        $q = Doctrine_Query::create()
        ->from("ApForms a")
        ->where("a.form_id = ?", $link->getFormId())
        ->limit(1);
        $linkedform = $q->fetchOne();
        if ($linkedform):
        //Display control buttons that manipulate the application
    ?>
    <div class="panel panel-default">
        <div class="panel-heading panel-heading-noradius">
        <h4 class="panel-title">
            <a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#link_<?php echo $count ?>">
                <?php echo $linkedform->getFormName() ?>
            </a>
        </h4>
        </div>
        <div id="link_<?php echo $count ?>" class="panel-collapse collapse">
        <div class="panel-body">
            <?php
            //Displays any information attached to this application
            include_partial('tasks/viewformlinks', array('link' => $link));
            ?>
        </div>
        </div>
    </div>
        <?php endif; ?>
    <?php endforeach; ?>