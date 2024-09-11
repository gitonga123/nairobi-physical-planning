<?php
use_helper("I18N");

$audit = new Audit();
$audit->saveAudit("", "Accessed translation files");

$translation = new Translation();

if($sf_user->mfHasCredential("managelanguages"))
{
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
        <div class="panel panel-dark">



            <form id="languageform" name="languageform"  action="/plan/forms/savetranslate/id/<?php echo $_GET['id']; ?>/service/<?php echo $_GET['filter'] ?>" method="post" enctype="multipart/form-data"  autocomplete="off" data-ajax="false">
                <input type="hidden" name="filter" value="<?php echo $filter; ?>">

                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Translate Labels'); ?></h3>

                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary-alt settings-margin42"><?php echo __('Save Translations'); ?></button>
                        <a class="btn btn-primary-alt settings-margin42" href="<?php echo public_path('backend.php/forms/index/filter/'.$_GET['filter'])?>"><?php echo __('Back to Forms'); ?></a>
                    </div>
                </div>

                <div class="panel panel-dark">

                    <div class="panel-body panel-body-nopadding form-bordered form-horizontal">

                        <?php
                        $q = Doctrine_Query::create()
                            ->from("ExtLocales a")
                            ->orderBy("a.local_title ASC");
                        $languages = $q->execute();

                        $q = Doctrine_Query::create()
                            ->from("ApFormElements a")
                            ->where("a.form_id = ?", $_GET['id'])
                            ->andWhere("a.element_status = 1")
                            ->orderBy("a.element_position ASC");
                        $elements = $q->execute();

                        $q = Doctrine_Query::create()
                            ->from("ApForms a")
                            ->where("a.form_id = ?", $_GET['id']);
                        $form = $q->fetchOne();
                        ?>
                        <div class="table-responsive">
                            <hr>
                            <h3 style="padding: 10px;"><?php echo __('Form Properties'); ?></h3>
                            <hr>
                            <table class="table dt-on-steroids mb0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th width="15%">Key</th>
                                    <?php
                                    foreach($languages as $language)
                                    {
                                        echo "<th>".$language->getLocalTitle()."</th>";
                                    }
                                    ?>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>
                                            <textarea name='form_key_locales[]' readonly='readonly'><?php echo $form->getFormName(); ?></textarea>
                                        </td>
                                        <?php
                                        foreach($languages as $language)
                                        {
                                            $trans_text = $translation->getTranslationManual("ap_forms", "form_name", $_GET['id'], $language->getLocaleIdentifier());

                                            if($trans_text)
                                            {
                                                echo '<td><textarea name="locale_' . $language->getLocaleIdentifier() . '_form_name_'.$language->getLocaleIdentifier().'" class="form-control">'.$trans_text.'</textarea></td>';
                                            }
                                            else {
                                                echo '<td><textarea name="locale_' . $language->getLocaleIdentifier() . '_form_name_'.$language->getLocaleIdentifier().'" class="form-control"></textarea></td>';
                                            }
                                        }
                                        ?>
                                    </tr>

                                    <tr>
                                        <td>2</td>
                                        <td>
                                            <textarea name='form_key_locales[]' readonly='readonly'><?php echo $form->getFormDescription(); ?></textarea>
                                        </td>
                                        <?php
                                        foreach($languages as $language)
                                        {
                                            $trans_text = $translation->getTranslationManual("ap_forms", "form_description", $_GET['id'], $language->getLocaleIdentifier());

                                            if($trans_text)
                                            {
                                                echo '<td><textarea name="locale_' . $language->getLocaleIdentifier() . '_form_description_'.$language->getLocaleIdentifier().'" class="form-control">'.$trans_text.'</textarea></td>';
                                            }
                                            else {
                                                echo '<td><textarea name="locale_' . $language->getLocaleIdentifier() . '_form_description_'.$language->getLocaleIdentifier().'" class="form-control"></textarea></td>';
                                            }
                                        }
                                        ?>
                                    </tr>

                                    <tr>
                                        <td>3</td>
                                        <td>
                                            <textarea name='form_key_locales[]' readonly='readonly'><?php echo $form->getFormSuccessMessage(); ?></textarea>
                                        </td>
                                        <?php
                                        foreach($languages as $language)
                                        {
                                            $trans_text = $translation->getTranslationManual("ap_forms", "form_success_message", $_GET['id'], $language->getLocaleIdentifier());

                                            if($trans_text)
                                            {
                                                echo '<td><textarea name="locale_' . $language->getLocaleIdentifier() . '_form_success_message_'.$language->getLocaleIdentifier().'" class="form-control">'.$trans_text.'</textarea></td>';
                                            }
                                            else {
                                                echo '<td><textarea name="locale_' . $language->getLocaleIdentifier() . '_form_success_message_'.$language->getLocaleIdentifier().'" class="form-control"></textarea></td>';
                                            }
                                        }
                                        ?>
                                    </tr>
                                </tbody>
                            </table>
                            <hr>
                            <h3 style="padding: 10px;"><?php echo __('Form Elements'); ?></h3>
                            <hr>
                            <table class="table dt-on-steroids mb0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th width="15%">Key</th>
                                    <?php
                                    foreach($languages as $language)
                                    {
                                        echo "<th>".$language->getLocalTitle()."</th>";
                                    }
                                    ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $count = 0;

                                foreach($elements as $element)
                                {
                                    //Display element title
                                    ?>
                                    <tr>
                                        <td><?php echo $count+1; ?></td>
                                        <td>
                                            <textarea name='key_locales[]' readonly='readonly'><?php echo $element->getElementTitle(); ?></textarea>
                                        </td>
                                        <?php
                                        foreach($languages as $language)
                                        {
                                            $trans_text = $translation->getOptionTranslationManual("ap_form_elements", "element_title", $_GET['id'], $element->getElementId(), $language->getLocaleIdentifier());

                                            if($trans_text)
                                            {
                                                echo '<td><textarea name="locale_' . $language->getLocaleIdentifier() . '[' . $element->getElementId() . ']" class="form-control">'.$trans_text.'</textarea></td>';
                                            }
                                            else {
                                                echo '<td><textarea name="locale_' . $language->getLocaleIdentifier() . '[' . $element->getElementId() . ']" class="form-control"></textarea></td>';
                                            }
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                    $count++;

                                    //Display element guideline if not empty
                                    if($element->getElementGuidelines()) {
                                        ?>
                                        <tr>
                                            <td><?php echo $count + 1; ?></td>
                                            <td>
                                                <textarea name='key_locales[]' readonly='readonly'><?php echo $element->getElementGuidelines(); ?></textarea>
                                            </td>
                                            <?php
                                            foreach ($languages as $language) {
                                                $trans_text = $translation->getOptionTranslationManual("ap_form_elements", "element_guidelines", $_GET['id'], $element->getElementId(), $language->getLocaleIdentifier());

                                                if($trans_text)
                                                {
                                                    echo '<td><textarea name="locale_guideline_' . $language->getLocaleIdentifier() . '[' . $element->getElementId() . ']" class="form-control">' . $trans_text . '</textarea></td>';
                                                } else {
                                                    echo '<td><textarea name="locale_guideline_' . $language->getLocaleIdentifier() . '[' . $element->getElementId() . ']" class="form-control"></textarea></td>';
                                                }
                                            }
                                            ?>
                                        </tr>
                                        <?php
                                        $count++;
                                        }

                                        //Display element options
                                        $q = Doctrine_Query::create()
                                            ->from("ApElementOptions a")
                                            ->where("a.form_id = ?", $_GET['id'])
                                            ->andWhere("a.element_id = ?", $element->getElementId())
                                            ->andWhere("a.live = 1");
                                        $options = $q->execute();

                                        foreach ($options as $option) {
                                            ?>
                                            <tr>
                                                <td><?php echo $count + 1; ?></td>
                                                <td>
                                                    <input type='text' name='key_locales[]'
                                                           value="<?php echo $option->getOptionText(); ?>"
                                                           readonly="readonly">
                                                </td>
                                                <?php
                                                foreach ($languages as $language) {
                                                    $trans_text = $translation->getOptionTranslationManual("ap_element_options", "option_text", $option->getAeoId(), $option->getOptionId(), $language->getLocaleIdentifier());

                                                    if($trans_text){
                                                        echo '<td><input type="text" name="locale_option_' . $element->getElementId() . '_' . $language->getLocaleIdentifier() . '[' . $option->getAeoId() . ']" class="form-control" value="' . $trans_text . '"></td>';
                                                    } else {
                                                        echo '<td><input type="text" name="locale_option_' . $element->getElementId() . '_' . $language->getLocaleIdentifier() . '[' . $option->getAeoId() . ']" class="form-control" value=""></td>';
                                                    }
                                                }
                                                ?>
                                            </tr>
                                            <?php
                                            $count++;
                                        }
                                }
                                ?>
                                </tbody>
                            </table>
                            <hr>
                            <h3 style="padding: 10px;"><?php echo __('Logic Settings'); ?></h3>
                            <hr>
                            <table class="table dt-on-steroids mb0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th width="15%">Key</th>
                                    <?php
                                    foreach($languages as $language)
                                    {
                                        echo "<th>".$language->getLocalTitle()."</th>";
                                    }
                                    ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $count = 0;

                                $q = Doctrine_Query::create()
                                   ->from("ApFieldLogicConditions a")
                                   ->where("a.form_id = ?", $_GET["id"]);
                                $conditions = $q->execute();

                                foreach($conditions as $condition) {
                                    $count++;
                                    //Display element title
                                    ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <td>
                                            <textarea name='condition_key_locales[<?php echo $condition->getAlcId(); ?>]'
                                                      readonly='readonly'><?php echo $condition->getRuleKeyword(); ?></textarea>
                                        </td>
                                        <?php
                                        foreach ($languages as $language) {
                                            $trans_text = $translation->getTranslationManual("field_logic_conditions", "rule_keyword", $condition->getAlcId(), $language->getLocaleIdentifier());

                                            if ($trans_text) {
                                                echo '<td><textarea name="condition_locale_' . $language->getLocaleIdentifier() . '_' . $condition->getAlcId() . '" class="form-control">' . $trans_text . '</textarea></td>';
                                            } else {
                                                echo '<td><textarea name="condition_locale_' . $language->getLocaleIdentifier() . '_' . $condition->getAlcId() . '" class="form-control"></textarea></td>';
                                            }
                                        }
                                        ?>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <?php
}
else
{
    include_partial("settings/accessdenied");
}
?>
