<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed form settings");

if($sf_user->mfHasCredential("manageforms"))
{
  if($_POST['option_values'])
  {
    $last_option_id = $_POST['last_option_id'];
    $last_option_position = $_POST['last_option_position'];

    foreach($_POST['option_values'] as $option)
    {
      if(strlen($option) > 0)
      {
        try
        {
          $last_option_id++;
          $last_option_position++;

          $element_option = new ApElementOptions();

          $element_option->setFormId($_GET['id']);
          $element_option->setElementId($_GET['element_id']);
          $element_option->setOptionId($last_option_id);
          $element_option->setPosition($last_option_position);
          $element_option->setOptionText($option);
          $element_option->setLive(1);

          $element_option->save();
        }catch(Exception $ex)
        {
          echo "Error: ".$ex;
        }
      }
    }
  }
?>
<div class="contentpanel panel-email">

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"> <?php if($form){ echo $form->getFormName(); ?> -&gt; <?php } ?>Bulk Options</h3>        
    </div>

    <div class="panel-heading text-right">

        <?php
        if($_GET['element_id'])
        {
        ?>
            <button class="btn btn-primary"  data-toggle="modal" data-target="#myModal"><?php echo __('+ Add Option'); ?></button>
        <?php
        }
        ?>
</div>

    <div class="panel-body padding-0">

      <select id="dropdown_field" name="dropdown_field" onChange="window.location='/backend.php/forms/bulkoptions?id=<?php echo $form->getFormId(); ?>&element_id=' + this.value;" class="form-control" style="margin: 20px; width: 95%;">
        <option>Choose A Field...</option>
        <?php
          $q = Doctrine_Query::create()
             ->from("ApFormElements a")
             ->where("a.form_id = ?", $form->getFormId())
             ->andWhere("a.element_type = ?", 'select')
             ->andWhere("a.element_status = 1")
             ->orderBy("a.element_title ASC");
          $dropdown_fields = $q->execute();

          foreach($dropdown_fields as $dropdown_field)
          {
            $selected = "";

            if($_GET['element_id'] == $dropdown_field->getElementId())
            {
              $selected = "selected='selected'";
            }

            echo "<option value='".$dropdown_field->getElementId()."' ".$selected.">".$dropdown_field->getElementTitle()."</opton>";
          }
        ?>
      </select>

      <hr>

      <?php
        if($_GET['element_id'])
        {
          ?>
          <div class="table-responsive">
          <table class="table dt-on-steroids mb0" id="table3">
            <thead>
              <tr>
                <th width="10">#</th>
                <th class="no-sort" width="80%"><?php echo __('Title'); ?></th>
                <th class="no-sort"><?php echo __('Action'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
                $q = Doctrine_Query::create()
                   ->from("ApElementOptions a")
                   ->where("a.form_id = ?", $form->getFormId())
                   ->andWhere("a.element_id = ?", $_GET['element_id'])
                   ->orderBy("a.option_text ASC");
                $options = $q->execute();

                $last_option_id = 0;
                $last_option_position = 0;
            		$count = 1;
            	?>
              <?php foreach ($options as $option): ?>
                <?php
                  if($option->getOptionId() > $last_option_id)
                  {
                    $last_option_id = $option->getOptionId();
                  }

                  if($option->getPosition() > $last_option_position)
                  {
                    $last_option_position = $option->getPosition();
                  }
                ?>
                <tr>
                  <td><?php echo $count++; ?></td>
                  <td><input type="text" name="options[]" class="form-control" value="<?php echo $option->getOptionText(); ?>"  onKeyUp="updateOption('<?php echo $_GET['id']; ?>','<?php echo $_GET['element_id']; ?>','<?php echo $option->getAeoId(); ?>', this.value);">
                    <div id="option_stats_<?php echo $option->getAeoId(); ?>"></div>
                  </td>
                  <td>
                    <?php
                    if($option->getLive())
                    {
                    ?>
                    <div id='option_<?php echo $option->getAeoId(); ?>'>
                      <a id="<?php echo $option->getAeoId(); ?>" href="#<?php echo $option->getAeoId(); ?>" onClick="updateUnpublishOption('<?php echo $_GET['id']; ?>','<?php echo $_GET['element_id']; ?>','<?php echo $option->getAeoId(); ?>');"><span class="fa fa-trash-o"></span></a>
                    </div>
                    <?php
                    }
                    else {
                    ?>
                    <div id='option_<?php echo $option->getAeoId(); ?>'>
                      <a href="#" onClick="updatePublishOption('<?php echo $_GET['id']; ?>','<?php echo $_GET['element_id']; ?>','<?php echo $option->getAeoId(); ?>');"><span class="fa fa-ban"></span></a>
                    </div>
                    <?php
                    }
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
          <?php
        }
      ?>
    </div><!--panel-body-->
</div><!--panel-default-->
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <form class="form" action="<?php echo public_path('plan/forms/bulkoptions?id='.$_GET['id']) ?>&element_id=<?php echo $_GET['element_id']; ?>" method="post" autocomplete="off">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><?php echo __('Add Options'); ?></h4>
      </div>
      <div class="modal-body modal-body-nopadding" id="newtask">

        <div class="form-group">
        	<label class="col-sm-4"><?php echo __('Option #1'); ?></label>
            <div class="col-sm-8"><input type="text" name="option_values[]"  class="form-control">
            </div>
        </div>

        <div class="form-group">
        	<label class="col-sm-4"><?php echo __('Option #2'); ?></label>
            <div class="col-sm-8"><input type="text" name="option_values[]"  class="form-control">
            </div>
        </div>

        <div class="form-group">
        	<label class="col-sm-4"><?php echo __('Option #3'); ?></label>
            <div class="col-sm-8"><input type="text" name="option_values[]"  class="form-control">
            </div>
        </div>

        <div class="form-group">
        	<label class="col-sm-4"><?php echo __('Option #4'); ?></label>
            <div class="col-sm-8"><input type="text" name="option_values[]"  class="form-control">
            </div>
        </div>

        <div class="form-group">
        	<label class="col-sm-4"><?php echo __('Option #5'); ?></label>
            <div class="col-sm-8"><input type="text" name="option_values[]"  class="form-control">
            </div>
        </div>

        <input type="hidden" name="last_option_id" value="<?php echo $last_option_id; ?>">
        <input type="hidden" name="last_option_position" value="<?php echo $last_option_position; ?>">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
        <button type="submit" class="btn btn-primary"><?php echo __('Add Option'); ?></button>
      </div>
    </form>
    </div><!-- modal-content -->
  </div><!-- modal-dialog -->
</div><!-- modal -->

<script language="javascript">
function updateOption(form_id, element_id, aeo_id, value) {
        var xmlHttpReq1 = false;
        var self1 = this;
        // Mozilla/Safari

        if (window.XMLHttpRequest) {
                self.xmlHttpReq1 = new XMLHttpRequest();
        }
        // IE
        else if (window.ActiveXObject) {
                self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
        }
        self.xmlHttpReq1.open('POST', '/backend.php/forms/bulkoptionupdate', true);
        self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        self.xmlHttpReq1.onreadystatechange = function() {
          if (self.xmlHttpReq1.readyState == 4) {
            document.getElementById('option_stats_' + aeo_id).innerHTML = "Updated!";
          }
        }
        self.xmlHttpReq1.send('form_id=' + form_id + '&element_id=' + element_id + '&aeo_id=' + aeo_id + '&value=' + value);
}

function updatePublishOption(form_id, element_id, aeo_id) {
        var xmlHttpReq1 = false;
        var self1 = this;
        // Mozilla/Safari

        if (window.XMLHttpRequest) {
                self.xmlHttpReq1 = new XMLHttpRequest();
        }
        // IE
        else if (window.ActiveXObject) {
                self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
        }
        self.xmlHttpReq1.open('POST', '/backend.php/forms/bulkoptionpublish', true);
        self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        self.xmlHttpReq1.onreadystatechange = function() {
          if (self.xmlHttpReq1.readyState == 4) {
            document.getElementById('option_stats_' + aeo_id).innerHTML = "<font color='red'>Published!</font>";
          }
        }
        self.xmlHttpReq1.send('form_id=' + form_id + '&element_id=' + element_id + '&aeo_id=' + aeo_id);
}

function updateUnpublishOption(form_id, element_id, aeo_id) {
        var xmlHttpReq1 = false;
        var self1 = this;
        // Mozilla/Safari

        if (window.XMLHttpRequest) {
                self.xmlHttpReq1 = new XMLHttpRequest();
        }
        // IE
        else if (window.ActiveXObject) {
                self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
        }
        self.xmlHttpReq1.open('POST', '/backend.php/forms/bulkoptionunpublish', true);
        self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        self.xmlHttpReq1.onreadystatechange = function() {
          if (self.xmlHttpReq1.readyState == 4) {
            document.getElementById('option_stats_' + aeo_id).innerHTML = "<font color='green'>Deleted!</font>";
          }
        }
        self.xmlHttpReq1.send('form_id=' + form_id + '&element_id=' + element_id + '&aeo_id=' + aeo_id);
}
</script>
<?php
}
else
{
  include_partial("settings/accessdenied");
}
?>
