<table class="table table-striped table-hover table-special" id="apps_tbl">
    <thead>
    <tr>
        <th >#</th>
        <th ><?php echo __("Service"); ?></th>
        <th ><?php echo __("Status"); ?></th>
        <th ><?php echo __("Submitted On"); ?></th>
        <th ><?php echo __("Submitted By"); ?></th>
        <th ><?php echo __("Actions") ?></th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script>
	$(function(){
		var table=$('#apps_tbl').DataTable({
			"processing": true,
			"serverSide" : true,
			"ajax": {
				url:"#",
				type: "get",
				error:function(){
                    //$(".employee-grid-error").html("");
                    //$("#apps_tbl").append('<tbody class="tasks_inbox-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#apps_tbl_processing").css("display","none");
 				},				
				complete:function(){
                    $("#apps_tbl_processing").css("display","none");
				}
			},
            "fnDrawCallback": function (oSettings) {
                $('tbody > tr', $(this)).removeClass('hide');
            },
            "fnPreDrawCallback": function (oSettings) {
                $('tbody > tr', $(this)).addClass('hide');
                return true;
            },
			"rowCallback":function(row,data,index){
                var html='<a class="btn btn-default btn-xs" title="<?php echo __('View'); ?>" href="'+window.location.protocol+'//'+window.location.hostname+':'+window.location.port+'/plan/applications/view/id/'+data.id+'"><?php echo __('View'); ?> <span class="fa fa-eye"></span></a>';
                $('td:eq(5)',row).html(html);
                let link_rw_1='<a title="'+data.application_id+'" href="'+window.location.protocol+'//'+window.location.hostname+':'+window.location.port+'/plan/applications/view/id/'+data.id+'" >'+data.id+'</a>';
                $('td:eq(0)',row).html(link_rw_1);
                $(row).addClass(data.date_highlight);
                let info_app='<a title="'+data.id+'" href="'+window.location.protocol+'//'+window.location.hostname+':'+window.location.port+'/plan/applications/view/id/'+data.id+'">'+data.application_id+'</a><h1>'+data.service+'</h1>'+'<p>'+data.date_submitted+'<span class="badge pull-right">'+data.days_in_stage+' <?php echo __('Day(s)') ?></span></p>';
                $('td:eq(1)',row).html(info_app);
                $('td:eq(3)',row).attr('data-search',data.date_submitted);
                

            },
            columns: [
                { data: 'id'},
                { data: 'application_id'},
                { data: 'stage'},
                { data: 'date_submitted'},
                { data: 'applicant'},
                { data: 'id'}
            ]
		});
	});
	</script>