<?php
  $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
	require($prefix_folder.'includes/init.php');
	
	require($prefix_folder.'../../../config/form_builder_config.php');
	require($prefix_folder.'includes/db-core.php');
	require($prefix_folder.'includes/helper-functions.php');
	require($prefix_folder.'includes/check-session.php');

	require($prefix_folder.'includes/entry-functions.php');
	require($prefix_folder.'includes/users-functions.php');

	$sort_by = trim($_GET['sortby']);

	//get page number for pagination
	if (isset($_REQUEST['pageno'])) {
	   $pageno = $_REQUEST['pageno'];
	}else{
	   $pageno = 1;
	}

	
	$dbh = mf_connect_db();
	$mf_settings = mf_get_settings($dbh);
	
	//check permission, is the user allowed to access this page?
	if(empty($_SESSION['mf_user_privileges']['priv_administer'])){
		$user_perms = mf_get_user_permissions($dbh,$form_id,$_SESSION['mf_user_id']);

		//this page need edit_entries or view_entries permission
		if(empty($user_perms['edit_entries']) && empty($user_perms['view_entries'])){
			$_SESSION['MF_DENIED'] = "You don't have permission to access this page.";

			$ssl_suffix = mf_get_ssl_suffix();						
			header("Location: http{$ssl_suffix}://".$_SERVER['HTTP_HOST'].mf_get_dirname($_SERVER['PHP_SELF'])."/restricted.php");
			exit;
		}
	}
	
	$query = "select 
					A.form_name,
					ifnull(B.entries_sort_by,'id-desc') entries_sort_by,
					ifnull(B.entries_filter_type,'all') entries_filter_type,
					ifnull(B.entries_enable_filter,0) entries_enable_filter			  
				from 
					".MF_TABLE_PREFIX."forms A left join ".MF_TABLE_PREFIX."entries_preferences B 
				  on 
				  	A.form_id=B.form_id and B.user_id=? 
			   where 
			   		A.form_id = ?";
	$params = array($_SESSION['mf_user_id'],$form_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	
	if(!empty($row)){
		
		$row['form_name'] = mf_trim_max_length($row['form_name'],65);

		if(!empty($row['form_name'])){		
			$form_name = htmlspecialchars($row['form_name']);
		}else{
			$form_name = 'Untitled Form (#'.$form_id.')';
		}	

		$entries_filter_type   = $row['entries_filter_type'];
		$entries_enable_filter = $row['entries_enable_filter'];
	}else{
		die("Error. Unknown form ID.");
	}

  $header_data =<<<EOT
<link type="text/css" href="/form_builder/js/jquery-ui/themes/base/jquery.ui.all.css" rel="stylesheet" />
<link type="text/css" href="/form_builder/css/pagination_classic.css" rel="stylesheet" />
<link type="text/css" href="/form_builder/css/dropui.css" rel="stylesheet" />
<link type="text/css" href="/form_builder/js/datepick/smoothness.datepick.css" rel="stylesheet" />
EOT;
	
	$current_nav_tab = 'manage_forms';
	require($prefix_folder.'includes/header.php'); 
?>
<div id="content" class="full">
			<div class="panel panel-default">
				<div class="content_header">
					<div class="content_header_title">
						<div id="me_form_title" <?php if(!empty($total_incomplete_entries)){ echo 'style="max-width: 80%"'; } ?>>
							<h2><?php echo "<a class=\"breadcrumb\" href='/backend.php/forms/index'>".$form_name.'</a>'; ?> <span class="icon-arrow-right2 breadcrumb_arrow"></span> Entries</h2>
							<br>
							<p>Edit and manage your form entries</p>
						</div>
						
						<div style="clear: both; height: 1px"></div>
					</div>
					
				</div>

				<?php mf_show_message(); ?>

				<div class="content_body">

          <form name="service_form" id="service_form" method="post" action="/backend.php/forms/update/id/<?php echo $ap_form->getFormId(); ?>">
            <table id="service_form" class="form_table">
              <tfoot>
                  <tr>
                      <td colspan="2">
                          <button type="submit" class="btn btn-primary"><?php echo __('Save Settings');?></button>
                      </td>
                  </tr>
              </tfoot>
              <tbody>
                <?php echo $form ?>
              </tbody>
            </table>
          </form>
          
        </div>
      </div>
    </div>
  </div>