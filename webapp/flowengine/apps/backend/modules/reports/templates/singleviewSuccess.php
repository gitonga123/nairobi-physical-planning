 <div class="g12" style="padding-left: 3px;">
			<form style="margin-bottom: 0px;">
			<label style='height: 30px; margin-top: 0px;'>
			<div style='float: left; font-size: 20px; font-weight: 700;'><?php echo $report->getTitle() ?></div></label>
			<fieldset style="margin: 0px;">
			<section>
				<div style="float: right; width: 98%;" align='right'>
				<button onClick="if(document.getElementById('filter').style.display == 'none'){ document.getElementById('filter').style.display = 'block'; }else{ document.getElementById('filter').style.display = 'none'; }">Filter</button>
				</div>
			</section>
			</fieldset>
			</form>
 
 <div style="float: left;">
				<font style="font-weight: 900; margin-left: 10px;">Application Status:</font> <?php
				$q = Doctrine_Query::create()
					 ->from('SubMenus a')
					 ->where('a.id = ?', $application->getApproved());
				$submenu = $q->fetchOne();
				
				echo $submenu->getTitle();
				
			?>
</div>
<div style="float: right;">
    <button onclick="window.location='/plan/reports/printsingleview/applicationid/<?php echo $application->getId(); ?>/reportid/<?php echo $report->getId(); ?>'">Print Report</button>
	<button OnClick="window.location='/plan/applications/view/id/<?php echo $application->getId(); ?>';">View Application</button>
</div>

<div class="g12">
			<br>
			<div class="tab">
					<ul>
						<li><a href="#tabs-1"><?php echo $application->getApplicationId(); ?></a></li>
					</ul>
					<div id="tabs-1" style="padding: 5px;">
						<div style="padding: 5px;">
						<?php
						$content =  $report->getContent();
						$parser = new Templateparser();
						$content = $parser->parse($application->getId(),$application->getFormId(), $application->getEntryId(), $content);
						echo html_entity_decode($content);
						?>
						</div>
					</div>
				</div>
		
</div>
</div>