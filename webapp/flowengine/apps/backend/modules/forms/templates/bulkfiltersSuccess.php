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
                    $element_option->setOption($option);
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

            <div class="panel panel-body padding-0 ">

                <select id="dropdown_field" name="dropdown_field" onChange="window.location='/plan/forms/bulkfilters?id=<?php echo $form->getFormId(); ?>&element_id=' + this.value;" class="form-control" style="margin: 20px; width: 95%;">
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

                        echo "<option value='".$dropdown_field->getElementId()."' ".$selected.">".$dropdown_field->getElementTitle()."</option>";
                    }
                    ?>
                </select>

                <hr>

                <?php
                if($_GET['element_id'])
                {
                    ?>

                    <select id="link_dropdown_field" name="link_dropdown_field" onChange="window.location='/plan/forms/bulkfilters?id=<?php echo $form->getFormId(); ?>&element_id=<?php echo $_GET['element_id']; ?>&link_id=' + this.value;" class="form-control" style="margin: 20px; width: 95%;">
                        <option>Choose A Field...</option>
                        <?php
                        $q = Doctrine_Query::create()
                            ->from("ApFormElements a")
                            ->where("a.form_id = ?", $form->getFormId())
                            ->andWhere("a.element_type = ?", 'select')
                            ->andWhere("a.element_status = 1")
                            ->andWhere("a.element_id <> ?", $_GET['element_id'])
                            ->orderBy("a.element_title ASC");
                        $dropdown_fields = $q->execute();

                        foreach($dropdown_fields as $dropdown_field)
                        {
                            $selected = "";

                            if($_GET['link_id'] == $dropdown_field->getElementId())
                            {
                                $selected = "selected='selected'";
                            }

                            echo "<option value='".$dropdown_field->getElementId()."' ".$selected.">".$dropdown_field->getElementTitle()."</option>";
                        }
                        ?>
                    </select>

                    <hr>

                    <select id="option_field" name="option_field" onChange="window.location='/plan/forms/bulkfilters?id=<?php echo $form->getFormId(); ?>&element_id=<?php echo $_GET['element_id']; ?>&link_id=<?php echo $_GET['link_id']; ?>&filter_option=' + this.value;" class="form-control" style="margin: 20px; width: 95%;">
                        <option>Choose An Option...</option>
                        <?php
                        $q = Doctrine_Query::create()
                            ->from("ApElementOptions a")
                            ->where("a.form_id = ?", $form->getFormId())
                            ->andWhere("a.element_id = ?", $_GET['element_id'])
                            ->orderBy("a.option_text ASC");
                        $filter_options = $q->execute();

                        foreach($filter_options as $option)
                        {
                            $selected = "";

                            if($_GET['filter_option'] == $option->getOptionId())
                            {
                                $selected = "selected='selected'";
                            }

                            echo "<option value='".$option->getOptionId()."' ".$selected.">".$option->getOptionText()."</option>";
                        }
                        ?>
                    </select>

                    <hr>

                    <div class="table-responsive">
                        <table class="table dt-on-steroids mb0" id="table3">
                            <thead>
                            <tr>
                                <th width="10">#</th>
                                <th class="no-sort" width="50%"><?php echo __('Title'); ?></th>
                                <th class="no-sort" width="50%"><?php echo __('Title'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $q = Doctrine_Query::create()
                                ->from("ApElementOptions a")
                                ->where("a.form_id = ?", $form->getFormId())
                                ->andWhere("a.element_id = ?", $_GET['element_id'])
                                ->andWhere("a.option_id = ?",  $_GET['filter_option'])
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
                                        <div id="option_stats_<?php echo $option->getOptionId(); ?>"></div>
                                    </td>
                                    <td>
                                        <ul>
                                        <?php
                                        if($_GET['link_id'])
                                        {
                                          $q = Doctrine_Query::create()
                                              ->from("ApElementOptions a")
                                              ->where("a.form_id = ?", $form->getFormId())
                                              ->andWhere("a.element_id = ?", $_GET['link_id'])
                                              ->orderBy("a.option_text ASC");
                                          $lioptions = $q->execute();

                                          foreach($lioptions as $lioption)
                                          {
                                              $q = Doctrine_Query::create()
                                                  ->from("ApDropdownFilters a")
                                                  ->where("a.form_id = ?", $_GET['id'])
                                                  ->andWhere("a.element_id = ?", $_GET['element_id'])
                                                  ->andWhere("a.link_id = ?", $_GET['link_id'])
                                                  ->andWhere("a.option_id = ?", $option->getOptionId())
                                                  ->andWhere("a.lioption_id = ?", $lioption->getOptionId());
                                              $option_count = $q->count();
                                              ?>
                                              <li><input type="checkbox" <?php if($option_count > 0){ ?>checked="checked"<?php } ?> name="li<?php echo $option->getOptionId(); ?>-<?php echo $lioption->getOptionId(); ?>" onChange="linkFilter(<?php echo $_GET['id']; ?>, <?php echo $_GET['element_id']; ?>, <?php echo $_GET['link_id']; ?>, <?php echo $option->getOptionId(); ?>, <?php echo $lioption->getOptionId(); ?>);"> <?php echo $lioption->getOptionText(); ?></li>
                                              <?php
                                          }
                                        }
                                        ?>
                                        </ul>
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


    <script language="javascript">
        function linkFilter(form_id, element_id, link_id, option_id, lioption_id) {
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
            self.xmlHttpReq1.open('POST', '/plan/forms/bulkfilterupdate', true);
            self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            self.xmlHttpReq1.onreadystatechange = function() {
                if (self.xmlHttpReq1.readyState == 4) {
                    document.getElementById('option_stats_' + option_id).innerHTML = "Updated!";
                }
            }
            self.xmlHttpReq1.send('form_id=' + form_id + '&element_id=' + element_id + '&link_id=' + link_id + '&option_id=' + option_id + '&lioption_id=' + lioption_id);
        }
    </script>
    <?php
}
else
{
    include_partial("settings/accessdenied");
}
?>
