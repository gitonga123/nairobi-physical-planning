<?php
        $prefix_folder = dirname(__FILE__)."/../../../../..";
	require($prefix_folder.'/lib/vendor/cp_form/config.php');
	$prefix_folder = dirname(__FILE__)."/../../../../..";
	require($prefix_folder.'/lib/vendor/cp_form/includes/db-core.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/db-functions.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/helper-functions.php');
	require($prefix_folder.'/lib/vendor/cp_form/includes/entry-functions.php');

	connect_db();
	
	
	if(empty($form_id) || empty($entry_id)){
			die('ID required.');
	}
	
	
	//check for delete parameter
	if(!empty($_GET['delete'])){
			
		delete_entries($form_id,array($entry_id));
			
		$_SESSION['AP_SUCCESS']['title'] = 'Entry deleted';
		$_SESSION['AP_SUCCESS']['desc']  = "Entry #{$entry_id} has been deleted.";
		
		$ssl_suffix = get_ssl_suffix();	
		header("Location: /backend.php/forms/manageentries?id={$form_id}");
		exit;
	}
		
	//get form name
	$query = "select form_name from `ap_forms` where form_id='$form_id'";
	$result = do_query($query);
	$row = do_fetch_result($result);
	$form_name = $row['form_name'];
	
	$entry_details = get_entry_details($form_id,$entry_id);
	
	//get entry timestamp
	$query = "select date_created,date_updated,ip_address from `ap_form_{$form_id}` where id='$entry_id'";
	$result = do_query($query);
	$row = do_fetch_result($result);
	
	$date_created = $row['date_created'];
	if(!empty($row['date_updated'])){
		$date_updated = $row['date_updated'];
	}else{
		$date_updated = '&nbsp;';
	}
	$ip_address   = $row['ip_address'];
	
	//get ids for navigation buttons
	//older entry id
	$result = do_query("select id from ap_form_{$form_id} where id < $entry_id order by id desc limit 1");
	$row = do_fetch_result($result);
	$older_entry_id = $row['id'];
	
	//oldest entry id
	$result = do_query("select id from ap_form_{$form_id} order by id asc limit 1");
	$row = do_fetch_result($result);
	$oldest_entry_id = $row['id'];
	
	//newer entry id
	$result = do_query("select id from ap_form_{$form_id} where id > $entry_id order by id asc limit 1");
	$row = do_fetch_result($result);
	$newer_entry_id = $row['id'];
	
	//newest entry id
	$result = do_query("select id from ap_form_{$form_id} order by id desc limit 1");
	$row = do_fetch_result($result);
	$newest_entry_id = $row['id'];
	
	if(($entry_id == $newest_entry_id) && ($entry_id == $oldest_entry_id)){
		$nav_position = 'disabled';
	}elseif($entry_id == $newest_entry_id){
		$nav_position = 'newest';
	}elseif ($entry_id == $oldest_entry_id){
		$nav_position = 'oldest';
	}else{
		$nav_position = 'middle';
	}
	
	
	
?>

<div id="tri_list">
<ul>

<?php 
		$toggle = false;
		
		foreach ($entry_details as $data){ 
			if($toggle){
				$toggle = false;
				$row_style = 'class="alt"';
			}else{
				$toggle = true;
				$row_style = '';
			}	
?>  
  	<li><img src='/images/bullet.png' /> &nbsp;<b style="color:#000; font-weight: 700;"><?php echo $data['label']; ?>:</b><br> &nbsp;&nbsp;&nbsp;&nbsp;<?php if($data['value']){ echo nl2br($data['value']); }else{ echo "<i> - not defined - </i>"; } ?></li>
<?php } ?>  	
  	    <li><img src='/images/bullet.png' /> &nbsp;<b style="color:#000; font-weight: 700;">Date Created:</b><br>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $date_created; ?></li>

</ul>
</div>