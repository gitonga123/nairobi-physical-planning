<?php
/**
 * viewSuccess.php template.
 *
 * Displays business profile
 *
 * @package    backend
 * @subpackage profiles
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");

$application_manager = new ApplicationManager();
$entry_details = $application_manager->get_entry_details($business->getFormId(), $business->getEntryId());

try
{
	$business_manager = new BusinessManager();
	error_log('Cyclic-b: Checking '.$business->getTitle().' for with cyclic billing');
	$business_manager->generate_cyclic_bills($business->getId());
}catch(Exception $ex)
{
	error_log('Cyclic-b: permitflow: '.$ex->getMessage());
}
?>
<div class="pageheader">
  <h2><i class="fa fa-envelope"></i> <?php echo __('Users'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/backend.php"><?php echo __('Home'); ?></a></li>
      <li><a href="/backend.php/frusers/index"><?php echo __('Users'); ?></a></li>
    </ol>
  </div>
</div>

<div class="contentpanel">


		<div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title"><?php echo strtoupper($business->getTitle()); ?></h3>
        </div>


        <div class="panel-heading text-right p-10">
    <?php
	if($sf_user->mfHasCredential("can_inspect"))
	{
    ?>
            <a class="btn btn-default btn-sm m-r-10" id="newpage" href="/backend.php/profiles/inspect/id/<?php echo $business->getId(); ?>" >
              <span class="fa fa-edit"></span>
              <?php echo __('Inspect Business'); ?>
            </a>
    <?php
	}
    ?>

    <?php
      if($business->getDeleted())
      {
        ?>
        <a class="btn btn-default btn-sm" href="/backend.php/profiles/activate/id/<?php echo $business->getId(); ?>"><span class="fa fa-edit"></span>  <?php echo __('Activate Business'); ?></a>
        <?php
      }
      else
      {
        ?>
        <a class="btn btn-default btn-sm m-r-10" href="/backend.php/profiles/deactivate/id/<?php echo $business->getId(); ?>"><span class="fa fa-power-off"></span> <?php echo __('Deactivate Business'); ?></a>
        <?php
      }
    ?>

    <a class="btn btn-default btn-sm" href="/backend.php/profiles/transfer/id/<?php echo $business->getId(); ?>"><span class="fa fa-share"></span> <?php echo __('Transfer Business'); ?></a>
        </div>

		<div class="panel-body p-0">


      <div class="panel with-nav-tabs panel-default">
                  <div class="panel-heading">
                          <ul class="nav nav-tabs">
                            <li class="active"><a href="#vtab0" data-toggle="tab"><span class="fa fa-bars"></span> <?php echo __('Details'); ?></a></li>
                            <li><a href="#vtab2" data-toggle="tab"><span class="fa fa-users"></span> <?php echo __('Users'); ?></a></li>
                            <li><a href="#vtab3" data-toggle="tab"><span class="fa fa-users"></span> <?php echo __('Services'); ?></a></li>
                            <li><a href="#vtab4" data-toggle="tab"><span class="fa fa-money"></span> <?php echo __('Payments'); ?></a></li>
                            <li><a href="#vtab5" data-toggle="tab"><span class="fa fa-certificate"></span> <?php echo __('Inspections'); ?></a></li>
                          </ul>
                  </div>
                  <div class="panel-body">


			<div id="validationWizard" class="tab-content tab-content-nopadding">

				<div class="tab-pane form-bordered form-horizontal p20 active" id="vtab0">

					<?php 
					$q = Doctrine_Query::create()
					   ->from("ApFormPayments a")
					   ->where("a.payment_id = ?", $business->getFormId()."/".$business->getEntryId()."/".$business->getId())
					   ->andWhere("a.payment_status = ?", "pending")
					   ->orderBy("a.afp_id DESC")
					   ->limit(1);
					$payments = $q->execute();
					foreach($payments as $payment)
					{
					?>
					<div class="alert alert-success">
						<h4><?php echo __("Profile Activation Payment"); ?></h4> <?php echo __("Payment Reference"); ?>: <?php echo $business->getFormId()."/".$business->getEntryId()."/".$business->getId(); ?>. <br><br>
						<a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/confirm/<?php echo md5($business->getFormId()."/".$business->getEntryId()."/".$business->getId()); ?>" class="btn btn-success"><?php echo __("Confirm Payment"); ?></a>
					</div>
					<?php 
					}
					?>

					 <div class="table-responsive">
						<table class="table table-card-box m-b-0">
							<tbody>
							<?php
									$toggle = false;

									$business_json = html_entity_decode($business->getFormData());

									$business_data = json_decode($business_json, true);

									foreach($business_data as $row)
									{
										$row_markup = "";
										$row_markup .= "<tr {$row_style}>\n";
										$row_markup .= "<td><strong>{$row['label']}</strong></td>\n";
										$row_markup .= "<td>".nl2br($row['value'])."</td>\n";
										$row_markup .= "</tr>\n";

										echo $row_markup;
									}
							?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="tab-pane form-bordered form-horizontal p20" id="vtab2">
					<?php
					$user = $business->getUser();
        			$user_profile = $user->getSfGuardUserProfile();
					?>
					<table class="table table-card-box m-b-0">
						<thead>
							<tr>
							    <th><?php echo __('Full Name'); ?></th>
								<th><?php echo __('ID Number'); ?></th>
								<th><?php echo __('Email'); ?></th>
								<th><?php echo __('Mobile Number'); ?></th>
							</tr>

					<tr>
						<td><?php echo $user_profile->getFullname(); ?></td>
						<td><?php echo $user->getUsername(); ?></td>
						<td><?php echo $user->getEmailAddress(); ?></td>
						<td><?php echo $user_profile->getMobile(); ?></td>
					</tr>

					<?php
					foreach($users as $user_share)
					{
						$user = $user_share->getUser();
						$user_profile = $user->getSfGuardUserProfile();
						?>
							<tr>
							<td><?php echo $user_profile->getFullname(); ?></td>
							<td><?php echo $user->getUsername(); ?></td>
							<td><?php echo $user->getEmailAddress(); ?></td>
							<td><?php echo $user_profile->getMobile(); ?></td>
							</tr>
						<?php
					}
					?>

					</table>

				</div>
                <div class="tab-pane form-bordered form-horizontal p20" id="vtab3">
					<div class="table-responsive">
						<table class="table table-striped table-special m-b-0">
							<thead>
								<tr>
									<th><?php echo __("Service"); ?></th>
									<th><?php echo __("Ref No"); ?></th>
									<th><?php echo __("Status"); ?></th>
									<th class="text-right"><?php echo __("Actions"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($latest_services->getResults() as $service): ?>
								<tr>
									<td>
										<?php echo $service->getTitle(); ?>
										<h1><?php echo html_entity_decode($service->getStage()->getMenus()->getTitle()); ?></h1>
										<p><?php echo date('d F Y H:m:s', strtotime($service->getDateOfSubmission())); ?></p>
									</td>
									<td><?php echo $service->getApplicationId(); ?></td>
									<td><span class="label label-success"><?php echo $service->getStatusName(); ?></span></td>
									<td class="text-right">
										<a class="label label-primary"  title='<?php echo __('View Service'); ?>' href='/backend.php/applications/view/id/<?php echo $service->getId(); ?>'><span class="fa fa-eye"></span></a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
							<tfoot>
							<tr>
								<th colspan="12">
									<p class="table-showing pull-left"><strong><?php echo $latest_services->getNbResults(); ?></strong> <?php echo __("services in this profile"); ?>

										<?php if ($latest_services->haveToPaginate()): ?>
											- <?php echo __("page"); ?> <strong><?php echo $latest_services->getPage() ?>/<?php echo $latest_services->getLastPage() ?></strong>
										<?php endif; ?></p>

									<?php if ($latest_services->haveToPaginate()): ?>
										<ul class="pagination pagination-sm mb0 mt0 pull-right">
											<li><a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/apage/1">
													<i class="fa fa-angle-left"></i>
												</a></li>

											<li> <a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/apage/<?php echo $latest_services->getPreviousPage() ?>">
													<i class="fa fa-angle-left"></i>
												</a></li>

											<?php foreach ($latest_services->getLinks() as $page): ?>
												<?php if ($page == $latest_services->getPage()): ?>
													<li class="active"><a href=""><?php echo $page ?></a>
												<?php else: ?>
													<li><a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/apage/<?php echo $page ?>"><?php echo $page ?></a></li>
												<?php endif; ?>
											<?php endforeach; ?>

											<li> <a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/apage/<?php echo $latest_services->getNextPage() ?>">
													<i class="fa fa-angle-right"></i>
												</a></li>

											<li> <a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/apage/<?php echo $latest_services->getLastPage() ?>">
													<i class="fa fa-angle-right"></i>
												</a></li>
										</ul>
									<?php endif; ?>

								</th>
							</tr>
							</tfoot>
						</table>
					</div>
				</div>

				<div class="tab-pane form-bordered form-horizontal p20" id="vtab4">
					<div class="table-responsive">
						<table class="table table-striped table-special m-b-0">
							<thead>
								<tr>
									<th><?php echo __("Service"); ?></th>
									<th><?php echo __("Bill Ref No"); ?></th>
									<th><?php echo __("Bill Status"); ?></th>
									<th><?php echo __("Amount"); ?></th>
									<th><?php echo __("Due Date"); ?></th>
									<th class="text-right"><?php echo __("Actions"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($latest_invoices->getResults() as $invoice): ?>
								<?php $application = $invoice->getFormEntry(); ?>
								<tr>
									<td>
										<?php echo $application->getTitle(); ?>
										<h1><?php echo html_entity_decode($application->getStage()->getMenus()->getTitle()); ?></h1>
										<p><?php echo date('d F Y H:m:s', strtotime($application->getDateOfSubmission())); ?></p>
									</td>
									<td><?php echo $application->getFormId()."/".$application->getEntryId()."/".$invoice->getId(); ?></td>
									<td><span class="label <?php if($invoice->getPaid() != 2 ){ ?>label-danger<?php }else{ ?>label-success<?php } ?>"><?php echo $invoice->getStatus(); ?></span></td>
									<td><?php echo $invoice->getCurrency()." ".$invoice->getTotalAmount(); ?></td>
									<td><?php echo $invoice->getCreatedAt(); ?></td>
									<td class="text-right">
										<a class="label label-primary"  title='<?php echo __('View Invoice'); ?>' href='/backend.php/applications/viewinvoice/id/<?php echo $invoice->getId(); ?>'><span class="fa fa-eye"></span></a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
							<tfoot>
							<tr>
								<th colspan="12">
									<p class="table-showing pull-left"><strong><?php echo $latest_invoices->getNbResults(); ?></strong> <?php echo __("invoices in this stage"); ?>

										<?php if ($latest_invoices->haveToPaginate()): ?>
											- <?php echo __("page"); ?> <strong><?php echo $latest_invoices->getPage() ?>/<?php echo $latest_invoices->getLastPage() ?></strong>
										<?php endif; ?></p>

									<?php if ($latest_invoices->haveToPaginate()): ?>
										<ul class="pagination pagination-sm mb0 mt0 pull-right">
											<li><a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/mpage/1">
													<i class="fa fa-angle-left"></i>
												</a></li>

											<li> <a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/mpage/<?php echo $latest_invoices->getPreviousPage() ?>">
													<i class="fa fa-angle-left"></i>
												</a></li>

											<?php foreach ($latest_invoices->getLinks() as $page): ?>
												<?php if ($page == $latest_invoices->getPage()): ?>
													<li class="active"><a href=""><?php echo $page ?></a>
												<?php else: ?>
													<li><a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/mpage/<?php echo $page ?>"><?php echo $page ?></a></li>
												<?php endif; ?>
											<?php endforeach; ?>

											<li> <a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/mpage/<?php echo $latest_invoices->getNextPage() ?>">
													<i class="fa fa-angle-right"></i>
												</a></li>

											<li> <a href="/backend.php/profiles/view/id/<?php echo $business->getId(); ?>/mpage/<?php echo $latest_invoices->getLastPage() ?>">
													<i class="fa fa-angle-right"></i>
												</a></li>
										</ul>
									<?php endif; ?>

								</th>
							</tr>
							</tfoot>
						</table>
					</div>
				</div>

				<div class="tab-pane form-bordered form-horizontal p20" id="vtab5">
					<div class="table-responsive">
						<table class="table m-b-0">
							<thead>
								<tr>
									<th><?php echo __("Reviewer"); ?></th>
									<th><?php echo __("Date Of Inspection"); ?></th>
									<th class="text-right"><?php echo __("Actions"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($inspections as $inspection): ?>
								<tr>
									<td>
										<?php
											$reviewer = $inspection->getReviewer();
											echo $reviewer->getStrfirstname()." ".$reviewer->getStrlastname();
										?>
									</td>
									<td><?php echo $inspection->getCreatedAt(); ?></td>
									<td class="text-right">
										<a class="label label-primary"  title='<?php echo __('View Service'); ?>' href='/backend.php/profiles/viewinspection/id/<?php echo $inspection->getId(); ?>'><span class="fa fa-eye"></span></a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>

			</div>






    </div>
</div>


  </div> <!---End panel body->

    </div>
