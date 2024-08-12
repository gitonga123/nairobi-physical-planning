<?php
/**
 * indexSuccess.php template.
 *
 * Displays client dashboard
 *
 * @package    frontend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php
				$status = "";

				if($profile->getDeleted())
				{
					$status = " <span class='label label-danger'>".__("Not Active")."</span>";
				}

				echo $profile->getTitle().$status;
			?>
			<a href="plan/profile/view/id/<?php echo $profile->getId(); ?>" class="btn btn-primary btn-sm pull-right" style="margin-top: -4px; color: #FFFFFF; margin-left: 5px;  margin-right: 5px;"><?php echo __("Back to Profile"); ?></a>
        </h3>
    </div>
    <div class="panel-body">

    <div class="panel-body">
        <link rel="stylesheet" type="text/css" href="/form_builder/css/edit_entry.css" media="all">
        <link rel="stylesheet" type="text/css" href="/form_builder/view.mobile.css" media="all">

        <div id="main_body" class=" integrated">
            <div id="form_container" style="padding-top: 5px;">

                <form class="appnitro 1 top_label" method="post" data-highlightcolor="#FFF7C0" action="plan/profile/saveuser">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo __("Add a user to"); ?> <?php echo $profile->getTitle(); ?></h3>
                        <p><?php echo __("This form enables you to add user to a business who can submit applications through the business"); ?></p>
                    </div>
                    <ul>
                        <li id="li_2">
                            <label class="description" for="element_2"><?php echo __("ID number of user"); ?> <span id="required_2" class="required">*</span></label>
                            <div>
                                <input id="element_2" name="id_number" class="element text medium" type="text" value="">
                            </div> 
                        </li>
                        <li id="li_buttons" class="buttons">
                            <input id="submit_form" class="bb_button bb_green" type="submit" name="submit_form" value="<?php echo __("Add User"); ?>">
                        </li>
                    </ul>
                </form>
            
            </div>
        </div>

	</div>
</div>
