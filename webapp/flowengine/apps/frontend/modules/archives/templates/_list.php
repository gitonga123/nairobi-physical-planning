<?php
use_helper("I18N");

$q = Doctrine_Query::create()
    ->from("AttachedPermit a")
    ->where("a.application_id = ?", $application->getId());
$permit = $q->fetchOne();
?>
<tr <?php if($permit): ?><?php else: ?>class="unread"<?php endif; ?>>
    <td> </td>
    <td>
        <?php
        $q = Doctrine_Query::create()
            ->from("Favorites a")
            ->where("a.application_id = ?", $application->getId())
            ->andWhere("a.userid = ?", $_SESSION['SESSION_CUTEFLOW_USERID']);
        $favorite = $q->fetchOne();
        if($favorite)
        {
            ?>
            <a href="#" class="star star-checked" id="star<?php echo $application->getId(); ?>"><i class="glyphicon glyphicon-star"></i></a>
        <?php
        }
        else
        {
            ?>
            <a href="#" class="star" id="star<?php echo $application->getId(); ?>"><i class="glyphicon glyphicon-star"></i></a>
        <?php
        }
        ?>
    </td>
    <td>
        <?php
          echo html_entity_decode($application->getForm()->getFormName());
        ?>
    </td>
    <td>
        <a href="/index.php/archivesarchives/view/id/<?php echo $application->getId(); ?>"><?php echo $application->getApplicationId(); ?></a>
    </td>
    <td>
        <?php echo date('d F Y', strtotime($application->getDateOfSubmission())); ?>
    </td>
    <td>
        <?php
        echo $application->getStatusName();
        ?>
    </td>
    <td>
        <a  title='<?php echo __('View Application'); ?>' href='/index.php/archives/view/id/<?php echo $application->getId(); ?>'> <span class="label label-primary"><i class="fa fa-eye"></i></span></a>
    </td>
</tr>
