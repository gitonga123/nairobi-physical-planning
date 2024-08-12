<?php
/**
 * groupsSuccess.php template.
 *
 * Displays list of all of the categories of applications
 *
 * @package    frontend
 * @subpackage application
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');
?>
<div class="panel-group" id="accordion-test-2">
    <?php
$count = 0;
foreach ($groups as $group) {
	$count++;

	$form_count = 0;
	$q = Doctrine_Query::create()
		->from('ApForms a')
		->where('a.form_group = ?', $group->getGroupId())
		->andWhere('a.form_type = 1')
		->andWhere('a.form_active = 1')
		->orderBy('a.form_name ASC');
	$applications = $q->execute();
	foreach ($applications as $application) {
		//Check if enable_categories is set, if it is then filter application forms
		if (sfConfig::get('app_enable_categories') == "yes") {
			$q = Doctrine_Query::create()
				->from('sfGuardUserCategoriesForms a')
				->where('a.categoryid = ?', $sf_user->getGuardUser()->getProfile()->getRegisteras())
				->andWhere('a.formid = ?', $application->getFormId());
			$category = $q->count();
			if ($category == 0) {
				continue;
			} else {
				$form_count++;
			}
		} else {
			//If form category permissions is disabled and then just display the category
			$form_count++;
		}
	}
	if ($form_count == 0) {
		continue;
	}
	?>
    <!--OTB Start: Only show forms for those who have provided additional details-->
    <?php
$membersManager = new MembersManager();
	$membership = $membersManager->MembershipIsValidated($sf_user->getGuardUser()->getId());
	if ($membership and $membership['validated']):
	?>
    <!--OTB End: Only show forms for those who have provided additional details-->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion-test-2" href="#collapseTwo-<?php echo $count; ?>"
                class="collapsed" aria-expanded="false">
                <?php echo $group->getGroupName(); ?>
            </a>
            </h4>
        </div>
        <div id="collapseTwo-<?php echo $count; ?>" class="<?php //panel-collapse collapse ?>">
            <div class="panel-body">
                <ul class="list-group list-group-forms m-b-0">
                    <?php
$q = Doctrine_Query::create()
		->from('ApForms a')
		->andWhere('a.form_type = 1')
		->andWhere('a.form_active = 1')
		->andWhere('a.form_group = ?', $group->getGroupId())
		->orderBy('a.form_name ASC');
	$forms = $q->execute();
	foreach ($forms as $form) {
		if (sfConfig::get('app_enable_categories') == "yes") {
			$q = Doctrine_Query::create()
				->from('sfGuardUserCategoriesForms a')
				->where('a.categoryid = ?', $sf_user->getGuardUser()->getProfile()->getRegisteras())
				->andWhere('a.formid = ?', $form->getFormId());
			$category = $q->count();
			if ($category == 0) {
				continue;
			}
		}
		?>
                    <li class="list-group-item">
                        <a href="/index.php//forms/info?id=<?php echo $form->getFormId(); ?>"><?php echo $form->getFormName() ?></a>
                    </li>
                    <?php
}
	?>
                    <ul>
                    </div>
                </div>
            </div>
            <?php
else:
		$q = Doctrine_Query::create()
			->from('sfGuardUserCategories a')
			->where('a.id = ?', $sf_user->getGuardUser()->getProfile()->getRegisteras());
		$actual_category = $q->fetchOne();
		?>
	            <div class="blog-details" style="border-top: 1px solid #d2d2d2; background:#fff;">
	                <div class="blog-summary">
	                    <h4 style="color:red"><b><i class="fa fa-exclamation-triangle fa-5x"></i><?php echo __('ATTENTION'); ?> <?php echo strtoupper($actual_category->getName()); ?> - <?php echo __('PLEASE PROVIDE US WITH YOUR MEMBERSHIP DETAILS'); ?></b></h4>
	                    <p><?php echo __('If you are viewing this message, you cannot make any submissions because'); ?>:</p>
	                    <ul>
	                        <li>1. <?php echo __('You have not provided us with additional details for your user category or'); ?>;</li>
	                        <li>2. <?php echo __('You need to update your professional membership details after annual renewal and you must validate this update by clicking the link sent to your membership email.'); ?></li>
	                    </ul>
	                    <p><?php echo __('You must ensure you are duly registered with '); ?><b><?php echo $actual_category->getMemberAssociationName(); ?></b><?php echo __(' as a '); ?><b><?php echo $actual_category->getName(); ?>.</b></p>
	                    <p><?php echo __('To provide additional details, follow the steps below'); ?>:</p>
	                    <ol>
	                        <li><?php echo __('Click on your Name at the top right of the screen.'); ?></li>
	                        <li><?php echo __('Click on'); ?> <a href="/index.php//settings"><?php echo __('Account Settings'); ?></a>.</li>
	                        <li><?php echo __('Click on the "Edit Additional Details" tab.') ?></li>
	                        <li><?php echo __('Click the "Add Additional Details" button, give your details and submit.'); ?></li>
	                    </ol>
	                    <?php if (strlen($membership['member_no'])): ?>
	                    <p><?php echo __('If you have still not received a verification email in your inbox, kindly click the button below.'); ?></p>
	                    <?php if (strlen($sf_user->getAttribute('boraqs_reset', ''))): ?>
	                    <div class="alert alert-warning">
	                        <p><?php echo $sf_user->getAttribute('boraqs_reset') ?></p>
	                    </div>
	                    <?php endif;?>
                    <?php $sf_user->getAttributeHolder()->remove('boraqs_reset');?>
                    <p><a href="<?php echo '/index.php//membersdatabase/resendboraq' ?>" class="btn btn-warning"><?php echo __('Resend Verification Email'); ?></a></p>
                    <?php endif;?>
                </div>
            </div>
            <?php
break;
	endif;
	?>
            <!--OTB End: Only show forms for those who have provided additional details-->
            <?php
}
?>
        </div>