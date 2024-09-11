<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed translation files");

if($sf_user->mfHasCredential("managelanguages"))
{
  $_SESSION['current_module'] = "languages";
  $_SESSION['current_action'] = "translate";
  $_SESSION['current_id'] = "";
?>
<?php
/**
 * indexSuccess.php template.
 *
 * Displays list of available languages
 *
 * @package    backend
 * @subpackage languages
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>
<div class="contentpanel">



      <form id="languageform" name="languageform"  action="/plan/languages/savetranslate" method="post" enctype="multipart/form-data"  autocomplete="off" data-ajax="false">
        <input type="hidden" name="filter" value="<?php echo $filter; ?>">
        <div class="panel panel-default">

        <div class="panel-heading">
          <h3 class="panel-title"><?php echo __('Translate Labels'); ?></h3>
        </div>

        <div class="panel-heading text-right">
                   <button type="submit" class="btn btn-primary" id="newlanguage" href="/plan/languages/new"><?php echo __('Save Translations'); ?></button>
                   <a class="btn btn-primary" id="newlanguage" href="/plan/languages/translate"><?php echo __('Back to Languages'); ?></a>
        </div>

        <div class="panel-heading text-right">
          <select size="1" name="filter" aria-controls="table2" class="select2" onChange="window.location='/plan/languages/translate/filter/' + this.value;">
            <option value="2">Select ...</option>
            <option value="frontend" <?php if(empty($filter) || $filter=="frontend"){ echo "selected"; $filter = "frontend"; } ?>>Frontend</option>
            <option value="backend" <?php if($filter=="backend"){ echo "selected"; } ?>>Backend</option>
          </select>
          </div>



          <div class="panel-body form-bordered form-horizontal">

            <?php
              $q = Doctrine_Query::create()
                 ->from("ExtLocales a")
                 ->orderBy("a.local_title ASC");
              $languages = $q->execute();
              ?>
              <div class="table-responsive">
              <table class="table dt-on-steroids mb0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th width="15%">Key</th>
                    <?php
                    $prefix_folder = dirname(__FILE__)."/../../../../../apps/".$filter."/i18n/";
                    $labels = array();

                    foreach($languages as $language)
                    {
                      echo "<th>".$language->getLocalTitle()."</th>";

                      $labels[$language->getLocaleIdentifier()] = simplexml_load_file($prefix_folder.'messages.'.$language->getLocaleIdentifier().'.xml');
                    }
                    ?>
                    <th></th>
                </tr>
                </thead>
              <tbody>
                <?php
                    $count = 0;

                    foreach($labels['en_US']->file[0]->body[0] as $label)
                    {
                    ?>
                    <tr>
                      <td><?php echo $count+1; ?></td>
                      <td>
                        <input type='text' name='key_locales[]' value="<?php echo $label->source; ?>" readonly="readonly">
                      </td>
                      <?php
                      foreach($languages as $language)
                      {
                        $curr_labels = $labels[$language->getLocaleIdentifier()]->file[0]->body[0];
                        echo "<td><input type='text' name='locale_".$language->getLocaleIdentifier()."[".$count."]' class='form-control' value='".$curr_labels[0]->{'trans-unit'}[$count]->target."'></td>";
                      }
                      ?>
                      <td class="text-right"><a href="#" onclick="removeRow(this)" class="btn btn-danger btn-sm"><span class="fa fa-times"></span></a></td></td>
                    </tr>
                    <?php
                      $count++;
                    }
                    ?>
              </tbody>
            </table>
            </div>

            <div id="more_fields" name="more_fields"></div>

            <button id='add_label' name='add_label' type="button" class="btn btn-primary" style="margin: 10px;"><?php echo __("Add Label"); ?></button>

            <?php
              $field = '<div class="table-responsive"><table class="table dt-on-steroids mb0"><tbody><tr><td>#</td><td><input type="text" name="key_locales[]" value=""></td>';

              foreach($languages as $language)
              {
                $field .= '<td><input type="text" name="locale_'.$language->getLocaleIdentifier().'['.$count.']" class="form-control" value=""></td>';
              }

              $field .= '<td><a href="#" onclick="removeRow(this)">x</a></td></tr></tbody></table></div>';
            ?>

            <script language="javascript">
            removeRow = function(el) {
                $(el).parents("tr").remove()
            }

            jQuery(document).ready(function(){

              var count = <?php echo $count; ?>;

              $( "#add_label" ).click(function() {

                count = count + 1;

                var field = '<div class="table-responsive"><table class="table dt-on-steroids mb0"><tbody><tr><td>#</td><td><input type="text" name="key_locales[]" value=""></td>';

                <?php
                foreach($languages as $language)
                {
                ?>
                field = field.concat('<td><input type="text" name="locale_<?php echo $language->getLocaleIdentifier(); ?>[' + count + ']" class="form-control" value=""></td>');
                <?php
                }
                ?>

                field = field.concat('<td><a href="#" onclick="removeRow(this)">x</a></td></tr></tbody></table></div>');

                $("#more_fields").append(field);
              });
            });
            </script>

          </div>
    </div>

    </form>

  </div>

<?php
}
else
{
  include_partial("settings/accessdenied");
}
?>
