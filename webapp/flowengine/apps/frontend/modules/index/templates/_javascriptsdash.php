<?php

/**
 * _javascripts.php template.
 *
 * Displays Javascripts
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>

<!-- Latest compiled and minified JavaScript -->
<script src="/assets_frontend/js/jquery.min.js" crossorigin="anonymous"></script>
<script src="/assets_frontend/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="/assets_frontend/js/docs.min.js" crossorigin="anonymous"></script>

<script src="/assets_frontend/js/docs.min.js" crossorigin="anonymous"></script>

<?php
if (sfConfig::get('app_google_analytics_id')) {
?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-5XE6F7DLZG"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());

		gtag('config', '<?php echo sfConfig::get('app_google_analytics_id'); ?>');
	</script>
<?php
}

//Only display livechat if it is enabled
if (sfConfig::get('app_enable_livechat')) {
?>
	<!-- Start of LiveChat (www.livechatinc.com) code -->
	<script type="text/javascript">
		window.__lc = window.__lc || {};
		window.__lc.license = 7766711;
		(function() {
			var lc = document.createElement('script');
			lc.type = 'text/javascript';
			lc.async = true;
			lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(lc, s);
		})();
	</script>
	<!-- End of LiveChat code -->
<?php
}
?>

<script>
	$(document).ready(function() {
		var application_next_step = document.querySelectorAll('[id^="more_action_id_"]');
		if (application_next_step.length > 1) {
			var application_next_array = Array.from(application_next_step);
			var application_id = [];
			application_next_array.map(function(item) {
				item_id = $("#".concat(item.id)).attr("data-id");
				$.get("/plan/dashboard/checknextaction?id=".concat(item_id), function(data) {
					if (data.length > 2) {
						data = JSON.parse(data);
						console.log("#more_action_id_" + data.application);
						$("#more_action_id_" + data.application).html(`<a title="More Actions" href="/plan/application/view/id/${data.application}"><span class="badge badge-danger"><i class="fa fa-plus-circle" aria-hidden="true"></i></span></a>`);
					}
				});
			});
		}
	});
</script>

<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
<script>
	$(function() {
		$('#custom_bills').DataTable({});
		$('#apps').DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": {
				url: "#",
				type: "get",
				error: function() {
					//$(".employee-grid-error").html("");
					//$("#apps_tbl").append('<tbody class="tasks_inbox-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#apps_processing").css("display", "none");
				},
				complete: function() {
					$("#apps_processing").css("display", "none");
				}
			},
			"fnDrawCallback": function(oSettings) {
				$('tbody > tr', $(this)).removeClass('hide');
			},
			"fnPreDrawCallback": function(oSettings) {
				$('tbody > tr', $(this)).addClass('hide');
				return true;
			},
			"rowCallback": function(row, data, index) {
				var btn = '<a title="<?php echo __('View Application') ?>" href="' + window.location.protocol + '//' + window.location.hostname + ':' + window.location.port + '/plan/application/view/id/' + data.id + '"><span class="badge badge-primary"><i class="fa fa-eye"></i></span></a>';
				$('td:eq(5)', row).html(btn);
				var link = '<a title="<?php echo __('View Application') ?>" href="' + window.location.protocol + '//' + window.location.hostname + ':' + window.location.port + '/plan/application/view/id/' + data.id + '">' + data.application_id + '</a>';
				$('td:eq(2)', row).html(link);
			},
			columns: [{
					data: 'id'
				},
				{
					data: 'form_name'
				},
				{
					data: 'application_id'
				},
				{
					data: 'stage'
				},
				{
					data: 'date_created'
				},
				{
					data: 'id'
				}
			]
		});
		$('#apps_lastest').DataTable();
		$('#bills').DataTable();

		$('#invoices').DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": {
				url: "#",
				type: "get",
				error: function() {
					//$(".employee-grid-error").html("");
					//$("#apps_tbl").append('<tbody class="tasks_inbox-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#invoices_processing").css("display", "none");
				},
				complete: function() {
					$("#invoices_processing").css("display", "none");
				}
			},
			"fnDrawCallback": function(oSettings) {
				$('tbody > tr', $(this)).removeClass('hide');
			},
			"fnPreDrawCallback": function(oSettings) {
				$('tbody > tr', $(this)).addClass('hide');
				return true;
			},
			"rowCallback": function(row, data, index) {
				var btn = '<a title="<?php echo __('View Invoice') ?>" href="' + window.location.protocol + '//' + window.location.hostname + ':' + window.location.port + '/plan/invoices/view/id/' + data.id + '"><span class="badge badge-primary"><i class="fa fa-eye"></i></span></a>';
				$('td:eq(8)', row).html(btn);
				var link = '<a title="<?php echo __('View Application') ?>" href="' + window.location.protocol + '//' + window.location.hostname + ':' + window.location.port + '/plan/application/view/id/' + data.app_id + '">' + data.application_id + '</a>';
				$('td:eq(5)', row).html(link);
			},
			columns: [{
					data: 'id'
				},
				{
					data: 'inv_no'
				},
				{
					data: 'date_created'
				},
				{
					data: 'due_date'
				},
				{
					data: 'amount'
				},
				{
					data: 'application_id'
				},
				{
					data: 'form_name'
				},
				{
					data: 'status'
				},
				{
					data: 'id'
				}
			]
		});

		$('#message_form').submit(function(e) {
			$.ajax({
				url: "<?php echo url_for('/plan/application/messaging') ?>",
				data: $(this).serialize(),
				type: "POST",
				dataType: "json",
			}).done(function(resp) {
				if (resp.success) {
					$('#message_block').append('<li class="clearfix"><div class="chat-avatar"><?php echo $profile_img ?><i>' + resp.message.time + '</i></div><div class="conversation-text"><div class="ctext-wrap"><i>' + resp.message.name + '</i><p>' + resp.message.content + '</p></div></div></li>');
				} else {
					alert('Message not created!');
				}
				$('#msg_wysiwyg').val('');
			}).fail(function(xhr, status, errorThrown) {
				alert('Error!');
				console.log("Error: " + errorThrown);
				console.log("Status: " + status);
				console.dir(xhr);
			});
			e.preventDefault();
		});

	});
</script>


<!--Start of Tawk.to Script-->
<script type="text/javascript">
	/* var Tawk_API = Tawk_API || {},
		Tawk_LoadStart = new Date();
	(function() {
		var s1 = document.createElement("script"),
			s0 = document.getElementsByTagName("script")[0];
		s1.async = true;
		s1.src = 'https://embed.tawk.to/64e7723d94cf5d49dc6c4c11/1h8k1ch3o';
		s1.charset = 'UTF-8';
		s1.setAttribute('crossorigin', '*');
		s0.parentNode.insertBefore(s1, s0);
	})(); */
</script>
<!--End of Tawk.to Script-->

<script async src="https://www.googletagmanager.com/gtag/js?id=G-Z4BM5P1Z0W"></script>
<script>
	window.dataLayer = window.dataLayer || [];

	function gtag() {
		dataLayer.push(arguments);
	}
	gtag('js', new Date());

	gtag('config', 'G-Z4BM5P1Z0W');
</script>