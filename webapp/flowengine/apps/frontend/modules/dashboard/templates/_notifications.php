<?php
/**
 * _notifications template.
 *
 * Displays a list of the latest notifications
 *
 * @package    frontend
 * @subpackage notifications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

foreach ($corrections_applications as $application) {
    ?>
    <div class="alert alert-danger">
        <strong><?php echo ("Corrections!"); ?></strong>
        <?php echo ("You have an application that has been sent back to corrections. The following are the reasons for rejection:"); ?>
        <?php
        $q = Doctrine_Query::create()
            ->from("EntryDecline a")
            ->where("a.entry_id = ? and a.resolved =?", array($application->getId(), 0));
        $comments = $q->execute();
        ?>
        <ul>
            <?php foreach ($comments as $comment) { ?>
                <li><?php echo $comment->getDescription(); ?> -
                    <?php if ($comment->getResolved()) {
                        echo "<span class='label label-success'>" . ("Resolved") . "</span>";
                    } else {
                        echo "<span class='label label-danger'>" . ("Not Resolved") . "</span>";
                    } ?>
                </li>
            <?php } ?>
        </ul>
        <strong> <a
                href="/index.php/application/edit/id/<?php echo $application->getId(); ?>"><?php echo ("Click here to edit and resubmit"); ?></a></strong>
    </div>
    <?php
}

foreach ($transferring_applications as $application) {
    $origin_user = Doctrine_Core::getTable("SfGuardUser")->find($application->getUserId());
    $origin_user_profile = $origin_user->getProfile();

    $id = $application->getId();
    $data = json_encode(array('id' => $id));
    $encryptdata = base64_encode($data);
    ?>
    <!-- <div class="alert alert-success">
        <h4><?php // echo ("Transfer!"); ?></h4> <?php // echo ("You have an application"); ?> (<?php //echo $application->getApplicationId(); ?> ) <?php // echo ("that is being transferred to you from"); ?> <?php //echo $origin_user_profile->getFullname()." (".$origin_user_profile->getEmail().")"; ?>. <br><br>
        <a href="/index.php/application/accepttransfer/code/<?php //echo $encryptdata; ?>" class="btn btn-success"><?php //echo ("Confirm Transfer"); ?></a>  <a class="btn btn-danger" href="/index.php/application/canceltransfer/code/<?php // echo $encryptdata; ?>"><? php// echo ("Cancel Transfer"); ?></a>
    </div> -->
    <?php
}
?>