<?php
/**
 * _comments_conditions template.
 *
 * Shows summary of conditions from all reviewers
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

/*$q = Doctrine_Query::create()
     ->from('CfUser a')
     ->where('a.nid = ?', $sf_user->getAttribute('userid'));
$reviewer = $q->fetchOne();*/

$q = Doctrine_Query::create()
	  ->from('Permits a')
	  ->where('a.applicationform = ?', $application->getFormId());
$permit = $q->fetchOne();

if($permit)
{
?>
<div class="table-responsive">
<table class="table dt-on-steroids mb0" id="table3">
	<thead>
	<tr><th>#</th><th style="min-width: 300px;">Description</th><th style="width: 150px;">Selected?</th></tr>
		</thead>
		<tbody>
		<?php
		$q = Doctrine_Query::create()
		   ->from('ConditionsOfApproval a')
		   ->where('a.permit_id = ?', $permit->getId())
		   ->orderBy('a.short_name ASC');
		$conditions = $q->execute();
		foreach($conditions as $condition)
		{
					$q = Doctrine_Query::create()
					   ->from('ApprovalCondition a')
					   ->where('a.entry_id = ?', $application->getId())
					   ->andWhere('a.condition_id = ?', $condition->getId());
					$cnd = $q->fetchOne();

					$resolved = "";
					if(empty($cnd))
					{
						 $resolved = $resolved."<span class='glyphicon glyphicon-remove'></span>";
					}
					else
					{
						 $resolved = $resolved."<span class='glyphicon glyphicon-ok'></span>";
					}

					echo "<tr><td>".$condition->getShortName()."</td><td>".$condition->getDescription()."</td><td><div id='cn_".$condition->getId()."'>".$resolved."</div></td></tr>";
		}
		?>
		</tbody>
</table>
</div>
<?php
}
?>
