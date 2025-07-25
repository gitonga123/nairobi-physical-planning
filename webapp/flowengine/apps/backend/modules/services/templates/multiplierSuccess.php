<?php
use_helper("I18N");
?>
<div class="contentpanel">
    <div class="panel panel-default">
      <div class="panel-heading">
          <h3 class="panel-title"><?php echo __('Multiplier Fees'); ?></h3>
          
      </div>


    <form action="#" method="post">

        <div class="panel-body">

            <div class="table-responsive">
            <table class="table dt-on-steroids mb0" id="table3">
                <thead>
                <tr>
                    <th class="no-sort" width="5%">#</th>
                    <th width="60%"><?php echo __('Fee'); ?></th>
                    <th width="60%"><?php echo __('Amount'); ?></th>
                    <th width="60" class="no-sort"><?php echo __('Actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                        
                        </td>
                        <td>
                            <select id="fee_field" name="fee_field" class="form-control">
                                <option></option> 
                                <?php
                                    $q = Doctrine_Query::create()
                                        ->from("ApFormElements a")
                                        ->where("a.form_id = ?", $service->getServiceForm())
                                        ->andWhere("a.element_type = ?", 'number')
                                        ->andWhere("a.element_status = 1")
                                        ->orderBy("a.element_title ASC");
                                    $dropdown_fields = $q->execute();

                                    foreach($dropdown_fields as $dropdown_field)
                                    {
                                        $selected = "";

                                        echo "<option value='".$dropdown_field->getElementId()."'>".$dropdown_field->getElementTitle()."</opton>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="fee_amount" class="form-control" value="0">
                        </td>
                        <td>
                            <button type="submit" class="btn btn-primary">Submit</button>  
                        </td>
                    </tr>

                    <?php 
                    foreach($fees as $fee)
                    {
                    ?>
                    <tr>
                        <td><?php echo $fee->getId(); ?></td>
                        <td>
                        <?php 
                            $q = Doctrine_Query::create()
                               ->from("ApFormElements a")
                               ->where("a.form_id = ?", $service->getServiceForm())
                               ->andWhere("a.element_id = ?", $fee->getFieldId());
                            $element = $q->fetchOne();

                            if($element)
                            {
                                echo $element->getElementTitle();
                            }
                        ?>
                        </td>
                        <td><?php echo $fee->getMultiplierAmount(); ?></td>
                        <td>
                            <a href="/plan/services/multiplier/id/31/delete/<?php echo $fee->getId(); ?>" class="btn btn-danger">Delete</button>  
                        </td>
                    </tr>
                    <?php 
                    }
                    ?>

                </tbody>
            </table>
            </div>

        </div>

        </form>
    </div>
</div>
