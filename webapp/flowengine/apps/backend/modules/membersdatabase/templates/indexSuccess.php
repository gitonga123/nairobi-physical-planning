<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed Membership Association Settings");
?>

<div class="contentpanel panel-email">
    <div class="panel panel-dark">

        <div class="panel-heading">
                <h3 class="panel-title"><?php echo __('Membership Databases'); ?></h3>
        </div>

        <div class="panel-group panel-group" id="accordion2">
            <?php
            $q = Doctrine_Query::create()
               ->from("SfGuardUserCategories a")
			   ->where("a.member_association_name IS NOT NULL")
               ->orderBy("a.orderid ASC");
            $associations = $q->execute();

            $count = 0;
            foreach($associations as $association)
            {
                $count++;
                ?>
                <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                    <a href="<?php echo public_path(); ?>backend.php/membersdatabase/association/filter/<?php echo $association->getId(); ?>" class="collapsed">
                        <?php echo $count; ?>:  <?php echo $association->getMemberAssociationName(); ?>
                    </a>
                    </h4>
                </div>
                </div>
                <?php
            }
            ?>
          </div>
    </div>
</div>
