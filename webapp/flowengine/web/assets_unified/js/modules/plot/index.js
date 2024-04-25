(function($, e){
    var 
        // The modal used within this panel
        ui = $('.modal', e),

        // The base url to use
        baseURL = window.location.pathname,

        // Number of columns of the table
        cols = $('thead > tr > th', e).length - 1,

        // Define the datatable item
        oTable = $('table', e).dataTable({
            "bProcessing": true, "bServerSide": true,
            "sAjaxSource": baseURL,
            "iDisplayLength": 25,
            "aaSorting": [[0, "desc"]],
            "aoColumnDefs": [
                {"bSearchable": false, "bSortable": false, "aTargets": [cols]}
            ],
            "fnDrawCallback": function (oSettings) {
                $('tbody > tr', e).removeClass('hide');
            },
            "fnPreDrawCallback": function (oSettings) {
                $('tbody > tr', e).addClass('hide');
                return true;
            },
            "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                // The utilities used for deleting and editing a given record
                var html = '<span style="cursor:pointer" class="badge badge-primary" data-index="'+aData[cols]+'"><i class="fa fa-pencil"></i></span>';
                html += ' <span style="cursor:pointer" class="badge badge-danger" data-index="'+aData[cols]+'"><i class="fa fa-trash-o"></i></span>';

                // Place the utilities
                $(nRow).children('td:eq('+cols+')').html(html);

                // Edit the plot details
                $('.badge-primary', nRow).click(function(){
                    var index = $(this).data('index');

                    jsonPost(baseURL, {index:index}, function(o){
                        if ( o.status === false ) {
                            alert('There is no matching record')
                            return false;
                        } o = o.row;

                        $('input[name="plot[id]"]', ui).val(o.id);                       
                        $('input[name="plot[owner_name]"]', ui).val(o.ownerName);
                        $('input[name="plot[owner_phone]"]', ui).val(o.ownerPhone);
                        $('input[name="plot[plot_no]"]', ui).val(o.plotNo);
                        $('input[name="plot[block_number]"]', ui).val(o.blockNumber);
                        $('input[name="plot[ward]"]', ui).val(o.ward);
                        $('input[name="plot[plot_size_ha]"]', ui).val(o.plotSizeHa);
                        $('input[name="plot[property_usage]"]', ui).val(o.propertyUsage);
                        $('input[name="plot[amount_land_rates]"]', ui).val(o.amountLandRates);

                        showUI();
                    });
                });

                // Delete a plot
                $('.badge-danger', nRow).click(function(){
                    var index = $(this).data('index');

                    if ( confirm('Are you sure you would like to delete the specified record?') === true ) {
                        jsonPost($('form', e).attr('trash'), {index:index}, function(o){
                            setTimeout(function(){alert(o.msg)}, 100);
                            oTable.fnDraw();
                        });
                    }
                });

                // show empty values for the empty table cells
                $('td', nRow).each( function () {
                    if ($(this).html() === '')
                        $(this).html('<span style="color:#c3c3c3">Not Available</span>');
                });
            }
        });

    // Reload the grid
    $('.panel-heading > .btn-success', e).click(function(){
        oTable.fnDraw();
    });

    // New form
    $('.panel-heading > .btn-primary', e).click(function(){
        $('input.form-control, select.form-control, textarea.form-control', ui).val('');
        showUI();
    });

    // Focus on the right item on modal show
    ui.on('shown.bs.modal', function (e) {
        $('input[name="plot[plot_no]"]', ui).focus();
    });

    // On form post
    $('form', e).on('submit', function(event){
        // Prevent form post
        event.preventDefault();

        // Send the form data and get the result
        jsonPost($(this).attr('action'), $(this).serializeArray(), function(o){
            var cls = (true === o.status) ? 'success': 'danger';

            // Refresh the grid
            oTable.fnDraw();

            // Set the content
            $('.alert p', ui).html(o.msg);
            $('.alert', ui).addClass('alert-'+cls);

            // Show the message
            $('.alert', ui).removeClass('hide');

            // Close the message once all is done
            setTimeout(function(){
                // Unset the content
                $('.alert p', ui).html('');
                $('.alert', ui).removeClass('alert-'+cls);

                // Hide the message panel
                $('.alert', ui).addClass('hide');
            }, 8000);
        });

        // Ensure the form is not posted
        return false;
    });

    // Called to open the modal
    function showUI() {
        ui.modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    // Called to make an ajax post
    function jsonPost(url, input, success) {
        $('#preloader').show();

        $.ajax({
            type: 'POST', dataType: "json", data: input, cache: false, url: url,
            headers: {"X-Code": $("input[name='plot[_csrf_token]']", e).val()},
            success: function (o) {
                $('#preloader').fadeOut();
                success(o);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#preloader').fadeOut();
                alert(errorThrown);
            },
            complete: function(jqXHR, textStatus) {
                var code = jqXHR.getResponseHeader('X-Code');
                if ( null !== code ) $("input[name='plot[_csrf_token]']", e).val(code);
            }
        });
    }
})(jQuery, jQuery('.plot-details-panel'));