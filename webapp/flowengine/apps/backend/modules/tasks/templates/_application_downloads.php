<div class="panel panel-info">
    <div class="panel-heading">
        <div class="panel-title">
            <?php echo __("Downloads"); ?>
        </div>
    </div><!-- panel-heading-->
    <div class="panel-body padding-0">
            
        <table class="table table-special m-b-0">
        <!-- Buttons to download permits -->
        <?php
        $q = Doctrine_Query::create()
            ->from("SavedPermit a")
            ->where("a.application_id = ?", $application->getId())
            ->andWhere("a.permit_status <> 3")
            ->andWhere("a.expiry_trigger = 0");
        $permits = $q->execute();

        if($q->count())
        {
        ?>
        <thead>
            <tr>
            <th width="60px">#</th>
            <th><?php echo __("Service"); ?></th>
            <th><?php echo __("Date of issue"); ?></th>
            <th><?php echo __("Date of expiry"); ?></th>
            <th><?php echo __("Actions"); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach($permits as $permit)
                {
                    $q = Doctrine_Query::create()
                            ->from('Permits a')
                            ->where('a.id = ?', $permit->getTypeId());
                    $permittype = $q->fetchOne();

                    if($q->count() == 0 || ($permittype->getPartType() == 2 && $permit->getDocumentKey() == ""))
                    {
                            continue;
                    }

                    $permit_status = "";

                    if($permit->getExpiryTrigger() != 0)
                    {
                        $permit_status = "- EXPIRED -";
                    }
                    ?>
                    <tr>
                        <td><?php echo $permit->getId(); ?></td>
                        <td><?php echo $permittype->getTitle(); ?></td>
                        <td><?php echo $permit->getDateOfIssue()?></td>
                        <td><?php echo $permit->getDateOfExpiry()?></td>
                        <td>
                            <a target="_blank" class="btn btn-xs btn-success" onClick="window.location = '/plan/permits/view/id/<?php echo $permit->getId(); ?>';"><span class="fa fa-print"></span></a>
                        </td>
                        </tr>
                    <?php
                }
                ?>
            </tbody>
        <?php
        }
        else 
        {
            ?>
            <thead>
            <tr>
            <th>
                <div align="left">
                <?php echo __("No Downloads Available"); ?>
                </div>
            </th>
            </tr>
        </thead>
            <?php
        }
        ?>
        </table>

    </div>
</div>