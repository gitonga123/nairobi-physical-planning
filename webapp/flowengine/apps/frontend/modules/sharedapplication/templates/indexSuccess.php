<?php
/**
 * indexSuccess.php template.
 *
 * Displays list of all of the currently logged in client's shared applications
 *
 * @package    frontend
 * @subpackage sharedapplication
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');

function GetDays($sEndDate, $sStartDate){  
        $aDays[] = $start_date;
	$start_date  = $sStartDate;
	$end_date = $sEndDate;
	$current_date = $start_date;
	while(strtotime($current_date) <= strtotime($end_date))
	{
		    $aDays[] = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
		    $current_date = gmdate("Y-m-d", strtotime("+1 day", strtotime($current_date)));
	}
        return $aDays;  
} 
?>
<?php
	//$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
	//mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);
	
	
	
	$filter = $_GET['filter'];
	if(!empty($filter))
	{
		$filter = " AND b.approved = '".$filter."'";
	}
	
	
?>

  
    
<div class="contentpanel">

     <div class="row">
       
           <div class="col-sm-12 col-lg-12">
                  
                    
                    <?php

if($_GET['filter'])
{
	$q = Doctrine_Query::create()
	   ->from("FormEntryShares a")
       //    ->where("a.status = ? AND (a.receiverid = ? OR a.senderid = ? )",array("active",$sf_user->getGuardUser()->getId(),$sf_user->getGuardUser()->getId())) //OTB patch - Show where sharing status is active
	   ->where("a.receiverid = ? OR a.senderid = ?", array($sf_user->getGuardUser()->getId(),$sf_user->getGuardUser()->getId()))        
	   ->orderBy("a.id DESC");
	 //$pager = new sfDoctrinePager('FormEntryShares', 10);
	 //$pager->setQuery($q);
	 //$pager->setPage($page);
	 //$pager->init();
	
	 $counter = 1;
	 $form_shares=$q->execute();
	 //error_log('--------');
	 //error_log(print_r($q->fetchArray(),true));
	 include_partial('sharedapplication/list', array('sharedapplications' => $form_shares,'filter' => $_GET['filter']));
	 
}
else
{
	$q = Doctrine_Query::create()
	   ->from("FormEntryShares a")
       //    ->where("a.status = ? AND (a.receiverid = ? OR a.senderid = ? )",array("active",$sf_user->getGuardUser()->getId(),$sf_user->getGuardUser()->getId())) //OTB patch - Show where sharing status is active
	   ->where("a.receiverid = ? OR a.senderid = ?", array($sf_user->getGuardUser()->getId(),$sf_user->getGuardUser()->getId()))        
	   ->orderBy("a.id DESC");
	 //$pager = new sfDoctrinePager('FormEntryShares', 10);
	 //$pager->setQuery($q);
	 //$pager->setPage($page);
	 //$pager->init();
	
	 $counter = 1;
	 $form_shares=$q->execute();
	 error_log('--------');
	 error_log(print_r($q->fetchArray(),true));
	 include_partial('sharedapplication/list', array('sharedapplications' => $form_shares));
	 

}
?>
              
              
              <?php /*?><ul class="pagination">
<?php if ($pager->haveToPaginate()): ?>
	<?php
    $filter = "";
	if($_GET['filter'])
	{
		$filter = "&filter=".$_GET['filter'];
	}
  ?>
  <li><?php echo "<a title='First' href='".public_path()."plan/sharedapplication/index?page=1".$filter."'><i class=\"fa fa-angle-left\"></i></a>"; ?></li>
  <li><?php echo "<a title='Previous' href='".public_path()."plan/sharedapplication/index?page=".$pager->getPreviousPage()."".$filter."'><i class=\"fa fa-angle-left\"></i></a>"; ?></li>
  
  <?php foreach ($pager->getLinks() as $page): ?>
    <?php
	if($pager->getPage() == $page)
	{
		?>
		<li class="active"><a><?php echo $page; ?></a></li>
		<?php
	}
	else
	{
	?>
		<li><?php echo "<a title='Page ".$page."' href='".public_path()."plan/sharedapplication/index?page=".$page."".$filter."'>".$page."</a>"; ?></li>
	<?php
	}
	?>
  <?php endforeach; ?>

 <li> <?php echo "<a title='Next' href='".public_path()."plan/sharedapplication/index?page=".$pager->getNextPage()."".$filter."'><i class=\"fa fa-angle-right\"></i></a>"; ?></li>
 <li> <?php echo "<a title='Last' href='".public_path()."plan/sharedapplication/index?page=".$pager->getLastPage()."".$filter."'><i class=\"fa fa-angle-right\"></i></a>"; ?></li>
<?php endif; ?>
</ul><!-- /.pagination --><?php */?>

    
       
            </div><!-- col-sm-9 -->
        </div><!-- row -->
  
  
                

</div><!-- row -->
         
 <div><!-- content panel -->       
                             



