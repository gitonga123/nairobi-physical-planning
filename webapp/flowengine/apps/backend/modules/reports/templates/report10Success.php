<?php use_helper('I18N', 'Date') ?>
<?php
/**
 * report10 template.
 *
 * Report of all activities with a certain period of time.
 *
 * @package    backend
 * @subpackage reports
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 
/**
*
* Function to get all the dates between a period of time
*
* @param String $sStartDate Start date to begin fetching dates from
* @param String $sEndDate End date where to stop fetching dates from
*
* @return String[]
*/
?>
<div class="pageheader">
  <h2><i class="fa fa-home"></i><?php echo __('Audit Trail'); ?></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __('You are here'); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/backend.php"><?php echo __('Reports'); ?></a></li>
      <li class="active"><?php echo __('Audit Trail'); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
	<div class="panel panel-default radius-all">
		<div class="panel-body padding-0">

			<div class="table-responsive">
				<table class="table table-striped table-hover mb0 radius-bl radius-br ">
					<thead>
					<tr>
							<th width="60">ID</th>
							<th>Reviewer</th>
							<th>Date/Time</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$count = 0;
					foreach($pager->getResults() as $audit)
					{
						?>
						<?php 
						$q = Doctrine_Query::create()
							->from('cfUser a')
							->where('a.nid = ?', $audit->getUserId());
						$thisuser = $q->fetchOne();
						if($thisuser)
						{
							$count++;
						?>
						<tr>
							<td><?php echo $count; ?></td>
							<td>
							<a href='/backend.php/users/showuser?id=<?php echo $thisuser->getNid(); ?>'>
							<?php
							echo $thisuser->getStrlastname()." ".$thisuser->getStrfirstname();
							?></a>
							</td>
							<td><?php echo $audit->getActionTimestamp(); ?></td>
							<td><?php 
							if($audit->getFormEntryId())
							{
								$q = Doctrine_Query::create()
								   ->from("FormEntry a")
								   ->where("a.id = ?", $audit->getFormEntryId());
								$application = $q->fetchOne();
								if($application)
								{
									echo "<a href='/backend.php/applications/view/id/".$application->getId()."'>".$application->getApplicationId()."</a> - ";
								}
							}

							$action = html_entity_decode($audit->getAction());
							$action = str_replace("<a", "<b", $action);
							$action = str_replace("/a>", "/b>", $action);

							echo $action; 
							?></td>
						</tr>
						<?php
						}
					}
					?>
					</tbody>
					<tfoot>
						<tr>
						<th class="radius-bl radius-br" colspan="12">
						<p class="table-showing pull-left"><strong><?php echo count($pager) ?></strong> Actions

						<?php if ($pager->haveToPaginate()): ?>
							- page <strong><?php echo $pager->getPage() ?>/<?php echo $pager->getLastPage() ?></strong>
						<?php endif; ?></p>

						<?php if ($pager->haveToPaginate()): ?>
							<ul class="pagination pagination-sm mb0 mt0 pull-right">
							<li><a href="/backend.php/reports/report10/page/1<?php if($reviewer){ echo "/reviewer/".$reviewer; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>">
								<i class="fa fa-angle-left"></i>
							</a></li>

							<li><a href="/backend.php/reports/report10/page/<?php echo $pager->getPreviousPage() ?><?php if($filter){ echo "/filter/".$filter; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>">
								<i class="fa fa-angle-left"></i>
							</a></li>

							<?php foreach ($pager->getLinks() as $page): ?>
								<?php if ($page == $pager->getPage()): ?>
								<li class="active"><a href=""><?php echo $page ?></li></a>
								<?php else: ?>
								<li><a href="/backend.php/reports/report10/page/<?php echo $page ?><?php if($reviewer){ echo "/reviewer/".$reviewer; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>"><?php echo $page ?></a></li>
								<?php endif; ?>
							<?php endforeach; ?>

							<li><a href="/backend.php/reports/report10/page/<?php echo $pager->getNextPage() ?><?php if($reviewer){ echo "/reviewer/".$reviewer; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>">
								<i class="fa fa-angle-right"></i>
							</a></li>

							<li><a href="/backend.php/reports/report10/page/<?php echo $pager->getLastPage() ?><?php if($reviewer){ echo "/reviewr/".$reviewer; } ?><?php if($fromdate){ echo "/fromdate/".$fromdate."/todate/".$todate; } ?>">
								<i class="fa fa-angle-right"></i>
							</a>
							</li>
							</ul>
						<?php endif; ?>
						</th>
						</tr>
                	</tfoot>
				</table>
			</div>

		</div>
	</div>

	<div align="center">
		<a class="btn btn-primary" href="/backend.php/reports/printreport10">Print Report</a>
	</div>
</div>