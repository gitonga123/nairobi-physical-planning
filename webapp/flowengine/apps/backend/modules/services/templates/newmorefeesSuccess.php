<?php
use_helper("I18N");
?>
<div class="contentpanel">
    <div class="panel panel-default">
      <div class="panel-heading">
          <h3 class="panel-title"><?php echo __('More Fees: Add New'); ?></h3>
      </div>


    <form action="/backend.php/services/savenewmorefees/id/<?php echo $service->getId(); ?>" method="post">

      <div class="panel-heading text-right" style="border-bottom:1px solid #d2d2d2;">
          <button type="submit" class="btn btn-primary">Save Details</button>
      </div>

        <div class="panel-body">

        <?php if($element_id){ ?>
        <label>Fee title</label>
            <input type="text" class="form-control" name="fee_title" id="fee_title">

        <br>
        <br>
        <?php } ?>

        <label>Select a fee field</label>
            <select id="dropdown_field" name="dropdown_field" onChange="window.location='/backend.php/services/newmorefees/serviceid/<?php echo $service->getId(); ?>/element_id/' + this.value;" class="form-control">
                <option>Choose A Field...</option>
                <?php
                $q = Doctrine_Query::create()
                    ->from("ApFormElements a")
                    ->where("a.form_id = ?", $service->getServiceForm())
                    ->andWhere("a.element_type = ?", 'select')
                    ->andWhere("a.element_status = 1")
                    ->orderBy("a.element_title ASC");
                $dropdown_fields = $q->execute();

                foreach($dropdown_fields as $dropdown_field)
                {
                    $selected = "";

                    if($element_id == $dropdown_field->getElementId())
                    {
                        $selected = "selected='selected'";
                    }

                    echo "<option value='".$dropdown_field->getElementId()."' ".$selected.">".$dropdown_field->getElementTitle()."</opton>";
                }
                ?>
            </select>

<hr>

            <?php
        if($element_id)
        {
          ?>
          <div class="table-responsive">
          <table class="table dt-on-steroids mb0" id="table3">
            <thead>
              <tr>
                <th width="10">#</th>
                <th class="no-sort" width="80%"><?php echo __('Title'); ?></th>
                <th class="no-sort"><?php echo __('Service Fee'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
                $q = Doctrine_Query::create()
                   ->from("ApElementOptions a")
                   ->where("a.form_id = ?", $service->getServiceForm())
                   ->andWhere("a.element_id = ?", $element_id)
                   ->orderBy("a.option_text ASC");
                $options = $q->execute();

            	$count = 1;
            	?>
              <?php foreach ($options as $option): ?>
                <tr>
                  <td><?php echo $count++; ?></td>
                  <td>
                    <?php echo $option->getOptionText(); ?>
                  </td>
                  <td>
                    <input type="text" name="options[<?php echo $option->getAeoId(); ?>]" class="form-control" value="0">
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
          <?php
        }
      ?>

        </div>

        <div class="panel-footer">
            <button type="submit" class="btn btn-primary">Save Details</button>
        </div>

        </form>
    </div>
</div>
