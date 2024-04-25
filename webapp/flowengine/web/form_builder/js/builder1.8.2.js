var active_element = null;
var sidebar_scroll_pos = null;

function isScrolledIntoView(c) {
    var e = $(window).scrollTop();
    var d = e + $(window).height();
    var a = $(c).offset().top;
    var b = a + $(c).height();
    return ((b >= e) && (a <= d))
}

function save_form() {
    $("#bottom_bar_msg").text("Please wait... Saving...");
    var b = $("#form_header").data("form_properties").id;
    var f = $("#form_header").data("form_properties");
    f.frame_height = $("#form_container").outerHeight();
    var c = $("#li_lastpage").data("field_properties");
    var e = $("#form_builder_sortable > li.dblive").not(".synched").not(".li_pagination");
    if (e.length >= 1) {
        var d = new Array();
        e.each(function(g) {
            d[g] = $(this).data("field_properties")
        })
    }
    var a = $("#form_builder_sortable").sortable("serialize", {
        key: "el_pos[]"
    });
    $.ajax({
        type: "POST",
        async: true,
        url: "/backend.php/forms/saveform",
        data: {
            form_id: b,
            fp: f,
            ep: d,
            el_pos: a,
            lp: c
        },
        cache: false,
        global: false,
        dataType: "json",
        error: function(i, g, h) {
            alert("Error while saving. Error Message:\n" + i.responseText)
        },
        success: function(g) {
            if (g.status == "ok") {
                $("#bottom_bar_loader").remove();
                $("#bottom_bar_msg").hide();
                $(".form_editor").data("form_saved", true);
                $("#dialog-form-saved").dialog("open")
            } else {
                alert("Error while saving. Error Message:\n" + g)
            }
        }
    })
}

function select_form_header(b) {
    var d = $("#form_header").data("form_properties");
    $("#" + active_element).removeClass("highlighted");
    active_element = null;
    $("#form_header .highlighted").removeClass("highlighted");
    $("#pagination_header").removeClass("highlighted");
    $("#" + b).addClass("highlighted");
    $("#selected_field_image").css("visibility", "hidden");
    $("#bottom_bar_field_action").fadeOut("fast", function() {
        $("#bottom_bar_content").animate({
            "margin-left": "225px"
        }, {
            duration: 200,
            queue: false
        })
    });
    if ($("#builder_tabs").tabs("option", "selected") != 2) {
        $("#builder_tabs").tabs("select", 2)
    }
    if ($("#form_header").data("coming_from_click") == 1) {
        var f = $("#content").position().top - 2;
        if ($("#sidebar").position().top > f) {
            $("#sidebar").css("top", f)
        }
    }
    $("#form_header").data("coming_from_click", 0);
    $("#all_form_properties > li").hide();
    if (b == "pagination_header") {
        if (d.pagination_type == "steps") {
            $("#pagination_style_steps").prop("checked", true)
        } else {
            if (d.pagination_type == "percentage") {
                $("#pagination_style_percentage").prop("checked", true)
            } else {
                $("#pagination_style_disabled").prop("checked", true)
            }
        }
        if (d.pagination_type == "disabled") {
            $("#prop_pagination_style legend").css({
                "background-color": "#DC6666",
                "border-color": "#DC6666"
            });
            $("#prop_pagination_style fieldset").css("border-color", "#DC6666")
        } else {
            $("#prop_pagination_style legend").css({
                "background-color": "#3D6C10",
                "border-color": "#3D6C10"
            });
            $("#prop_pagination_style fieldset").css("border-color", "#3D6C10")
        }
        $("#prop_pagination_style").show();
        $("#pagination_title_list > li").remove();
        var i = $("#form_builder_sortable > li.page_break");
        var g = "";
        var h = 1;
        i.each(function(k) {
            var l = $(this);
            g += '<li><label for="pagetitleinput_' + h + '">' + h + '.</label><input type="text" value="' + l.data("field_properties").page_title + '" autocomplete="off" class="text" id="pagetitleinput_' + h + '" /></li>';
            h++
        });
        $("#pagination_title_list").append(g);
        if (d.pagination_type == "disabled") {
            $("#prop_pagination_titles").hide()
        } else {
            $("#prop_pagination_titles").show()
        }
    } else {
        $("#all_form_properties .form_prop").show();
        if ($("#form_prop_toggle_a").data("expand") == 1) {
            $("#form_prop_toggle_a").text("show less options");
            $("#form_prop_toggle_img").attr("src", "/form_builder/images/icons/resultset_up.gif");
            $("#all_form_properties .advanced_prop").show()
        } else {
            $("#form_prop_toggle_a").text("show more options");
            $("#form_prop_toggle_img").attr("src", "/form_builder/images/icons/resultset_next.gif");
            $("#all_form_properties .advanced_prop").hide()
        }
        if ($("#form_review").prop("checked") == true && $("#form_prop_toggle_a").data("expand") == 1) {
            $("#form_prop_review").show()
        } else {
            $("#form_prop_review").hide()
        }
        if ($("#form_resume").prop("checked") == true && $("#form_prop_toggle_a").data("expand") == 1) {
            $("#form_prop_resume").show()
        } else {
            $("#form_prop_resume").hide()
        }
        $("#form_title").val(d.name);
        $("#form_type").val(d.type);
        $("#form_idn").val(d.idn);
        $("#form_code").val(d.code);
        $("#form_stage").val(d.stage);
        $("#form_group").val(d.group);
        $("#form_department").val(d.department);
        $("#form_department_stage").val(d.department_stage);
        $("#form_description").val(d.description);
        $("#form_label_alignment").val(d.label_alignment);
        $("#form_language").val(d.language);
        if(d.type == 1)
        {
            $("#form_application_properties").show();
            $("#form_commentsheet_properties").hide();
        }
        else if(d.type == 2)
        {
            $("#form_application_properties").hide();
            $("#form_commentsheet_properties").show();
        }
        else
        {
           $("#form_application_properties").hide();
           $("#form_commentsheet_properties").hide();
        }
        if (b == "form_header_title") {
            $("#form_title").focus().select()
        } else {
            $("#form_description").focus().select()
        }
        $("#form_success_message").val(d.success_message);
        $("#form_redirect_url").val(d.redirect);
        if (d.redirect_enable == 1) {
            $("#form_redirect_option").prop("checked", true);
            $("#form_success_message_option").prop("checked", false);
            $("#form_success_message").hide();
            $("#form_redirect_url").show()
        } else {
            $("#form_redirect_option").prop("checked", false);
            $("#form_success_message_option").prop("checked", true);
            $("#form_success_message").show();
            $("#form_redirect_url").hide()
        }
        if (d.review == 1) {
            $("#form_review").prop("checked", true)
        } else {
            $("#form_review").prop("checked", false)
        }
        if ($("#pagination_header").length > 0) {
            if (d.resume_enable == 1) {
                $("#form_resume").prop("checked", true)
            } else {
                $("#form_resume").prop("checked", false)
            }
        } else {
            d.resume_enable = 0;
            $("#form_resume").prop("checked", false);
            $("#form_resume").prop("disabled", true)
        }
        $("#form_review_title").val(d.review_title);
        $("#form_review_description").val(d.review_description);
        $("#review_primary_text").val(d.review_primary_text);
        $("#review_secondary_text").val(d.review_secondary_text);
        $("#review_primary_img").val(d.review_primary_img);
        $("#review_secondary_img").val(d.review_secondary_img);
        if (d.review_use_image == 1) {
            $("#form_review_use_text").prop("checked", false);
            $("#form_review_use_image").prop("checked", true);
            $("#div_review_use_image").show();
            $("#div_review_use_text").hide()
        } else {
            $("#form_review_use_text").prop("checked", true);
            $("#form_review_use_image").prop("checked", false);
            $("#div_review_use_image").hide();
            $("#div_review_use_text").show()
        }
        $("#form_resume_subject").val(d.resume_subject);
        $("#form_resume_content").val(d.resume_content);
        $("#form_resume_from_name").val(d.resume_from_name);
        $("#form_resume_from_email_address").val(d.resume_from_email_address);
        if (d.password.length > 0) {
            $("#form_password_option").prop("checked", true);
            $("#form_password").show();
            $("#form_password_data").val(d.password)
        } else {
            $("#form_password_option").prop("checked", false);
            $("#form_password").hide();
            $("#form_password_data").val("")
        }
        $("#form_limit").val(d.limit);
        if (d.limit_enable == 1) {
            $("#form_limit_option").prop("checked", true);
            $("#form_limit_div").show()
        } else {
            $("#form_limit_option").prop("checked", false);
            $("#form_limit_div").hide()
        }
        $("#form_captcha_type").val(d.captcha_type);
        if (d.captcha == 1) {
            $("#form_captcha").prop("checked", true);
            $("#form_captcha_type_option").show()
        } else {
            $("#form_captcha").prop("checked", false);
            $("#form_captcha_type_option").hide()
        }
        $("#form_unique_ip_maxcount").val(d.unique_ip_maxcount);
        $("#form_unique_ip_period").val(d.unique_ip_period);
        if (d.unique_ip == 1) {
            $("#form_unique_ip").prop("checked", true);
            $("#form_unique_ip_div").show()
        } else {
            $("#form_unique_ip").prop("checked", false);
            $("#form_unique_ip_div").hide()
        }
        if (d.name_hide == 1) {
            $("#form_name_hide").prop("checked", true)
        } else {
            $("#form_name_hide").prop("checked", false)
        }
        if (d.schedule_enable == 1) {
            $("#form_schedule_enable").prop("checked", true);
            $("#form_prop_scheduling_start").show();
            $("#form_prop_scheduling_end").show()
        } else {
            $("#form_schedule_enable").prop("checked", false);
            $("#form_prop_scheduling_start").hide();
            $("#form_prop_scheduling_end").hide()
        }
        if (d.schedule_start_date != null && d.schedule_start_date != "0000-00-00") {
            var j = d.schedule_start_date.split("-");
            $("#scheduling_start_yyyy").val(j[0]);
            $("#scheduling_start_mm").val(parseInt(j[1], 10));
            $("#scheduling_start_dd").val(parseInt(j[2], 10));
            $("#linked_picker_scheduling_start").datepick("setDate", new Date(parseInt(j[0], 10), parseInt(j[1], 10) - 1, parseInt(j[2], 10)))
        } else {
            $("#scheduling_start_yyyy").val("");
            $("#scheduling_start_mm").val("");
            $("#scheduling_start_dd").val("")
        }
        if (d.schedule_start_hour != "" && d.schedule_start_hour != "0000-00-00") {
            var e = d.schedule_start_hour.split(":");
            $("#scheduling_start_hour").val(e[0]);
            $("#scheduling_start_minute").val(e[1]);
            $("#scheduling_start_ampm").val(e[2])
        }
        if (d.schedule_end_date != null && d.schedule_end_date != "0000-00-00") {
            var a = d.schedule_end_date.split("-");
            $("#scheduling_end_yyyy").val(a[0]);
            $("#scheduling_end_mm").val(parseInt(a[1], 10));
            $("#scheduling_end_dd").val(parseInt(a[2], 10));
            $("#linked_picker_scheduling_end").datepick("setDate", new Date(parseInt(a[0], 10), parseInt(a[1], 10) - 1, parseInt(a[2], 10)))
        } else {
            $("#scheduling_end_yyyy").val("");
            $("#scheduling_end_mm").val("");
            $("#scheduling_end_dd").val("")
        }
        if (d.schedule_end_hour != "" && d.schedule_end_hour != "0000-00-00") {
            var c = d.schedule_end_hour.split(":");
            $("#scheduling_end_hour").val(c[0]);
            $("#scheduling_end_minute").val(c[1]);
            $("#scheduling_end_ampm").val(c[2])
        }
        $("#form_custom_script_url").val(d.custom_script_url);
        if (d.custom_script_enable == 1) {
            $("#form_custom_script_enable").prop("checked", true);
            $("#form_custom_script_div").show()
        } else {
            $("#form_custom_script_enable").prop("checked", false);
            $("#form_custom_script_div").hide()
        }
    }
    adjust_main_height();
    check_synch_fields()
}

function check_synch_fields() {
    var a = $("#form_header").data("form_properties").id;
    var d = 10;
    var c = $("#form_builder_sortable > li").not(".dblive").not(".synched").not(".highlighted").not(".li_pagination").not("#li_lastpage");
    if (c.length >= d) {
        var b = new Array();
        c.each(function(e) {
            b[e] = $(this).data("field_properties")
        });
        $.ajax({
            type: "POST",
            async: true,
            url: ";/backend.php/forms/synchfields",
            data: {
                form_id: a,
                fp: b
            },
            cache: false,
            global: false,
            dataType: "json",
            error: function(h, f, g) {},
            success: function(e) {
                if (e.status == "ok") {
                    $(e.updated_element_id).addClass("synched")
                }
            }
        })
    }
}

function select_element(c) {
    var g = $(c);
    if ((active_element != null) && (active_element != g.attr("id"))) {
        $("#" + active_element).removeClass("highlighted")
    }
    $("#form_header .highlighted").removeClass("highlighted");
    $("#pagination_header").removeClass("highlighted");
    active_element = g.attr("id");
    g.addClass("highlighted");
    g.removeClass("synched");
    g.data("field_properties").position = g.index();
    check_synch_fields();
    $("#element_inactive_msg").hide();
    $("#element_properties_form").show();
    var f = 40;
    var e = 110;
    var a = 75;
    if ($.browser.msie) {
        f = 80;
        e = 65;
        a = -10
    }
    var b = g.offset().top - f;
    if (Math.abs(b - $("#sidebar").offset().top) < $(window).height()) {
        $("#sidebar").animate({
            top: b
        }, {
            duration: 200,
            queue: false
        })
    } else {
        $("#sidebar").css("top", b)
    }
    $("#selected_field_image").css("visibility", "visible").animate({
        top: b - e
    }, {
        duration: 200,
        queue: false
    });
    if (g.data("field_properties").id != "lastpage") {
        $("#bottom_bar_content").animate({
            "margin-left": "90px"
        }, {
            duration: 200,
            queue: false,
            complete: function() {
                $("#bottom_bar_field_action").fadeIn()
            }
        })
    } else {
        $("#bottom_bar_field_action").fadeOut("fast", function() {
            $("#bottom_bar_content").animate({
                "margin-left": "225px"
            }, {
                duration: 200,
                queue: false
            })
        })
    }
    load_properties("#" + active_element);
    var i = (b + $("#sidebar").outerHeight()) - a;
    var d = $("#header").outerHeight() + $("#navigation").outerHeight() + $("#content").outerHeight();
    if (i > d) {
        var h = (i - d);
        $("#main").animate({
            "padding-bottom": h + "px"
        }, {
            duration: 200,
            queue: false
        })
    }
}

function load_properties(p) {
    var c = $(p);
    var v = c.data("field_properties");
    $("#all_properties li").hide();
    $("#element_label").val(v.title);
    $("#prop_element_label").show();
    var a = "";
    if (v.type == "matrix") {
        a = {
            matrix: "Matrix Choice"
        }
    } else {
        if (v.type == "radio" || v.type == "checkbox" || v.type == "select") {
            a = {
                radio: "Multiple Choice",
                checkbox: "Checkboxes",
                select: "Drop Down"
            }
        } else {
            a = {
                text: "Single Line Text",
                textarea: "Paragraph Text",
                number: "Number",
                simple_name: "Name",
                date: "Date",
                time: "Time",
                phone: "Phone",
                money: "Price",
                url: "Web Site",
                email: "Email",
                address: "Address",
                file: "File Upload",
                section: "Section Break",
                signature: "Signature",
                plinth_area: "Plinth Area", //OTB patch
                total_plinth_area: "Total Plinth Area", //OTB patch
            }
        }
    }
    $("#element_type > option").remove();
    $.each(a, function(x, w) {
        $("#element_type").append($("<option></option>").val(x).html(w))
    });
    if (v.type == "name" || v.type == "simple_name" || v.type == "name_wmiddle" || v.type == "simple_name_wmiddle") {
        $("#element_type").val("simple_name");
         $("#element_footprint_span").hide(); //OTB Patch
         $("#element_plotsize_span").hide();
        $("#element_actualplotration_span").hide();
        $("#element_permittedgroundcoverage_span").hide();
        $("#element_file_ifc_span").hide();
        $("#element_plot_no_span").hide();
        $("#element_ownertype_span").hide();
        $("#prop_element_select_options").hide();
        $("#prop_table_name_conf").hide();
        $("#prop_option_query_conf").hide();
        $("#prop_application_conf").hide();
    }
    //OTB patch - Total Plinth Area
    else if(v.type == "total_plinth_area"){
         $("#element_footprint_span").hide(); //OTB Patch
         $("#element_file_ifc_span").hide(); //OTB Patch
         $("#element_plot_no_span").hide(); //OTB Patch
         $("#element_ownertype_span").hide(); //OTB Patch
         $("#prop_element_select_options").hide();
         $("#prop_table_name_conf").hide();
         $("#prop_option_query_conf").hide();
         $("#prop_application_conf").hide();
          $("#element_type").val("total_plinth_area");
    } //OTB patch - Plinth Area
    else if(v.type == "plinth_area"){
         $("#element_footprint_span").show(); //OTB patch
        $("#element_type").val("plinth_area");
        $("#element_plotsize_span").hide();
        $("#element_actualplotration_span").hide();
        $("#element_permittedgroundcoverage_span").hide();
        $("#element_file_ifc_span").hide();
        $("#element_plot_no_span").hide();
        $("#element_ownertype_span").hide();
        $("#prop_element_select_options").hide();
        $("#prop_table_name_conf").hide();
        $("#prop_option_query_conf").hide();
        $("#prop_application_conf").hide();
    }
	else {
        if (v.type == "date" || v.type == "europe_date") {
            $("#element_type").val("date");
             $("#element_footprint_span").hide(); //OTB Patch
             $("#element_plotsize_span").hide();
            $("#element_actualplotration_span").hide();
             $("#element_permittedgroundcoverage_span").hide();
             $("#element_file_ifc_span").hide();
             $("#element_plot_no_span").hide();
             $("#element_ownertype_span").hide();
             $("#prop_element_select_options").hide();
             $("#prop_table_name_conf").hide();
             $("#prop_option_query_conf").hide();
             $("#prop_application_conf").hide();
        } else {
            if (v.type == "phone" || v.type == "simple_phone") {
                $("#element_type").val("phone");
				$("#element_footprint_span").hide(); //OTB Patch
				$("#element_plotsize_span").hide();
				$("#element_actualplotration_span").hide();
				$("#element_permittedgroundcoverage_span").hide();
				$("#element_file_ifc_span").hide();
				$("#element_plot_no_span").hide();
                $("#element_ownertype_span").hide();
                $("#prop_element_select_options").hide();
                $("#prop_table_name_conf").hide();
                $("#prop_option_query_conf").hide();
                $("#prop_application_conf").hide();
            } else {
                $("#element_type").val(v.type)
            }
        }
    }
    if (v.is_db_live == 1) {
        $("#element_type").prop("disabled", true)
    } else {
        $("#element_type").prop("disabled", false)
    }
    $("#prop_element_type").show();
    if ($("#pagination_header").length > 0) {
        $("#element_position").text(v.position)
    } else {
        $("#element_position").text(v.position + 1)
    }
    if (v.is_required == "1") {
        $("#element_required").prop("checked", true)
    } else {
        $("#element_required").prop("checked", false)
    }
    if (v.is_unique == "1") {
        $("#element_unique").prop("checked", true)
    } else {
        $("#element_unique").prop("checked", false)
    }
    if (v.is_readonly == "1") {
        $("#element_readonly").prop("checked", true)
    } else {
        $("#element_readonly").prop("checked", false)
    }
    if ((v.is_required == "1") || (v.is_unique == "1") || (v.is_readonly == "1")) {
        $("#prop_options legend").css({
            "background-color": "#DC6666",
            "border-color": "#DC6666"
        });
        $("#prop_options fieldset").css("border-color", "#DC6666")
    } else {
        $("#prop_options legend").css({
            "background-color": "#3D6C10",
            "border-color": "#3D6C10"
        });
        $("#prop_options fieldset").css("border-color", "#3D6C10")
    }
    //OTB patch For checked footprint
     if (v.footprint == "1") {
        $("#element_footprint").prop("checked", true)
    } else {
        $("#element_footprint").prop("checked", false)
    }
    /** OTB patch */
     if (v.permittedgroundcoverage == "1") {
        $("#element_permittedgroundcoverage").prop("checked", true)
    } else {
        $("#element_permittedgroundcoverage").prop("checked", false)
    }
     if (v.plotsize == "1") {
        $("#element_plotsize").prop("checked", true)
    } else {
        $("#element_plotsize").prop("checked", false)
    }
     if (v.actualplotratio == "1") {
        $("#element_actualplotratio").prop("checked", true)
    } else {
        $("#element_actualplotratio").prop("checked", false)
    }
     //zone and permitted user
     if (v.zone == "1") {
        $("#element_zone").prop("checked", true)
    } else {
        $("#element_zone").prop("checked", false)
    }
    if (v.permitteduser == "1") {
        $("#element_permitteduser").prop("checked", true)
    } else {
        $("#element_permitteduser").prop("checked", false)
    }
    //is project cost
     if (v.projectcost == "1") {
        $("#element_projectcost").prop("checked", true)
    }
    else {
        $("#element_projectcost").prop("checked", false)
    }
    //for user to type in reasons for exceeding plot ratio and ground coverage
    if (v.groundcoveragereason == "1") {
        $("#element_groundcoveragereason").prop("checked", true)
    }
    else {
        $("#element_groundcoveragereason").prop("checked", false)
    }
    //OTB patch - for identify field to be used for calculating grand total of plinth area > New + Existing = Grand Total
    if (v.grandtotalplintharea == "1") {
        $("#element_grandtotalplintharea").prop("checked", true)
    }
    else {
        $("#element_grandtotalplintharea").prop("checked", false)
    }
    //OTB patch - for identify field to be used for calculating grand total of footprint area > New + Existing = Grand Total
    if (v.grandtotalfootprintarea == "1") {
        $("#element_grandtotalfootprintarea").prop("checked", true)
    }
    else {
        $("#element_grandtotalfootprintarea").prop("checked", false)
    }
    if (v.plotratioreason == "1") {
        $("#element_plotratioreason").prop("checked", true)
    }
    else {
        $("#element_plotratioreason").prop("checked", false)
    }
    if (v.file_ifc == "1") {
        $("#element_file_ifc").prop("checked", true)
    }
    else {
        $("#element_file_ifc").prop("checked", false)
    }
    if (v.plot_no == "1") {
        $("#element_plot_no").prop("checked", true)
    }
    else {
        $("#element_plot_no").prop("checked", false)
    }
    if (v.ownertype == "1") {
        $("#element_ownertype").prop("checked", true)
    }
    else {
        $("#element_ownertype").prop("checked", false)
    }
    ////
    //end otb
    $("#element_unique_span,#element_readonly_span").show();
    $("#prop_options").show();
    $("#prop_breaker").show();
    $("#element_public,#element_private,#elemet_hidden").prop("checked", false);
    if (v.is_private == "1") {
        $("#element_private").prop("checked", true)
    } else {
        if (v.is_private == "2") {
            $("#element_hidden").prop("checked", true)
        } else {
            $("#element_public").prop("checked", true)
        }
    }
    if (v.is_private == "1") {
        $("#prop_access_control legend").css({
            "background-color": "#DC6666",
            "border-color": "#DC6666"
        });
        $("#prop_access_control fieldset").css("border-color", "#DC6666")
    } else {
        $("#prop_access_control legend").css({
            "background-color": "#3D6C10",
            "border-color": "#3D6C10"
        });
        $("#prop_access_control fieldset").css("border-color", "#3D6C10")
    }
    $("#prop_access_control").show();
    $("#element_instructions").val(v.guidelines);
    $("#prop_instructions").show();
    $("#element_custom_css").val(v.css_class);
    $("#prop_custom_css").show();
    if (v.enable_placeholder == 1) {
        $("#element_enable_placeholder,#element_enable_placeholder_text").prop("checked", true)
    } else {
        $("#element_enable_placeholder,#element_enable_placeholder_text").prop("checked", false)
    }
    if (v.type == "text") {
        $("#element_size").val(v.size);
        $("#prop_element_size").show();
        $("#element_default_value_text").val(v.default_value);
        $("#prop_default_value_text").show();
        $("#element_text_default_length").val(v.text_default_length);
        $("#element_text_default_prefix").val(v.text_default_prefix);
        $("#element_text_default_random_type").val(v.text_default_random_type);
        $("#element_text_default_case").val(v.text_default_case);
        $("#default_value_text_random_div,#default_value_text_static_div").hide();
        $("#element_footprint_span").hide(); //OTB Patch
        $("#element_file_ifc_span").hide(); //OTB Patch
        $("#element_plotsize_span").show();//OTB Patch
        $("#element_actualplotration_span").show();//OTB Patch
        $("#element_permittedgroundcoverage_span").show(); //OTB Patch
        $("#element_plot_no_span").show(); //OTB Patch
        $("#element_ownertype_span").show(); //OTB Patch
        $("#prop_element_select_options").hide();
        $("#prop_table_name_conf").hide();
        $("#prop_option_query_conf").hide();
        $("#prop_application_conf").hide();
        if (v.text_default_type == "random") {
            $("#element_default_value_text_random").prop("checked", true);
            $("#default_value_text_random_div").show()
        } else {
            $("#element_default_value_text_static").prop("checked", true);
            $("#default_value_text_static_div").show()
        }
        $("#element_range_min").val(v.range_min);
        $("#element_range_max").val(v.range_max);
        $("#element_range_limit_by").val(v.range_limit_by);
        $("#prop_remote_url").val(v.option_query);
        $("#prop_remote_value").val(v.remote_value);
        $("#prop_remote_criteria").val(v.field_name);
        $("#prop_remote_username").val(v.remote_username);
        $("#prop_remote_password").val(v.remote_password);
        $("#prop_field_value").val(v.field_value);
        $("#prop_field_error_message").val(v.field_error_message);
        if ((parseInt(v.range_min) + parseInt(v.range_max)) >= 1) {
            $("#prop_range legend").css({
                "background-color": "#DC6666",
                "border-color": "#DC6666"
            });
            $("#prop_range fieldset").css("border-color", "#DC6666")
        } else {
            $("#prop_range legend").css({
                "background-color": "#3D6C10",
                "border-color": "#3D6C10"
            });
            $("#prop_range fieldset").css("border-color", "#3D6C10")
        }
        if (v.constraint == "password") {
            $("#prop_text_as_password").prop("checked", true)
        } else {
            $("#prop_text_as_password").prop("checked", false)
        }
        $("#prop_text_options").show();
        $("#prop_dataflow_options").show();
        $("#element_range_number_limit_by").hide();
        $("#element_range_limit_by").show();
        $("#prop_range").show()
    } else {
        if (v.type == "number") {
            $("#element_size").val(v.size);
            $("#prop_element_size").show();
            $("#element_default_value").val(v.default_value);
            $("#prop_default_value").show();
            $("#prop_placeholder").show();
            $("#element_range_min").val(v.range_min);
            $("#element_range_max").val(v.range_max);
            $("#element_range_number_limit_by").val(v.range_limit_by);
            $("#prop_number_advance_options").show();
            $("#element_file_ifc_span").hide();
            $("#element_plot_no_span").hide();
            $("#element_ownertype_span").hide();
            $("#prop_element_select_options").hide();
            $("#prop_table_name_conf").hide();
            $("#prop_option_query_conf").hide();
            $("#prop_application_conf").hide();
            if (v.number_enable_quantity == 1) {
                rebuild_quantity_link_dropdown();
                $("#prop_number_quantity_link").val(v.number_quantity_link);
                $("#prop_number_enable_quantity").prop("checked", true);
                $("#prop_number_quantity_link_div").show()
            } else {
                $("#prop_number_enable_quantity").prop("checked", false);
                $("#prop_number_quantity_link_div").hide()
            }
            if ((parseInt(v.range_min) + parseInt(v.range_max)) >= 1) {
                $("#prop_range legend").css({
                    "background-color": "#DC6666",
                    "border-color": "#DC6666"
                });
                $("#prop_range fieldset").css("border-color", "#DC6666")
            } else {
                $("#prop_range legend").css({
                    "background-color": "#3D6C10",
                    "border-color": "#3D6C10"
                });
                $("#prop_range fieldset").css("border-color", "#3D6C10")
            }
            $("#element_range_limit_by").hide();
            $("#element_range_number_limit_by").show();
            $("#prop_range").show()
        } else {
            if (v.type == "url") {
                $("#element_size").val(v.size);
                $("#prop_element_size").show();
                $("#element_default_value").val(v.default_value);
                $("#prop_default_value").show();
                $("#prop_placeholder").show();
                $("#element_file_ifc_span").hide();
                $("#element_plot_no_span").hide();
                $("#element_ownertype_span").hide();
                $("#prop_element_select_options").hide();
                $("#prop_table_name_conf").hide();
                $("#prop_option_query_conf").hide();
                $("#prop_application_conf").hide();
            } else {
                if (v.type == "email") {
                    $("#element_size").val(v.size);
                    $("#prop_element_size").show();
                    $("#element_default_value").val(v.default_value);
                    $("#prop_default_value").show();
                    $("#prop_placeholder").show();
					//OTB Start Notification to others
					$("#prop_notify_others").show();
					$("#element_file_ifc_span").hide();
					$("#element_plot_no_span").hide();
                    $("#element_ownertype_span").hide();
                    $("#prop_element_select_options").hide();
                    $("#prop_table_name_conf").hide();
                    $("#prop_option_query_conf").hide();
                    $("#prop_application_conf").hide();
                    if (v.notify_contact == 1) {
						$("#element_notify_contact").prop("checked", true);
					} else {
						$("#element_notify_contact").prop("checked", false);
					}//OTB End Notification to others
                } else {
                    if (v.type == "signature") {
                        $("#element_size").val(v.size);
                        $("#prop_element_size").show();
                        $("#element_unique_span").hide();
                        $("#element_readonly_span").hide();
                        $("#element_file_ifc_span").hide();
                        $("#element_plot_no_span").hide();
                        $("#element_ownertype_span").hide();
                        $("#prop_element_select_options").hide();
                        $("#prop_table_name_conf").hide();
                        $("#prop_option_query_conf").hide();
                        $("#prop_application_conf").hide();
                    } else {
                        if (v.type == "file") {
                            $("#prop_file_options").show();
                            $("#prop_file_block_or_allow").val(v.file_block_or_allow);
                            $("#file_type_list").val(v.file_type_list);
                            if (v.file_enable_type_limit == 1) {
                                $("#prop_file_enable_type_limit").prop("checked", true);
                                $("#form_file_limit_type").show()
                            } else {
                                $("#prop_file_enable_type_limit").prop("checked", false);
                                $("#form_file_limit_type").hide()
                            }
                            if (v.file_as_attachment == 1) {
                                $("#prop_file_as_attachment").prop("checked", true)
                            } else {
                                $("#prop_file_as_attachment").prop("checked", false)
                            }
                            if (v.file_auto_upload == 1) {
                                $("#prop_file_auto_upload").prop("checked", true)
                            } else {
                                $("#prop_file_auto_upload").prop("checked", false)
                            }
                            $("#file_max_selection").val(v.file_max_selection);
                            if (v.file_enable_multi_upload == 1) {
                                $("#prop_file_multi_upload").prop("checked", true);
                                $("#form_file_max_selection").show()
                            } else {
                                $("#prop_file_multi_upload").prop("checked", false);
                                $("#form_file_max_selection").hide()
                            }
                            $("#file_size_max").val(v.file_size_max);
                            if (v.file_enable_size_limit == 1) {
                                $("#prop_file_limit_size").prop("checked", true);
                                $("#form_file_limit_size").show()
                            } else {
                                $("#prop_file_limit_size").prop("checked", false);
                                $("#form_file_limit_size").hide()
                            }
                            if (v.file_enable_advance == 1) {
                                $("#prop_file_enable_advance").prop("checked", true);
                                $("#prop_file_advance_options").show()
                            } else {
                                $("#prop_file_enable_advance").prop("checked", false);
                                $("#prop_file_advance_options").hide()
                            }
                            $("#element_unique_span").hide();
                            $("#element_readonly_span").hide();
							//OTB Start File QR Code verification
                            $("#prop_file_authenticity_verification").show();
                            if (v.mark_file_with_qr_code == 1) {
                                $("#prop_mark_file_with_qr_code").prop("checked", true);
                            } else {
                                $("#prop_mark_file_with_qr_code").prop("checked", false);
                            }

                            if (v.file_qr_all_pages == 1) {
                                $("#prop_file_qr_all_pages").prop("checked", true);
                            } else {
                                $("#prop_file_qr_all_pages").prop("checked", false);
                            }

							if (v.file_qr_page_position == "top_left") {
								$("#prop_file_qr_page_position").val("top_left")
							}else if (v.file_qr_page_position == "top_right") {
								$("#prop_file_qr_page_position").val("top_right")
							}else if (v.file_qr_page_position == "bottom_left") {
								$("#prop_file_qr_page_position").val("bottom_left")
							}else if (v.file_qr_page_position == "bottom_right") {
								$("#prop_file_qr_page_position").val("bottom_right")
							}

							if (v.file_qr_users == "clients") {
								$("#prop_file_qr_users").val("clients")
							}else if (v.file_qr_users == "reviewers") {
								$("#prop_file_qr_users").val("reviewers")
							}else if (v.file_qr_users == "both") {
								$("#prop_file_qr_users").val("both")
							}
							//OTB End File QR Code verification
							$('#element_file_ifc_span').show();
                            if (v.file_ifc_span == 1) {
                                $("#element_file_ifc_span").prop("checked", true);
                            } else {
                                $("#element_file_ifc_span").prop("checked", false);
                            }
                            $("#prop_element_select_options").hide();
                            $("#prop_table_name_conf").hide();
                            $("#prop_option_query_conf").hide();
                            $("#prop_application_conf").hide();
                        } else {
                            if (v.type == "textarea") {
                                $("#element_size").val(v.size);
                                $("#prop_element_size").show();
                                $("#element_default_value_textarea").val(v.default_value);
                                $("#prop_default_value_textarea").show();
                                $("#prop_placeholder").show();
                                $("#element_range_min").val(v.range_min);
                                $("#element_range_max").val(v.range_max);
                                $("#element_range_limit_by").val(v.range_limit_by);
                                if ((parseInt(v.range_min) + parseInt(v.range_max)) >= 1) {
                                    $("#prop_range legend").css({
                                        "background-color": "#DC6666",
                                        "border-color": "#DC6666"
                                    });
                                    $("#prop_range fieldset").css("border-color", "#DC6666")
                                } else {
                                    $("#prop_range legend").css({
                                        "background-color": "#3D6C10",
                                        "border-color": "#3D6C10"
                                    });
                                    $("#prop_range fieldset").css("border-color", "#3D6C10")
                                }
                                $("#element_range_number_limit_by").hide();
                                $("#element_range_limit_by").show();
                                $("#prop_range").show();
                                $("#element_file_ifc_span").hide();
                                $("#element_plot_no_span").hide();
                                $("#element_ownertype_span").hide();
                                $("#prop_element_select_options").hide();
                                $("#prop_table_name_conf").hide();
                                $("#prop_option_query_conf").hide();
                                $("#prop_application_conf").hide();
                            } else {
                                if (v.type == "name" || v.type == "simple_name" || v.type == "name_wmiddle" || v.type == "simple_name_wmiddle") {
                                    $("#name_format").val(v.type);
                                    if (v.is_db_live == 1) {
                                        $("#name_format").prop("disabled", true)
                                    } else {
                                        $("#name_format").prop("disabled", false)
                                    }
                                    $("#prop_name_format").show();
                                    $("#element_unique_span").hide();
                                    $("#element_file_ifc_span").hide();
                                    $("#element_plot_no_span").hide();
                                    $("#element_ownertype_span").hide();
                                    $("#prop_element_select_options").hide();
                                    $("#prop_table_name_conf").hide();
                                    $("#prop_option_query_conf").hide();
                                    $("#prop_application_conf").hide();
                                } else {
                                    if (v.type == "time") {
                                        if (v.time_showsecond == 1) {
                                            $("#prop_time_showsecond").prop("checked", true)
                                        } else {
                                            $("#prop_time_showsecond").prop("checked", false)
                                        }
                                        if (v.time_24hour == 1) {
                                            $("#prop_time_24hour").prop("checked", true)
                                        } else {
                                            $("#prop_time_24hour").prop("checked", false)
                                        }
                                        $("#element_default_time").val(v.default_value);
                                        $("#prop_default_time").show();
                                        $("#prop_time_options").show();
                                        $("#element_file_ifc_span").hide();
                                        $("#element_plot_no_span").hide();
                                        $("#element_ownertype_span").hide();
                                        $("#prop_element_select_options").hide();
                                        $("#prop_table_name_conf").hide();
                                        $("#prop_option_query_conf").hide();
                                        $("#prop_application_conf").hide();
                                    } else {
                                        if (v.type == "address") {
                                            $("#element_countries").val(v.default_value);
                                            $("#prop_default_country").show();
                                            if (v.address_hideline2 == 1) {
                                                $("#prop_address_hideline2").prop("checked", true)
                                            } else {
                                                $("#prop_address_hideline2").prop("checked", false)
                                            }
                                            if (v.address_us_only == 1) {
                                                $("#prop_address_us_only").prop("checked", true);
                                                $("#element_countries").prop("disabled", true)
                                            } else {
                                                $("#prop_address_us_only").prop("checked", false);
                                                $("#element_countries").prop("disabled", false)
                                            }
                                            $("#prop_address_options").show();
                                            $("#element_unique_span").hide();
                                            $("#prop_element_select_options").hide();
                                            $("#prop_table_name_conf").hide();
                                            $("#prop_option_query_conf").hide();
                                            $("#prop_application_conf").hide();
                                        } else {
                                            if (v.type == "money") {
                                                $("#element_default_value").val(v.default_value);
                                                $("#prop_default_value").show();
                                                if (v.constraint == "" || v.constraint == "dollar") {
                                                    $("#money_format").val("dollar")
                                                } else {
                                                    $("#money_format").val(v.constraint)
                                                }
                                                $("#prop_remote_url_pr").val(v.option_query);
                                                $("#prop_remote_value_pr").val(v.remote_value);
                                                $("#prop_remote_username_pr").val(v.remote_username);
                                                $("#prop_remote_password_pr").val(v.remote_password);
                                                $("#prop_remote_server_field_pr").val(v.remote_server_field);
                                                $("#price_class").val(v.price_class);
                                                $("#prop_price_class").show();
                                                $("#prop_currency_format").show();
                                                $("#prop_element_select_options").hide();
                                                $("#prop_table_name_conf").hide();
                                                $("#prop_option_query_conf").hide();
                                                $("#prop_application_conf").hide();
                                            } else {
                                                if (v.type == "phone") {
                                                    $("#element_default_phone1").val(v.default_value.substr(0, 3));
                                                    $("#element_default_phone2").val(v.default_value.substr(3, 3));
                                                    $("#element_default_phone3").val(v.default_value.substr(6, 4));
                                                    $("#prop_default_phone").show();
                                                    $("#phone_format").val(v.type);
                                                    $("#prop_phone_format").show();
													//OTB Start Notification to others
													$("#prop_notify_others").show();
													if (v.notify_contact == 1) {
														$("#element_notify_contact").prop("checked", true);
													} else {
														$("#element_notify_contact").prop("checked", false);
													}//OTB End Notification to others
                                                } else {
                                                    if (v.type == "simple_phone") {
                                                        $("#element_default_value").val(v.default_value);
                                                        $("#prop_default_value").show();
                                                        $("#phone_format").val(v.type);
                                                        $("#prop_phone_format").show();
														//OTB Start Notification to others
														$("#prop_notify_others").show();
														if (v.notify_contact == 1) {
															$("#element_notify_contact").prop("checked", true);
														} else {
															$("#element_notify_contact").prop("checked", false);
														}//OTB End Notification to others
                                                    } else {
                                                        if (v.type == "date" || v.type == "europe_date") {
                                                            $("#date_type").val(v.type);
                                                            $("#prop_date_format").show();
                                                            $("#element_default_date").val(v.default_value);
                                                            $("#prop_default_date").show();
                                                            $("#prop_date_options").show();
                                                            if ((v.date_enable_range == 1) || (v.date_enable_selection_limit == 1) || (v.date_disable_past_future == 1) || (v.date_disable_weekend == 1) || (v.date_disable_specific == 1)) {
                                                                $("#prop_date_options legend").css({
                                                                    "background-color": "#DC6666",
                                                                    "border-color": "#DC6666"
                                                                });
                                                                $("#prop_date_options fieldset").css("border-color", "#DC6666")
                                                            } else {
                                                                $("#prop_date_options legend").css({
                                                                    "background-color": "#3D6C10",
                                                                    "border-color": "#3D6C10"
                                                                });
                                                                $("#prop_date_options fieldset").css("border-color", "#3D6C10")
                                                            }
                                                            var d = false;
                                                            if (v.date_range_min != null && v.date_range_min != "0000-00-00") {
                                                                if (v.date_range_min.indexOf("-00-00") > -1) {
                                                                    d = true
                                                                }
                                                                if (v.date_range_min.indexOf("-00-01") > -1) {
                                                                    d = true
                                                                }
                                                            }
                                                            if (v.date_range_max != null && v.date_range_max != "0000-00-00") {
                                                                if (v.date_range_max.indexOf("-00-00") > -1) {
                                                                    d = true
                                                                }
                                                                if (v.date_range_max.indexOf("-00-01") > -1) {
                                                                    d = true
                                                                }
                                                            }
                                                            if (v.date_enable_range == 1) {
                                                                $("#prop_date_range").prop("checked", true);
                                                                $("#prop_date_range_details").show();
                                                                $("#prop_date_range_fixed,#prop_date_range_relative").hide();
                                                                if (d == true) {
                                                                    $("#prop_date_range_format_switcher").val("relative");
                                                                    $("#prop_date_range_relative").show()
                                                                } else {
                                                                    $("#prop_date_range_format_switcher").val("fixed");
                                                                    $("#prop_date_range_fixed").show()
                                                                }
                                                            } else {
                                                                $("#prop_date_range").prop("checked", false);
                                                                $("#prop_date_range_details").hide()
                                                            }
                                                            if (d == true) {
                                                                if (v.date_range_min != null && v.date_range_min != "0000-00-00") {
                                                                    var n = v.date_range_min.split("-");
                                                                    if (n[2] == "01") {
                                                                        $("#date_range_min_relative").val(-parseInt(n[0]))
                                                                    } else {
                                                                        $("#date_range_min_relative").val(parseInt(n[0]))
                                                                    }
                                                                } else {
                                                                    $("#date_range_min_relative").val(0)
                                                                }
                                                                if (v.date_range_max != null && v.date_range_max != "0000-00-00") {
                                                                    var q = v.date_range_max.split("-");
                                                                    if (q[2] == "01") {
                                                                        $("#date_range_max_relative").val(-parseInt(q[0]))
                                                                    } else {
                                                                        $("#date_range_max_relative").val(parseInt(q[0]))
                                                                    }
                                                                } else {
                                                                    $("#date_range_max_relative").val(0)
                                                                }
                                                            } else {
                                                                if (v.date_range_min != null && v.date_range_min != "0000-00-00") {
                                                                    var n = v.date_range_min.split("-");
                                                                    $("#date_range_min_yyyy").val(n[0]);
                                                                    $("#date_range_min_mm").val(parseInt(n[1], 10));
                                                                    $("#date_range_min_dd").val(parseInt(n[2], 10));
                                                                    $("#linked_picker_range_min").datepick("setDate", new Date(parseInt(n[0], 10), parseInt(n[1], 10) - 1, parseInt(n[2], 10)))
                                                                } else {
                                                                    $("#date_range_min_yyyy").val("");
                                                                    $("#date_range_min_mm").val("");
                                                                    $("#date_range_min_dd").val("")
                                                                }
                                                                if (v.date_range_max != null && v.date_range_max != "0000-00-00") {
                                                                    var q = v.date_range_max.split("-");
                                                                    $("#date_range_max_yyyy").val(q[0]);
                                                                    $("#date_range_max_mm").val(parseInt(q[1], 10));
                                                                    $("#date_range_max_dd").val(parseInt(q[2], 10));
                                                                    $("#linked_picker_range_max").datepick("setDate", new Date(parseInt(q[0], 10), parseInt(q[1], 10) - 1, parseInt(q[2], 10)))
                                                                } else {
                                                                    $("#date_range_max_yyyy").val("");
                                                                    $("#date_range_max_mm").val("");
                                                                    $("#date_range_max_dd").val("")
                                                                }
                                                            }
                                                            if (v.date_enable_selection_limit == 1) {
                                                                $("#prop_date_selection_limit").prop("checked", true);
                                                                $("#form_date_selection_limit").show();
                                                                $("#date_selection_max").val(v.date_selection_max)
                                                            } else {
                                                                $("#prop_date_selection_limit").prop("checked", false);
                                                                $("#form_date_selection_limit").hide();
                                                                $("#date_selection_max").val(v.date_selection_max)
                                                            }
                                                            if (v.date_disable_past_future == 1) {
                                                                $("#prop_date_past_future_selection").prop("checked", true);
                                                                $("#prop_date_past_future").val(v.date_past_future).prop("disabled", false)
                                                            } else {
                                                                $("#prop_date_past_future_selection").prop("checked", false);
                                                                $("#prop_date_past_future").val(v.date_past_future).prop("disabled", true)
                                                            }
                                                            if (v.date_disable_weekend == 1) {
                                                                $("#prop_date_disable_weekend").prop("checked", true)
                                                            } else {
                                                                $("#prop_date_disable_weekend").prop("checked", false)
                                                            }
                                                            if (v.date_disable_specific == 1) {
                                                                $("#prop_date_disable_specific").prop("checked", true);
                                                                $("#form_date_disable_specific").show()
                                                            } else {
                                                                $("#prop_date_disable_specific").prop("checked", false);
                                                                $("#form_date_disable_specific").hide()
                                                            }
                                                            if (v.date_disabled_list != null && v.date_disabled_list != "") {
                                                                var t = v.date_disabled_list.split(", ");
                                                                $("#date_disabled_list").datepick("setDate", t)
                                                            }
                                                            $("#date_disabled_list").val(v.date_disabled_list)
                                                        } else {
                                                            if (v.type == "section") {
                                                                $("#prop_options").hide();
                                                                if (v.section_display_in_email == 1) {
                                                                    $("#prop_section_email_display").prop("checked", true)
                                                                } else {
                                                                    $("#prop_section_email_display").prop("checked", false)
                                                                }
                                                                $("#prop_section_size").val(v.size);
                                                                if (v.section_enable_scroll == 1) {
                                                                    $("#prop_section_enable_scroll").prop("checked", true);
                                                                    $("#div_section_size").show()
                                                                } else {
                                                                    $("#prop_section_enable_scroll").prop("checked", false);
                                                                    $("#div_section_size").hide()
                                                                }
                                                                $("#prop_section_options").show()
                                                            } else {
                                                                if (v.type == "radio" || v.type == "checkbox" || v.type == "select") {
                                                                    var h = "";
                                                                    var o = 0;
                                                                    var g = 0;
                                                                    var k = "";
                                                                    var f = "";
                                                                    var u = "";
                                                                    var l = "";
                                                                    var e = v.options;
                                                                    var b = new Array();
                                                                    $.each(e, function(w, x) {
                                                                        b[x.position] = w
                                                                    });
                                                                    if (v.type == "radio" || v.type == "select") {
                                                                        l = "radio"
                                                                    } else {
                                                                        if (v.type == "checkbox") {
                                                                            l = "checkbox"
                                                                        }
                                                                    }
                                                                    if (v.type == "select") {
                                                                        $("#element_size").val(v.size);
                                                                        $("#prop_element_size").show();
                                                                        $("#element_select_options").val(v.select_options);
                                                                        $("#prop_element_select_options").show();
                                                                        if(v.select_options == "table"){
                                                                            $("#table_name").val(v.table_name);
                                                                            $("#field_value").val(v.field_value);
                                                                            $("#field_name").val(v.field_name);
                                                                            $("#prop_table_name_conf").show();
                                                                        }
                                                                        if(v.select_options == "query"){
                                                                            $("#option_query").val(v.option_query);
                                                                            $("#field_value_option_query").val(v.field_value);
                                                                            $("#field_name_option_query").val(v.field_name);
                                                                            $("#prop_option_query_conf").show();
                                                                        }
                                                                        if(v.select_options == "application"){
                                                                            $("#existing_form").val(v.existing_form);
                                                                            $("#existing_stage").val(v.existing_stage);
                                                                            $("#prop_application_conf").show();
                                                                        }
                                                                    }
                                                                    $.each(b, function(w, x) {
                                                                        if (w > 0) {
                                                                            g = x;
                                                                            h = v.options[x].option;
                                                                            o = w;
                                                                            if (v.options[x].is_default == "1") {
                                                                                u = ' checked="checked" '
                                                                            } else {
                                                                                u = ""
                                                                            }
                                                                            k = '<li><input type="' + l + '" ' + u + ' title="Select this choice as the default." class="choices_default" name="choices_default" id="choicedef_' + g + '" /><input type="text" value="' + h.replace(/\"/g, "&quot;") + '" autocomplete="off" class="text" id="choice_' + g + '" /> <img class="add_choice" title="Add" alt="Add" src="/form_builder/images/icons/add.png" style="vertical-align: middle" id="choiceadd_' + g + '"><img class="del_choice" title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png" style="vertical-align: middle" id="choicedel_' + g + '"></li>';
                                                                            f += k
                                                                        }
                                                                    });
                                                                    $("#element_choices li").remove();
                                                                    $("#element_choices").append(f);
                                                                    $("#prop_choices").show();
                                                                    if (v.type == "radio" || v.type == "checkbox") {
                                                                        $("#element_choice_columns").val(v.choice_columns);
                                                                        $("#prop_choice_columns").show()
                                                                    }
                                                                    if (v.type == "radio" || v.type == "checkbox") {
                                                                        $("#prop_other_choices_label").val(v.choice_other_label);
                                                                        if (v.choice_has_other == 1) {
                                                                            $("#prop_choices_other_checkbox").prop("checked", true);
                                                                            $("#prop_other_choices_label").prop("disabled", false)
                                                                        } else {
                                                                            $("#prop_choices_other_checkbox").prop("checked", false);
                                                                            $("#prop_other_choices_label").prop("disabled", true)
                                                                        }
                                                                        if (v.type == "radio") {
                                                                            if (v.constraint == "random") {
                                                                                $("#prop_choices_randomize").prop("checked", true)
                                                                            } else {
                                                                                $("#prop_choices_randomize").prop("checked", false)
                                                                            }
                                                                            $("#prop_choices_randomize_span").show()
                                                                        } else {
                                                                            $("#prop_choices_randomize_span").hide()
                                                                        }
                                                                        $("#prop_choices_other").show()
                                                                    }
                                                                    if (v.type == "radio" || v.type == "select") {
                                                                        $("#choice_max_entry").val(v.choice_max_entry);
                                                                        if (v.choice_max_entry > 0) {
                                                                            $("#prop_choice_max_entry_enable").prop("checked", true);
                                                                            $("#form_choice_max_entry").show()
                                                                        } else {
                                                                            $("#prop_choice_max_entry_enable").prop("checked", false);
                                                                            $("#form_choice_max_entry").hide()
                                                                        }
                                                                        $("#prop_choice_max_entry").show()
                                                                    }
                                                                    if (v.type == "checkbox") {
                                                                        $("#prop_choice_limit_rule").val(v.choice_limit_rule);
                                                                        $("#prop_choice_limit_qty").val(v.choice_limit_qty);
                                                                        $("#prop_choice_limit_range_min").val(v.choice_limit_range_min);
                                                                        $("#prop_choice_limit_range_max").val(v.choice_limit_range_max);
                                                                        $("#prop_choice_limit_range_span,#prop_choice_limit_qty_span").hide();
                                                                        if (v.choice_limit_rule == "between") {
                                                                            $("#prop_choice_limit_range_span").show()
                                                                        } else {
                                                                            $("#prop_choice_limit_qty_span").show()
                                                                        }
                                                                        if (v.is_required == "1") {
                                                                            $("#prop_choice_limit").show()
                                                                        }
                                                                    }
                                                                    $("#element_unique_span").hide();
                                                                    $("#element_readonly_span").hide()
                                                                } else {
                                                                    if (v.type == "matrix") {
                                                                        var h = "";
                                                                        var o = 0;
                                                                        var g = 0;
                                                                        var k = "";
                                                                        var f = "";
                                                                        var j = v.options;
                                                                        var b = new Array();
                                                                        $.each(j, function(w, x) {
                                                                            b[x.position] = w
                                                                        });
                                                                        var m = "";
                                                                        $.each(b, function(w, x) {
                                                                            if (w > 0) {
                                                                                choice_element_id = x;
                                                                                h = v.options[x].row_title;
                                                                                o = w;
                                                                                if (w > 1) {
                                                                                    m = '<img class="del_choice" title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png" style="vertical-align: middle" id="matrixrowdel_' + choice_element_id + '">'
                                                                                }
                                                                                k = '<li><input type="text" value="' + h.replace(/\"/g, "&quot;") + '" autocomplete="off" class="text" id="matrixrow_' + choice_element_id + '" /> <img class="add_choice" title="Add" alt="Add" src="/form_builder/images/icons/add.png" style="vertical-align: middle" id="matrixrowadd_' + choice_element_id + '">' + m + "</li>";
                                                                                f += k
                                                                            }
                                                                        });
                                                                        $("#element_matrix_row li").remove();
                                                                        $("#element_matrix_row").append(f);
                                                                        $("#prop_matrix_row").show();
                                                                        f = "";
                                                                        var s = parseInt($("#" + active_element).data("field_properties").id);
                                                                        var i = v.options[s].column_data;
                                                                        var r = new Array();
                                                                        $.each(i, function(w, x) {
                                                                            r[x.position] = w
                                                                        });
                                                                        $.each(r, function(w, x) {
                                                                            if (w > 0) {
                                                                                g = x;
                                                                                h = i[x].column_title;
                                                                                o = w;
                                                                                k = '<li><input type="text" value="' + h.replace(/\"/g, "&quot;") + '" autocomplete="off" class="text" id="matrixcol_' + g + '" /> <img class="add_choice" title="Add" alt="Add" src="/form_builder/images/icons/add.png" style="vertical-align: middle" id="matrixcoladd_' + g + '"><img class="del_choice" title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png" style="vertical-align: middle" id="matrixcoldel_' + g + '"></li>';
                                                                                f += k
                                                                            }
                                                                        });
                                                                        $("#element_matrix_column li").remove();
                                                                        $("#element_matrix_column").append(f);
                                                                        $("#prop_matrix_column").show();
                                                                        if (v.matrix_allow_multiselect == 1) {
                                                                            $("#prop_matrix_allow_multiselect").prop("checked", true)
                                                                        } else {
                                                                            $("#prop_matrix_allow_multiselect").prop("checked", false)
                                                                        }
                                                                        if ($("#" + active_element).hasClass("dblive")) {
                                                                            $("#prop_matrix_allow_multiselect").prop("disabled", true)
                                                                        } else {
                                                                            $("#prop_matrix_allow_multiselect").prop("disabled", false)
                                                                        }
                                                                        $("#prop_matrix_options").show();
                                                                        $("#prop_instructions").hide();
                                                                        $("#element_unique_span").hide();
                                                                        $("#element_label").val(v.guidelines);
                                                                        $("#element_readonly_span").hide()
                                                                    } else {
                                                                        if (v.type == "page_break") {
                                                                            $("#all_properties li").hide();
                                                                            $("#prop_page_break_button").show();
                                                                            if (v.submit_use_image == 1) {
                                                                                $("#prop_submit_use_image").prop("checked", true);
                                                                                $("#prop_submit_use_text").prop("checked", false);
                                                                                if (v.submit_primary_img == "" || v.submit_primary_img == null) {
                                                                                    $("#submit_primary_img").val("http://")
                                                                                } else {
                                                                                    $("#submit_primary_img").val(v.submit_primary_img)
                                                                                }
                                                                                if (v.submit_secondary_img == "" || v.submit_secondary_img == null) {
                                                                                    $("#submit_secondary_img").val("http://")
                                                                                } else {
                                                                                    $("#submit_secondary_img").val(v.submit_secondary_img)
                                                                                }
                                                                                if ($("#" + active_element).hasClass("firstpage")) {
                                                                                    $("#lbl_submit_secondary_img").hide();
                                                                                    $("#submit_secondary_img").hide()
                                                                                } else {
                                                                                    $("#lbl_submit_secondary_img").show();
                                                                                    $("#submit_secondary_img").show()
                                                                                }
                                                                                $("#div_submit_use_image").show();
                                                                                $("#div_submit_use_text").hide()
                                                                            } else {
                                                                                $("#prop_submit_use_image").prop("checked", false);
                                                                                $("#prop_submit_use_text").prop("checked", true);
                                                                                $("#submit_primary_text").val(v.submit_primary_text);
                                                                                $("#submit_secondary_text").val(v.submit_secondary_text);
                                                                                if ($("#" + active_element).hasClass("firstpage")) {
                                                                                    $("#lbl_submit_secondary_text").hide();
                                                                                    $("#submit_secondary_text").hide()
                                                                                } else {
                                                                                    $("#lbl_submit_secondary_text").show();
                                                                                    $("#submit_secondary_text").show()
                                                                                }
                                                                                $("#div_submit_use_image").hide();
                                                                                $("#div_submit_use_text").show()
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $("#element_label").focus().select()
}

function set_properties(D, x) {
    var h = $("#" + active_element);
    var A = h.data("field_properties");
    var O = ' <span class="required">*</span>';
    var l = active_element.split("_");
    var v = l[1];

    if(D == "prop_remote_url")
    {
        A.option_query = x;
    }
    else if(D == "prop_remote_criteria")
    {
        A.field_name = x;
    }
    else if(D == "prop_field_value")
    {
        A.field_value = x;
    }
    else if(D == "prop_field_error_message")
    {
        A.field_error_message = x;
    }
    else if(D == "prop_remote_value")
    {
        A.remote_value = x;
    }
    else if(D == "prop_remote_username")
    {
        A.remote_username = x;
    }
    else if(D == "prop_remote_password")
    {
        A.remote_password = x;
    }
    else if(D == "prop_remote_server_field")
    {
        A.remote_server_field = x;
    }
    else if(D == "prop_remote_url_pr")
    {
        A.option_query = x;
    }
    else if(D == "prop_remote_username_pr")
    {
        A.remote_username = x;
    }
    else if(D == "prop_remote_password_pr")
    {
        A.remote_password = x;
    }
    else if(D == "prop_remote_server_field_pr")
    {
        A.remote_server_field = x;
    }
    else if(D == "prop_remote_value_pr")
    {
        A.remote_value = x;
    }
    else if(D == "price_class")
    {
        A.price_class = x;
    }
    //OTB patch for footprint
    else if(D == "element_footprint")
    {
        A.footprint = x;
    }
    else if(D == "element_plotsize")
    {
        A.plotsize = x;
    }
    else if(D == "element_actualplotratio")
    {
        A.actualplotratio = x;
    }
    else if(D == "element_permittedgroundcoverage")
    {
        A.permittedgroundcoverage = x;
    }
     else if(D == "element_zone")
    {
        A.zone = x;
    }
    else if(D == "element_projectcost")
    {
        A.projectcost = x;
    }
    else if(D == "element_groundcoveragereason")
    {
        A.groundcoveragereason = x;
    }
    //OTB patch - grandtotalplintharea 
    else if(D == "element_grandtotalplintharea")
    {
        A.grandtotalplintharea = x;
    } //OTB patch element_grandtotalfootprintarea
     else if(D == "element_grandtotalfootprintarea")
    {
        A.grandtotalfootprintarea = x;
    }
    else if(D == "element_plotratioreason")
    {
        A.plotratioreason = x;
    }
     else if(D == "element_permitteduser")
    {
        A.permitteduser = x;
    }
	//OTB Start File QR Code verification
    else if(D == "prop_mark_file_with_qr_code")
    {
        A.mark_file_with_qr_code = x;	
	}
    else if(D == "prop_file_qr_all_pages")
    {
        A.file_qr_all_pages = x;	
	}
    else if(D == "prop_file_qr_page_position")
    {
        A.file_qr_page_position = x;	
	}
    else if(D == "prop_file_qr_users")
    {
        A.file_qr_users = x;	
	}
	//OTB End File QR Code verification
	//OTB Start Copy notification to other contacts
	else if(D == "element_notify_contact")
    {
        A.notify_contact = x;	
	}
	else if(D == "element_file_ifc")
    {
        A.file_ifc = x;	
	}
	else if(D == "element_plot_no")
    {
        A.plot_no = x;	
    }
	else if(D == "element_ownertype")
    {
        A.ownertype = x;	
    }
	else if(D == "element_select_options")
    {
        A.select_options = x;	
    }
	else if(D == "table_name")
    {
        A.table_name = x;	
    }
	else if(D == "field_value")
    {
        A.field_value = x;	
    }
	else if(D == "field_name")
    {
        A.field_name = x;	
    }
	else if(D == "option_query")
    {
        A.option_query = x;	
    }
	else if(D == "field_value_option_query")
    {
        A.field_value = x;	
    }
	else if(D == "field_name_option_query")
    {
        A.field_name = x;	
    }
	else if(D == "existing_form")
    {
        A.existing_form = x;	
    }
	else if(D == "existing_stage")
    {
        A.existing_stage = x;	
	}
	//OTB End Copy notification to other contacts
    if (D == "element_label") {
        if (A.type == "section") {
            $("#" + active_element + " > h3").html(x.replace(/\n/g, "<br />"));
            A.title = x
        } else {
            if (A.type == "matrix") {
                if (A.is_required === "0") {
                    O = ""
                }
                $("#" + active_element + " caption").html(x.replace(/\n/g, "<br />") + O);
                A.guidelines = x
            } else {
                if (A.is_required === "0") {
                    O = ""
                }
                $("#" + active_element + " > label").html(x.replace(/\n/g, "<br />") + O);
                $("#" + active_element + " > span.description").html(x.replace(/\n/g, "<br />") + O);
                A.title = x
            }
        }
    } else {
        if (D == "element_required") {
            if (x == "1") {
                if (A.type == "matrix") {
                    $("#" + active_element + " caption").append(O)
                } else {
                    $("#" + active_element + " > label").append(O);
                    $("#" + active_element + " > span.description").append(O)
                }
                if (A.type == "checkbox") {
                    $("#prop_choice_limit").fadeIn()
                }
            } else {
                if (A.type == "checkbox") {
                    $("#prop_choice_limit").hide()
                }
                $("#" + active_element + " span.required").remove()
            }
            A.is_required = x;
            if ((A.is_required == "1") || (A.is_unique == "1") || (A.is_readonly == "1")) {
                $("#prop_options legend").css({
                    "background-color": "#DC6666",
                    "border-color": "#DC6666"
                });
                $("#prop_options fieldset").css("border-color", "#DC6666")
            } else {
                $("#prop_options legend").css({
                    "background-color": "#3D6C10",
                    "border-color": "#3D6C10"
                });
                $("#prop_options fieldset").css("border-color", "#3D6C10")
            }
        } else {
            if (D == "element_unique") {
                A.is_unique = x;
                if ((A.is_required == "1") || (A.is_unique == "1") || (A.is_readonly == "1")) {
                    $("#prop_options legend").css({
                        "background-color": "#DC6666",
                        "border-color": "#DC6666"
                    });
                    $("#prop_options fieldset").css("border-color", "#DC6666")
                } else {
                    $("#prop_options legend").css({
                        "background-color": "#3D6C10",
                        "border-color": "#3D6C10"
                    });
                    $("#prop_options fieldset").css("border-color", "#3D6C10")
                }
            } else {
                if (D == "element_readonly") {
                    A.is_readonly = x;
                    if ((A.is_required == "1") || (A.is_unique == "1") || (A.is_readonly == "1")) {
                        $("#prop_options legend").css({
                            "background-color": "#DC6666",
                            "border-color": "#DC6666"
                        });
                        $("#prop_options fieldset").css("border-color", "#DC6666")
                    } else {
                        $("#prop_options legend").css({
                            "background-color": "#3D6C10",
                            "border-color": "#3D6C10"
                        });
                        $("#prop_options fieldset").css("border-color", "#3D6C10")
                    }
                } else {
                    if (D == "element_visibility") {
                        if (x == "1") {
                            $("#" + active_element).addClass("private");
                            $("#prop_access_control legend").css({
                                "background-color": "#DC6666",
                                "border-color": "#DC6666"
                            });
                            $("#prop_access_control fieldset").css("border-color", "#DC6666")
                        } else {
                            $("#" + active_element).removeClass("private");
                            $("#prop_access_control legend").css({
                                "background-color": "#3D6C10",
                                "border-color": "#3D6C10"
                            });
                            $("#prop_access_control fieldset").css("border-color", "#3D6C10")
                        }
                        A.is_private = x
                    } else {
                        if (D == "element_text_default_type") {
                            $("#default_value_text_static_div,#default_value_text_random_div").hide();
                            if (x == "static") {
                                $("#default_value_text_static_div").show();
                                $("#" + active_element + " :input").val(A.default_value)
                            } else {
                                $("#default_value_text_random_div").show();
                                rebuild_default_text_random_preview()
                            }
                            A.text_default_type = x
                        } else {
                            if (D == "element_guidelines") {
                                if (A.type != "section") {
                                    if ($("#" + active_element + " > p.guidelines").length) {
                                        $("#" + active_element + " > p.guidelines").html(x.replace(/\n/g, "<br />"));
                                        if ($("#" + active_element + " > p.guidelines").text().length < 1) {
                                            $("#" + active_element + " > p.guidelines").remove();
                                            if ($("#form_builder_sortable p.guidelines").length == 0) {
                                                if ($("#form_builder_sortable > li").size() <= 50) {
                                                    $("#form_builder_sortable > li").not(".li_pagination").animate({
                                                        width: "97%"
                                                    }, {
                                                        duration: 250,
                                                        queue: true,
                                                        complete: function() {
                                                            $("#main_body").addClass("no_guidelines")
                                                        }
                                                    })
                                                } else {
                                                    $("#form_builder_sortable > li").not(".li_pagination").css("width", "97%")
                                                }
                                                $("#main_body").addClass("no_guidelines")
                                            }
                                        }
                                    } else {
                                        if (x != "") {
                                            if ($("#main_body").hasClass("no_guidelines")) {
                                                var G = "61%";
                                                if ($("#form_header").data("form_properties").label_alignment != "top_label") {
                                                    G = "76%"
                                                }
                                                if ($("#form_builder_sortable > li").size() <= 50) {
                                                    $("#form_builder_sortable > li").not(".li_pagination").animate({
                                                        width: G
                                                    }, {
                                                        duration: 250,
                                                        queue: true,
                                                        complete: function() {
                                                            $("#main_body").removeClass("no_guidelines")
                                                        }
                                                    })
                                                } else {
                                                    $("#form_builder_sortable > li").not(".li_pagination").css("width", G)
                                                }
                                                $("#main_body").removeClass("no_guidelines")
                                            }
                                            $("#" + active_element).append('<p class="guidelines">' + x.replace(/\n/g, "<br />") + "</p>")
                                        }
                                    }
                                } else {
                                    $("#" + active_element + " > p").html(x.replace(/\n/g, "<br />"))
                                }
                                A.guidelines = x
                            } else {
                                if (D == "element_custom_css") {
                                    A.css_class = x
                                } else {
                                    if (D == "element_section_display_in_email") {
                                        A.section_display_in_email = x
                                    } else {
                                        if (D == "element_enable_placeholder") {
                                            A.enable_placeholder = x;
                                            $("#" + active_element + " :input").attr("placeholder", x)
                                        } else {
                                            if (D == "element_date_enable_range") {
                                                if (x == 1) {
                                                    $("#prop_date_range_details").slideDown();
                                                    $("#prop_date_past_future").prop("disabled", true);
                                                    $("#prop_date_past_future_selection").prop("checked", false);
                                                    A.date_disable_past_future = 0
                                                } else {
                                                    $("#prop_date_range_details").fadeOut("slow")
                                                }
                                                A.date_enable_range = x;
                                                if ((A.date_enable_range == 1) || (A.date_enable_selection_limit == 1) || (A.date_disable_past_future == 1) || (A.date_disable_weekend == 1) || (A.date_disable_specific == 1)) {
                                                    $("#prop_date_options legend").css({
                                                        "background-color": "#DC6666",
                                                        "border-color": "#DC6666"
                                                    });
                                                    $("#prop_date_options fieldset").css("border-color", "#DC6666")
                                                } else {
                                                    $("#prop_date_options legend").css({
                                                        "background-color": "#3D6C10",
                                                        "border-color": "#3D6C10"
                                                    });
                                                    $("#prop_date_options fieldset").css("border-color", "#3D6C10")
                                                }
                                            } else {
                                                if (D == "element_date_range_format") {
                                                    $("#prop_date_range_fixed,#prop_date_range_relative").hide();
                                                    $("#date_range_min_yyyy,#date_range_min_mm,#date_range_min_dd").val("");
                                                    $("#date_range_max_yyyy,#date_range_max_mm,#date_range_max_dd").val("");
                                                    $("#date_range_min_relative,#date_range_max_relative").val(0);
                                                    if (x == "relative") {
                                                        $("#prop_date_range_relative").fadeIn();
                                                        var a = ("0000" + parseInt($("#date_range_min_relative").val())).slice(-4);
                                                        var S = ("0000" + parseInt($("#date_range_max_relative").val())).slice(-4);
                                                        A.date_range_min = a + "-00-00";
                                                        A.date_range_max = S + "-00-00"
                                                    } else {
                                                        if (x == "fixed") {
                                                            $("#prop_date_range_fixed").fadeIn();
                                                            A.date_range_min = $("#date_range_min_yyyy").val() + "-" + $("#date_range_min_mm").val() + "-" + $("#date_range_min_dd").val();
                                                            A.date_range_max = $("#date_range_max_yyyy").val() + "-" + $("#date_range_max_mm").val() + "-" + $("#date_range_max_dd").val()
                                                        }
                                                    }
                                                } else {
                                                    if (D == "element_date_range_min") {
                                                        A.date_range_min = x
                                                    } else {
                                                        if (D == "element_date_range_max") {
                                                            A.date_range_max = x
                                                        } else {
                                                            if (D == "element_date_enable_selection_limit") {
                                                                if (x == 1) {
                                                                    $("#form_date_selection_limit").slideDown()
                                                                } else {
                                                                    $("#form_date_selection_limit").fadeOut("slow")
                                                                }
                                                                A.date_enable_selection_limit = x;
                                                                if ((A.date_enable_range == 1) || (A.date_enable_selection_limit == 1) || (A.date_disable_past_future == 1) || (A.date_disable_weekend == 1) || (A.date_disable_specific == 1)) {
                                                                    $("#prop_date_options legend").css({
                                                                        "background-color": "#DC6666",
                                                                        "border-color": "#DC6666"
                                                                    });
                                                                    $("#prop_date_options fieldset").css("border-color", "#DC6666")
                                                                } else {
                                                                    $("#prop_date_options legend").css({
                                                                        "background-color": "#3D6C10",
                                                                        "border-color": "#3D6C10"
                                                                    });
                                                                    $("#prop_date_options fieldset").css("border-color", "#3D6C10")
                                                                }
                                                            } else {
                                                                if (D == "element_section_enable_scroll") {
                                                                    if (x == 1) {
                                                                        $("#" + active_element).removeClass("section_scroll_small section_scroll_medium section_scroll_large").addClass("section_scroll_" + A.size);
                                                                        $("#div_section_size").slideDown()
                                                                    } else {
                                                                        $("#" + active_element).removeClass("section_scroll_small section_scroll_medium section_scroll_large");
                                                                        $("#div_section_size").fadeOut("fast")
                                                                    }
                                                                    A.section_enable_scroll = x
                                                                } else {
                                                                    if (D == "element_date_selection_max") {
                                                                        A.date_selection_max = parseInt(x)
                                                                    } else {
                                                                        if (D == "element_date_disable_specific") {
                                                                            if (x == 1) {
                                                                                $("#form_date_disable_specific").slideDown()
                                                                            } else {
                                                                                $("#form_date_disable_specific").fadeOut("slow")
                                                                            }
                                                                            A.date_disable_specific = x;
                                                                            if ((A.date_enable_range == 1) || (A.date_enable_selection_limit == 1) || (A.date_disable_past_future == 1) || (A.date_disable_weekend == 1) || (A.date_disable_specific == 1)) {
                                                                                $("#prop_date_options legend").css({
                                                                                    "background-color": "#DC6666",
                                                                                    "border-color": "#DC6666"
                                                                                });
                                                                                $("#prop_date_options fieldset").css("border-color", "#DC6666")
                                                                            } else {
                                                                                $("#prop_date_options legend").css({
                                                                                    "background-color": "#3D6C10",
                                                                                    "border-color": "#3D6C10"
                                                                                });
                                                                                $("#prop_date_options fieldset").css("border-color", "#3D6C10")
                                                                            }
                                                                        } else {
                                                                            if (D == "element_date_disabled_list") {
                                                                                A.date_disabled_list = x
                                                                            } else {
                                                                                if (D == "element_date_disable_past_future") {
                                                                                    if (x == 1) {
                                                                                        $("#prop_date_past_future").prop("disabled", false);
                                                                                        $("#prop_date_range_details").fadeOut("slow");
                                                                                        $("#prop_date_range").prop("checked", false);
                                                                                        A.date_enable_range = 0
                                                                                    } else {
                                                                                        $("#prop_date_past_future").prop("disabled", true)
                                                                                    }
                                                                                    A.date_disable_past_future = x;
                                                                                    if ((A.date_enable_range == 1) || (A.date_enable_selection_limit == 1) || (A.date_disable_past_future == 1) || (A.date_disable_weekend == 1) || (A.date_disable_specific == 1)) {
                                                                                        $("#prop_date_options legend").css({
                                                                                            "background-color": "#DC6666",
                                                                                            "border-color": "#DC6666"
                                                                                        });
                                                                                        $("#prop_date_options fieldset").css("border-color", "#DC6666")
                                                                                    } else {
                                                                                        $("#prop_date_options legend").css({
                                                                                            "background-color": "#3D6C10",
                                                                                            "border-color": "#3D6C10"
                                                                                        });
                                                                                        $("#prop_date_options fieldset").css("border-color", "#3D6C10")
                                                                                    }
                                                                                } else {
                                                                                    if (D == "element_date_disable_weekend") {
                                                                                        A.date_disable_weekend = x;
                                                                                        if ((A.date_enable_range == 1) || (A.date_enable_selection_limit == 1) || (A.date_disable_past_future == 1) || (A.date_disable_weekend == 1) || (A.date_disable_specific == 1)) {
                                                                                            $("#prop_date_options legend").css({
                                                                                                "background-color": "#DC6666",
                                                                                                "border-color": "#DC6666"
                                                                                            });
                                                                                            $("#prop_date_options fieldset").css("border-color", "#DC6666")
                                                                                        } else {
                                                                                            $("#prop_date_options legend").css({
                                                                                                "background-color": "#3D6C10",
                                                                                                "border-color": "#3D6C10"
                                                                                            });
                                                                                            $("#prop_date_options fieldset").css("border-color", "#3D6C10")
                                                                                        }
                                                                                    } else {
                                                                                        if (D == "date_past_future") {
                                                                                            A.date_past_future = x
                                                                                        } else {
                                                                                            if (D == "element_file_enable_type_limit") {
                                                                                                if (x == 1) {
                                                                                                    $("#form_file_limit_type").slideDown()
                                                                                                } else {
                                                                                                    $("#form_file_limit_type").fadeOut("slow")
                                                                                                }
                                                                                                A.file_enable_type_limit = x
                                                                                            } else {
                                                                                                if (D == "element_file_block_or_allow") {
                                                                                                    A.file_block_or_allow = x;
                                                                                                    A.file_type_list = "";
                                                                                                    $("#file_type_list").val("")
                                                                                                } else {
                                                                                                    if (D == "element_file_type_list") {
                                                                                                        A.file_type_list = x
                                                                                                    } else {
                                                                                                        if (D == "element_file_as_attachment") {
                                                                                                            A.file_as_attachment = x
                                                                                                        } else {
                                                                                                            if (D == "element_file_auto_upload") {
                                                                                                                A.file_auto_upload = x
                                                                                                            } else {
                                                                                                                if (D == "element_file_enable_multi_upload") {
                                                                                                                    if (x == 1) {
                                                                                                                        $("#form_file_max_selection").slideDown()
                                                                                                                    } else {
                                                                                                                        $("#form_file_max_selection").fadeOut("slow")
                                                                                                                    }
                                                                                                                    A.file_enable_multi_upload = x
                                                                                                                } else {
                                                                                                                    if (D == "element_file_max_selection") {
                                                                                                                        A.file_max_selection = parseInt(x)
                                                                                                                    } else {
                                                                                                                        if (D == "element_file_enable_size_limit") {
                                                                                                                            if (x == 1) {
                                                                                                                                $("#form_file_limit_size").slideDown()
                                                                                                                            } else {
                                                                                                                                $("#form_file_limit_size").fadeOut("slow")
                                                                                                                            }
                                                                                                                            A.file_enable_size_limit = x
                                                                                                                        } else {
                                                                                                                            if (D == "element_file_size_max") {
                                                                                                                                A.file_size_max = parseInt(x)
                                                                                                                            } else {
                                                                                                                                if (D == "element_file_enable_advance") {
                                                                                                                                    if (x == 1) {
                                                                                                                                        $("#prop_file_advance_options").slideDown()
                                                                                                                                    } else {
                                                                                                                                        $("#prop_file_advance_options").fadeOut("slow")
                                                                                                                                    }
                                                                                                                                    A.file_enable_advance = x
                                                                                                                                } else {
                                                                                                                                    if (D == "element_number_enable_quantity") {
                                                                                                                                        if (x == 1) {
                                                                                                                                            rebuild_quantity_link_dropdown();
                                                                                                                                            $("#prop_number_quantity_link_div").slideDown()
                                                                                                                                        } else {
                                                                                                                                            $("#prop_number_quantity_link_div").fadeOut("slow")
                                                                                                                                        }
                                                                                                                                        A.number_enable_quantity = x
                                                                                                                                    } else {
                                                                                                                                        if (D == "element_number_quantity_link") {
                                                                                                                                            A.number_quantity_link = x
                                                                                                                                        } else {
                                                                                                                                            if (D == "element_choice_columns") {
                                                                                                                                                var r = "";
                                                                                                                                                if (x == 1) {
                                                                                                                                                    r = ""
                                                                                                                                                } else {
                                                                                                                                                    if (x == 2) {
                                                                                                                                                        r = "two_columns"
                                                                                                                                                    } else {
                                                                                                                                                        if (x == 3) {
                                                                                                                                                            r = "three_columns"
                                                                                                                                                        } else {
                                                                                                                                                            if (x == 9) {
                                                                                                                                                                r = "inline_columns"
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    }
                                                                                                                                                }
                                                                                                                                                $("#" + active_element).removeClass("two_columns three_columns inline_columns").addClass(r);
                                                                                                                                                A.choice_columns = x
                                                                                                                                            } else {
                                                                                                                                                if (D == "element_range_min") {
                                                                                                                                                    var M = parseInt(x);
                                                                                                                                                    if (!isNaN(M)) {
                                                                                                                                                        A.range_min = M
                                                                                                                                                    } else {
                                                                                                                                                        A.range_min = 0
                                                                                                                                                    }
                                                                                                                                                    if ((parseInt(A.range_min) + parseInt(A.range_max)) >= 1) {
                                                                                                                                                        $("#prop_range legend").css({
                                                                                                                                                            "background-color": "#DC6666",
                                                                                                                                                            "border-color": "#DC6666"
                                                                                                                                                        });
                                                                                                                                                        $("#prop_range fieldset").css("border-color", "#DC6666")
                                                                                                                                                    } else {
                                                                                                                                                        $("#prop_range legend").css({
                                                                                                                                                            "background-color": "#3D6C10",
                                                                                                                                                            "border-color": "#3D6C10"
                                                                                                                                                        });
                                                                                                                                                        $("#prop_range fieldset").css("border-color", "#3D6C10")
                                                                                                                                                    }
                                                                                                                                                    $("#" + active_element + " div > label").remove();
                                                                                                                                                    var s = "";
                                                                                                                                                    if (A.range_limit_by == "c") {
                                                                                                                                                        s = "characters"
                                                                                                                                                    } else {
                                                                                                                                                        if (A.range_limit_by == "w") {
                                                                                                                                                            s = "words"
                                                                                                                                                        } else {
                                                                                                                                                            if (A.range_limit_by == "d") {
                                                                                                                                                                s = "digits"
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    }
                                                                                                                                                    if (A.range_limit_by == "c" || A.range_limit_by == "w" || A.range_limit_by == "d") {
                                                                                                                                                        if ((A.range_min > 0 && A.range_max > 0) && (A.range_min == A.range_max)) {
                                                                                                                                                            $("#element_" + v).after('<label for="element_' + v + '">Must be <var id="range_min_' + v + '">' + A.range_min + '</var> <var class="range_limit_by">' + s + '</var>.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                        } else {
                                                                                                                                                            if (A.range_min > 0 && A.range_max > 0) {
                                                                                                                                                                $("#element_" + v).after('<label for="element_' + v + '">Must be between <var id="range_min_' + v + '">' + A.range_min + '</var> and <var id="range_max_' + v + '">' + A.range_max + '</var> <var class="range_limit_by">' + s + '</var>.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                            } else {
                                                                                                                                                                if (A.range_max > 0) {
                                                                                                                                                                    $("#element_" + v).after('<label for="element_' + v + '">Maximum of <var id="range_max_' + v + '">' + A.range_max + '</var> <var class="range_limit_by">' + s + '</var> allowed.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                } else {
                                                                                                                                                                    if (A.range_min > 0) {
                                                                                                                                                                        $("#element_" + v).after('<label for="element_' + v + '">Minimum of <var id="range_min_' + v + '">' + A.range_min + '</var> <var class="range_limit_by">' + s + '</var> required.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                    }
                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    } else {
                                                                                                                                                        if (A.range_limit_by == "v") {
                                                                                                                                                            if (A.range_min > 0 && A.range_max > 0) {
                                                                                                                                                                $("#element_" + v).after('<label for="element_' + v + '">Must be a number between <var id="range_min_' + v + '">' + A.range_min + '</var> and <var id="range_max_' + v + '">' + A.range_max + "</var>.</label>")
                                                                                                                                                            } else {
                                                                                                                                                                if (A.range_max > 0) {
                                                                                                                                                                    $("#element_" + v).after('<label for="element_' + v + '">Must be a number less than or equal to <var id="range_max_' + v + '">' + A.range_max + "</var>.</label>")
                                                                                                                                                                } else {
                                                                                                                                                                    if (A.range_min > 0) {
                                                                                                                                                                        $("#element_" + v).after('<label for="element_' + v + '">Must be a number greater than or equal to <var id="range_min_' + v + '">' + A.range_min + "</var>.</label>")
                                                                                                                                                                    }
                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    }
                                                                                                                                                } else {
                                                                                                                                                    if (D == "element_range_max") {
                                                                                                                                                        var p = parseInt(x);
                                                                                                                                                        if (!isNaN(p)) {
                                                                                                                                                            A.range_max = p
                                                                                                                                                        } else {
                                                                                                                                                            A.range_max = 0
                                                                                                                                                        }
                                                                                                                                                        if ((parseInt(A.range_min) + parseInt(A.range_max)) >= 1) {
                                                                                                                                                            $("#prop_range legend").css({
                                                                                                                                                                "background-color": "#DC6666",
                                                                                                                                                                "border-color": "#DC6666"
                                                                                                                                                            });
                                                                                                                                                            $("#prop_range fieldset").css("border-color", "#DC6666")
                                                                                                                                                        } else {
                                                                                                                                                            $("#prop_range legend").css({
                                                                                                                                                                "background-color": "#3D6C10",
                                                                                                                                                                "border-color": "#3D6C10"
                                                                                                                                                            });
                                                                                                                                                            $("#prop_range fieldset").css("border-color", "#3D6C10")
                                                                                                                                                        }
                                                                                                                                                        $("#" + active_element + " div > label").remove();
                                                                                                                                                        var s = "";
                                                                                                                                                        if (A.range_limit_by == "c") {
                                                                                                                                                            s = "characters"
                                                                                                                                                        } else {
                                                                                                                                                            if (A.range_limit_by == "w") {
                                                                                                                                                                s = "words"
                                                                                                                                                            } else {
                                                                                                                                                                if (A.range_limit_by == "d") {
                                                                                                                                                                    s = "digits"
                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                        if (A.range_limit_by == "c" || A.range_limit_by == "w" || A.range_limit_by == "d") {
                                                                                                                                                            if ((A.range_min > 0 && A.range_max > 0) && (A.range_min == A.range_max)) {
                                                                                                                                                                $("#element_" + v).after('<label for="element_' + v + '">Must be <var id="range_min_' + v + '">' + A.range_min + '</var> <var class="range_limit_by">' + s + '</var>.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                            } else {
                                                                                                                                                                if (A.range_min > 0 && A.range_max > 0) {
                                                                                                                                                                    $("#element_" + v).after('<label for="element_' + v + '">Must be between <var id="range_min_' + v + '">' + A.range_min + '</var> and <var id="range_max_' + v + '">' + A.range_max + '</var> <var class="range_limit_by">' + s + '</var>.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                } else {
                                                                                                                                                                    if (A.range_max > 0) {
                                                                                                                                                                        $("#element_" + v).after('<label for="element_' + v + '">Maximum of <var id="range_max_' + v + '">' + A.range_max + '</var> <var class="range_limit_by">' + s + '</var> allowed.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                    } else {
                                                                                                                                                                        if (A.range_min > 0) {
                                                                                                                                                                            $("#element_" + v).after('<label for="element_' + v + '">Minimum of <var id="range_min_' + v + '">' + A.range_min + '</var> <var class="range_limit_by">' + s + '</var> required.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                        }
                                                                                                                                                                    }
                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                        } else {
                                                                                                                                                            if (A.range_limit_by == "v") {
                                                                                                                                                                if (A.range_min > 0 && A.range_max > 0) {
                                                                                                                                                                    $("#element_" + v).after('<label for="element_' + v + '">Must be a number between <var id="range_min_' + v + '">' + A.range_min + '</var> and <var id="range_max_' + v + '">' + A.range_max + "</var>.</label>")
                                                                                                                                                                } else {
                                                                                                                                                                    if (A.range_max > 0) {
                                                                                                                                                                        $("#element_" + v).after('<label for="element_' + v + '">Must be a number less than or equal to <var id="range_max_' + v + '">' + A.range_max + "</var>.</label>")
                                                                                                                                                                    } else {
                                                                                                                                                                        if (A.range_min > 0) {
                                                                                                                                                                            $("#element_" + v).after('<label for="element_' + v + '">Must be a number greater than or equal to <var id="range_min_' + v + '">' + A.range_min + "</var>.</label>")
                                                                                                                                                                        }
                                                                                                                                                                    }
                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    } else {
                                                                                                                                                        if (D == "element_range_limit_by") {
                                                                                                                                                            if (x == "c") {
                                                                                                                                                                $("#" + active_element + " var.range_limit_by").text("characters")
                                                                                                                                                            } else {
                                                                                                                                                                if (x == "w") {
                                                                                                                                                                    $("#" + active_element + " var.range_limit_by").text("words")
                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                            if (A.type == "number") {
                                                                                                                                                                $("#" + active_element + " div > label").remove();
                                                                                                                                                                if (x == "d") {
                                                                                                                                                                    var s = "digits";
                                                                                                                                                                    if ((A.range_min > 0 && A.range_max > 0) && (A.range_min == A.range_max)) {
                                                                                                                                                                        $("#element_" + v).after('<label for="element_' + v + '">Must be <var id="range_min_' + v + '">' + A.range_min + '</var> <var class="range_limit_by">' + s + '</var>.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                    } else {
                                                                                                                                                                        if (A.range_min > 0 && A.range_max > 0) {
                                                                                                                                                                            $("#element_" + v).after('<label for="element_' + v + '">Must be between <var id="range_min_' + v + '">' + A.range_min + '</var> and <var id="range_max_' + v + '">' + A.range_max + '</var> <var class="range_limit_by">' + s + '</var>.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                        } else {
                                                                                                                                                                            if (A.range_max > 0) {
                                                                                                                                                                                $("#element_" + v).after('<label for="element_' + v + '">Maximum of <var id="range_max_' + v + '">' + A.range_max + '</var> <var class="range_limit_by">' + s + '</var> allowed.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                            } else {
                                                                                                                                                                                if (A.range_min > 0) {
                                                                                                                                                                                    $("#element_" + v).after('<label for="element_' + v + '">Minimum of <var id="range_min_' + v + '">' + A.range_min + '</var> <var class="range_limit_by">' + s + '</var> required.&nbsp;&nbsp; <em class="currently_entered">Currently Entered: <var id="currently_entered_' + v + '">0</var> <var class="range_limit_by">' + s + "</var>.</em></label>")
                                                                                                                                                                                }
                                                                                                                                                                            }
                                                                                                                                                                        }
                                                                                                                                                                    }
                                                                                                                                                                } else {
                                                                                                                                                                    if (x == "v") {
                                                                                                                                                                        if (A.range_min > 0 && A.range_max > 0) {
                                                                                                                                                                            $("#element_" + v).after('<label for="element_' + v + '">Must be a number between <var id="range_min_' + v + '">' + A.range_min + '</var> and <var id="range_max_' + v + '">' + A.range_max + "</var>.</label>")
                                                                                                                                                                        } else {
                                                                                                                                                                            if (A.range_max > 0) {
                                                                                                                                                                                $("#element_" + v).after('<label for="element_' + v + '">Must be a number less than or equal to <var id="range_max_' + v + '">' + A.range_max + "</var>.</label>")
                                                                                                                                                                            } else {
                                                                                                                                                                                if (A.range_min > 0) {
                                                                                                                                                                                    $("#element_" + v).after('<label for="element_' + v + '">Must be a number greater than or equal to <var id="range_min_' + v + '">' + A.range_min + "</var>.</label>")
                                                                                                                                                                                }
                                                                                                                                                                            }
                                                                                                                                                                        }
                                                                                                                                                                    }
                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                            A.range_limit_by = x
                                                                                                                                                        } else {
                                                                                                                                                            if (D == "element_size") {
                                                                                                                                                                A.size = x;
                                                                                                                                                                if (h.data("field_properties").type == "section") {
                                                                                                                                                                    $("#" + active_element).removeClass("section_scroll_small section_scroll_medium section_scroll_large").addClass("section_scroll_" + x)
                                                                                                                                                                } else {
                                                                                                                                                                    if (h.data("field_properties").type == "signature") {
                                                                                                                                                                        $("#" + active_element + " .signature_pad").removeClass("small medium large").addClass(x)
                                                                                                                                                                    } else {
                                                                                                                                                                        $("#" + active_element + " :input").removeClass("small medium large").addClass(x)
                                                                                                                                                                    }
                                                                                                                                                                }
                                                                                                                                                            } else {
                                                                                                                                                                if (D == "element_type_change") {
                                                                                                                                                                    var k = A.position;
                                                                                                                                                                    var m = h.html();
                                                                                                                                                                    var I = $("#form_header").data("form_properties").id;
                                                                                                                                                                    var B = $("#" + active_element).data("field_properties");
                                                                                                                                                                    if (h.data("field_properties").is_db_live == 0) {
                                                                                                                                                                        h.html('<img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> changing field type ...').addClass("delete_processing");
                                                                                                                                                                        $.ajax({
                                                                                                                                                                            type: "POST",
                                                                                                                                                                            async: true,
                                                                                                                                                                            url: "/backend.php/forms/deletedraftfield",
                                                                                                                                                                            data: {
                                                                                                                                                                                form_id: I,
                                                                                                                                                                                element_id: h.data("field_properties").id,
                                                                                                                                                                                element_type: h.data("field_properties").type
                                                                                                                                                                            },
                                                                                                                                                                            cache: false,
                                                                                                                                                                            global: true,
                                                                                                                                                                            dataType: "json",
                                                                                                                                                                            error: function(V, T, U) {
                                                                                                                                                                                h.html(m).removeClass("delete_processing")
                                                                                                                                                                            },
                                                                                                                                                                            success: function(T) {
                                                                                                                                                                                if (T.status == "ok") {
                                                                                                                                                                                    B.type = x;
                                                                                                                                                                                    $.ajax({
                                                                                                                                                                                        type: "POST",
                                                                                                                                                                                        async: true,
                                                                                                                                                                                        url: "/backend.php/forms/add_field.php",
                                                                                                                                                                                        data: {
                                                                                                                                                                                            element_type: x,
                                                                                                                                                                                            form_id: I,
                                                                                                                                                                                            holder_id: "li_" + A.id,
                                                                                                                                                                                            position: k,
                                                                                                                                                                                            action: "duplicate",
                                                                                                                                                                                            field_properties: B
                                                                                                                                                                                        },
                                                                                                                                                                                        cache: false,
                                                                                                                                                                                        global: true,
                                                                                                                                                                                        dataType: "json",
                                                                                                                                                                                        error: function(W, U, V) {
                                                                                                                                                                                            h.html(m).removeClass("delete_processing");
                                                                                                                                                                                            $("#dialog-message").dialog("open")
                                                                                                                                                                                        },
                                                                                                                                                                                        success: function(V) {
                                                                                                                                                                                            if (V.status == "ok") {
                                                                                                                                                                                                var U = $(V.markup);
                                                                                                                                                                                                U.data("field_properties", h.data("field_properties"));
                                                                                                                                                                                                U.data("field_properties").id = V.element_id;
                                                                                                                                                                                                U.data("field_properties").type = x;
                                                                                                                                                                                                $("#" + V.holder_id).replaceWith(U);
                                                                                                                                                                                                $("#li_" + V.element_id).hide().fadeIn();
                                                                                                                                                                                                reorganize_page_break();
                                                                                                                                                                                                $("#form_builder_sortable").sortable("refreshPositions");
                                                                                                                                                                                                select_element("#li_" + V.element_id)
                                                                                                                                                                                            } else {
                                                                                                                                                                                                $("#dialog-message").dialog("open");
                                                                                                                                                                                                $("#form_builder_sortable .click_processing").remove()
                                                                                                                                                                                            }
                                                                                                                                                                                        }
                                                                                                                                                                                    })
                                                                                                                                                                                } else {
                                                                                                                                                                                    h.html(m).removeClass("delete_processing");
                                                                                                                                                                                    $("#dialog-message").dialog("open")
                                                                                                                                                                                }
                                                                                                                                                                            }
                                                                                                                                                                        })
                                                                                                                                                                    }
                                                                                                                                                                } else {
                                                                                                                                                                    if (D == "element_default_value") {
                                                                                                                                                                        A.default_value = x;
                                                                                                                                                                        if (A.type == "address") {
                                                                                                                                                                            $("#element_" + A.id + "_6").val(x)
                                                                                                                                                                        } else {
                                                                                                                                                                            if (A.type == "date" || A.type == "europe_date" || A.type == "time") {} else {
                                                                                                                                                                                if (A.type == "phone") {
                                                                                                                                                                                    $("#element_" + A.id + "_1").val($("#element_default_phone1").val());
                                                                                                                                                                                    $("#element_" + A.id + "_2").val($("#element_default_phone2").val());
                                                                                                                                                                                    $("#element_" + A.id + "_3").val($("#element_default_phone3").val())
                                                                                                                                                                                } else {
                                                                                                                                                                                    if (A.type == "money") {
                                                                                                                                                                                        if (A.constraint == "yen") {
                                                                                                                                                                                            $("#" + active_element + " :input").val(x)
                                                                                                                                                                                        } else {
                                                                                                                                                                                            var z = x.split(".");
                                                                                                                                                                                            $("#" + active_element + " :input").eq(0).val(z[0]);
                                                                                                                                                                                            $("#" + active_element + " :input").eq(1).val(z[1])
                                                                                                                                                                                        }
                                                                                                                                                                                    } else {
                                                                                                                                                                                        $("#" + active_element + " :input").val(x)
                                                                                                                                                                                    }
                                                                                                                                                                                }
                                                                                                                                                                            }
                                                                                                                                                                        }
                                                                                                                                                                    } else {
                                                                                                                                                                        if (D == "element_choice_other_label") {
                                                                                                                                                                            A.choice_other_label = x;
                                                                                                                                                                            $("#" + active_element + " label.other").html(x)
                                                                                                                                                                        } else {
                                                                                                                                                                            if (D == "element_choice_has_other") {
                                                                                                                                                                                A.choice_has_other = x;
                                                                                                                                                                                if (x == 1) {
                                                                                                                                                                                    var C = "";
                                                                                                                                                                                    C += '<input id="element_' + A.id + '_0" name="element_' + A.id + '" class="element radio" type="' + A.type + '" value="0" />';
                                                                                                                                                                                    C += '<label class="choice other" for="element_' + A.id + '_0">' + A.choice_other_label + "</label>";
                                                                                                                                                                                    C += '<input type="text" value="" class="element text other" name="element_' + A.id + '_other" id="element_' + A.id + '_other" />';
                                                                                                                                                                                    C = "<span>" + C + "</span>";
                                                                                                                                                                                    $("#" + active_element + " > div").append(C);
                                                                                                                                                                                    $("#" + active_element + " > div > span").last().hide().slideDown();
                                                                                                                                                                                    $("#prop_other_choices_label").prop("disabled", false).select().focus()
                                                                                                                                                                                } else {
                                                                                                                                                                                    $("#" + active_element + " div > span").last().slideUp("slow", function() {
                                                                                                                                                                                        $(this).remove()
                                                                                                                                                                                    });
                                                                                                                                                                                    $("#prop_other_choices_label").prop("disabled", true)
                                                                                                                                                                                }
                                                                                                                                                                            } else {
                                                                                                                                                                                if (D == "element_choice_limit_rule") {
                                                                                                                                                                                    A.choice_limit_rule = x;
                                                                                                                                                                                    $("#prop_choice_limit_range_span,#prop_choice_limit_qty_span").hide();
                                                                                                                                                                                    if (x == "between") {
                                                                                                                                                                                        $("#prop_choice_limit_range_span").show()
                                                                                                                                                                                    } else {
                                                                                                                                                                                        $("#prop_choice_limit_qty_span").show()
                                                                                                                                                                                    }
                                                                                                                                                                                } else {
                                                                                                                                                                                    if (D == "element_choice_limit_qty") {
                                                                                                                                                                                        A.choice_limit_qty = x
                                                                                                                                                                                    } else {
                                                                                                                                                                                        if (D == "element_choice_limit_range_min") {
                                                                                                                                                                                            A.choice_limit_range_min = x
                                                                                                                                                                                        } else {
                                                                                                                                                                                            if (D == "element_choice_limit_range_max") {
                                                                                                                                                                                                A.choice_limit_range_max = x
                                                                                                                                                                                            } else {
                                                                                                                                                                                                if (D == "element_choice_max_entry") {
                                                                                                                                                                                                    A.choice_max_entry = x
                                                                                                                                                                                                } else {
                                                                                                                                                                                                    if (D == "element_text_default_length") {
                                                                                                                                                                                                        A.text_default_length = x;
                                                                                                                                                                                                        rebuild_default_text_random_preview()
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        if (D == "element_text_default_prefix") {
                                                                                                                                                                                                            A.text_default_prefix = x;
                                                                                                                                                                                                            rebuild_default_text_random_preview()
                                                                                                                                                                                                        } else {
                                                                                                                                                                                                            if (D == "element_text_default_random_type") {
                                                                                                                                                                                                                A.text_default_random_type = x;
                                                                                                                                                                                                                rebuild_default_text_random_preview()
                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                if (D == "element_text_default_case") {
                                                                                                                                                                                                                    A.text_default_case = x;
                                                                                                                                                                                                                    rebuild_default_text_random_preview()
                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                    if (D == "constraint") {
                                                                                                                                                                                                                        if (A.type == "money") {
                                                                                                                                                                                                                            var K = "";
                                                                                                                                                                                                                            var P = "";
                                                                                                                                                                                                                            var N = "";
                                                                                                                                                                                                                            if (x == "dollar" || x == "") {
                                                                                                                                                                                                                                K = "&#36;";
                                                                                                                                                                                                                                P = "Dollars";
                                                                                                                                                                                                                                N = "Cents"
                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                if (x == "euro") {
                                                                                                                                                                                                                                    K = "&#8364;";
                                                                                                                                                                                                                                    P = "Euros";
                                                                                                                                                                                                                                    N = "Cents"
                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                    if (x == "pound") {
                                                                                                                                                                                                                                        K = "&#163;";
                                                                                                                                                                                                                                        P = "Pounds";
                                                                                                                                                                                                                                        N = "Pence"
                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                        if (x == "baht") {
                                                                                                                                                                                                                                            K = "&#3647;";
                                                                                                                                                                                                                                            P = "Baht";
                                                                                                                                                                                                                                            N = "Satang"
                                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                                            if (x == "rupees") {
                                                                                                                                                                                                                                                K = "Rs";
                                                                                                                                                                                                                                                P = "Rupees";
                                                                                                                                                                                                                                                N = "Paise"
                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                if (x == "rand") {
                                                                                                                                                                                                                                                    K = "R";
                                                                                                                                                                                                                                                    P = "Rand";
                                                                                                                                                                                                                                                    N = "Cents"
                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                    if (x == "reais") {
                                                                                                                                                                                                                                                        K = "R&#36;";
                                                                                                                                                                                                                                                        P = "Reais";
                                                                                                                                                                                                                                                        N = "Centavos"
                                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                                        if (x == "forint") {
                                                                                                                                                                                                                                                            K = "&#70;&#116;";
                                                                                                                                                                                                                                                            P = "Forint";
                                                                                                                                                                                                                                                            N = "Filler"
                                                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                                                            if (x == "franc") {
                                                                                                                                                                                                                                                                K = "CHF";
                                                                                                                                                                                                                                                                P = "Francs";
                                                                                                                                                                                                                                                                N = "Rappen"
                                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                                if (x == "koruna") {
                                                                                                                                                                                                                                                                    K = "&#75;&#269;";
                                                                                                                                                                                                                                                                    P = "Koruna";
                                                                                                                                                                                                                                                                    N = "Haléřů"
                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                    if (x == "krona") {
                                                                                                                                                                                                                                                                        K = "kr";
                                                                                                                                                                                                                                                                        P = "Kroner";
                                                                                                                                                                                                                                                                        N = "Øre"
                                                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                                                        if (x == "pesos") {
                                                                                                                                                                                                                                                                            K = "&#36;";
                                                                                                                                                                                                                                                                            P = "Pesos";
                                                                                                                                                                                                                                                                            N = "Cents"
                                                                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                                                                            if (x == "ringgit") {
                                                                                                                                                                                                                                                                                K = "RM";
                                                                                                                                                                                                                                                                                P = "Ringgit";
                                                                                                                                                                                                                                                                                N = "Sen"
                                                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                                                if (x == "zloty") {
                                                                                                                                                                                                                                                                                    K = "&#122;&#322;";
                                                                                                                                                                                                                                                                                    P = "Złoty";
                                                                                                                                                                                                                                                                                    N = "Grosz"
                                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                                    if (x == "riyals") {
                                                                                                                                                                                                                                                                                        K = "&#65020;";
                                                                                                                                                                                                                                                                                        P = "Riyals";
                                                                                                                                                                                                                                                                                        N = "Halalah"
                                                                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                                                                        if (x == "yen") {
                                                                                                                                                                                                                                                                                            K = "&#165;";
                                                                                                                                                                                                                                                                                            P = "Yen"
                                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                                        else {
                                                                                                                                                                                                                                                                                          if (x == "kes") {
                                                                                                                                                                                                                                                                                            K = "KES";
                                                                                                                                                                                                                                                                                            P = "Shilling"
                                                                                                                                                                                                                                                                                          }
                                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                            }
                                                                                                                                                                                                                            $("#" + active_element + " > span.symbol").html(K);
                                                                                                                                                                                                                            if (x == "yen" && A.constraint != "yen") {
                                                                                                                                                                                                                                $("#" + active_element + " > span").last().remove();
                                                                                                                                                                                                                                $("#" + active_element + " > span").last().contents().filter(function() {
                                                                                                                                                                                                                                    return this.nodeType == 3
                                                                                                                                                                                                                                }).remove();
                                                                                                                                                                                                                                $("#element_" + A.id + "_1").attr("size", 15);
                                                                                                                                                                                                                                $("#" + active_element + " > span label").first().text(P)
                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                if (x != "yen" && A.constraint == "yen") {
                                                                                                                                                                                                                                    $("#element_" + A.id + "_1").attr("size", 10);
                                                                                                                                                                                                                                    $("#" + active_element + " > span").first().next().children().first().after(" . ");
                                                                                                                                                                                                                                    $("#" + active_element).append('<span><input type="text" size="2" class="text" readonly="readonly"><label>' + N + "</label></span>");
                                                                                                                                                                                                                                    $("#" + active_element + " > span label").first().text(P)
                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                    if (x != "yen" && A.constraint != "yen") {
                                                                                                                                                                                                                                        $("#" + active_element + " > span label").first().text(P).end().last().text(N)
                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                            }
                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                            if (A.type == "text") {
                                                                                                                                                                                                                                var E = "";
                                                                                                                                                                                                                                if (x == "password") {
                                                                                                                                                                                                                                    E = "password"
                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                    E = "text"
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                var o = '<input type="' + E + '" title="" value="' + $("#element_" + A.id).val() + '" class="element text ' + A.size + '" name="element_' + A.id + '" id="element_' + A.id + '">';
                                                                                                                                                                                                                                $("#element_" + A.id).remove();
                                                                                                                                                                                                                                $("#" + active_element + " > div").append(o)
                                                                                                                                                                                                                            }
                                                                                                                                                                                                                        }
                                                                                                                                                                                                                        A.constraint = x
                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                        if (D == "element_type") {
                                                                                                                                                                                                                            if (A.is_db_live != 1 && (x == "simple_name" || x == "name" || x == "simple_name_wmiddle" || x == "name_wmiddle")) {
                                                                                                                                                                                                                                $("#" + active_element + " > span:not(.description)").remove();
                                                                                                                                                                                                                                var d = "";
                                                                                                                                                                                                                                var F = "";
                                                                                                                                                                                                                                var i = "";
                                                                                                                                                                                                                                var R = "";
                                                                                                                                                                                                                                var f = "";
                                                                                                                                                                                                                                if (x == "simple_name") {
                                                                                                                                                                                                                                    F = '<span><input value="" size="8" maxlength="255" class="element text" name="element_' + A.id + '_1" id="element_' + A.id + '_1"><label>First</label></span>';
                                                                                                                                                                                                                                    R = '<span><input value="" size="14" maxlength="255" class="element text" name="element_' + A.id + '_2" id="element_' + A.id + '_2"><label>Last</label></span>';
                                                                                                                                                                                                                                    $("#" + active_element + " > span.description").after(F + R)
                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                    if (x == "name") {
                                                                                                                                                                                                                                        d = '<span><input value="" size="2" maxlength="255" class="element text" name="element_' + A.id + '_1" id="element_' + A.id + '_1"><label>Title</label></span>';
                                                                                                                                                                                                                                        F = '<span><input value="" size="8" maxlength="255" class="element text" name="element_' + A.id + '_2" id="element_' + A.id + '_2"><label>First</label></span>';
                                                                                                                                                                                                                                        R = '<span><input value="" size="14" maxlength="255" class="element text" name="element_' + A.id + '_3" id="element_' + A.id + '_3"><label>Last</label></span>';
                                                                                                                                                                                                                                        f = '<span><input value="" size="3" maxlength="255" class="element text" name="element_' + A.id + '_4" id="element_' + A.id + '_4"><label>Suffix</label></span>';
                                                                                                                                                                                                                                        $("#" + active_element + " > span.description").after(d + F + R + f)
                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                        if (x == "simple_name_wmiddle") {
                                                                                                                                                                                                                                            F = '<span><input value="" size="8" maxlength="255" class="element text" name="element_' + A.id + '_1" id="element_' + A.id + '_1"><label>First</label></span>';
                                                                                                                                                                                                                                            i = '<span><input value="" size="8" maxlength="255" class="element text" name="element_' + A.id + '_2" id="element_' + A.id + '_2"><label>Middle</label></span>';
                                                                                                                                                                                                                                            R = '<span><input value="" size="14" maxlength="255" class="element text" name="element_' + A.id + '_3" id="element_' + A.id + '_3"><label>Last</label></span>';
                                                                                                                                                                                                                                            $("#" + active_element + " > span.description").after(F + i + R)
                                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                                            if (x == "name_wmiddle") {
                                                                                                                                                                                                                                                d = '<span class="namewm_ext"><input value="" maxlength="255" class="element text large" name="element_' + A.id + '_1" id="element_' + A.id + '_1"><label>Title</label></span>';
                                                                                                                                                                                                                                                F = '<span class="namewm_first"><input value="" maxlength="255" class="element text large" name="element_' + A.id + '_2" id="element_' + A.id + '_2"><label>First</label></span>';
                                                                                                                                                                                                                                                i = '<span class="namewm_middle"><input value="" maxlength="255" class="element text large" name="element_' + A.id + '_3" id="element_' + A.id + '_3"><label>Middle</label></span>';
                                                                                                                                                                                                                                                R = '<span class="namewm_last"><input value="" maxlength="255" class="element text large" name="element_' + A.id + '_4" id="element_' + A.id + '_4"><label>Last</label></span>';
                                                                                                                                                                                                                                                f = '<span class="namewm_ext"><input value="" maxlength="255" class="element text large" name="element_' + A.id + '_5" id="element_' + A.id + '_5"><label>Suffix</label></span>';
                                                                                                                                                                                                                                                $("#" + active_element + " > span.description").after(d + F + i + R + f)
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                            }
                                                                                                                                                                                                                            if (x == "date") {
                                                                                                                                                                                                                                $("#" + active_element + " > span > label").eq(0).text("MM").end().eq(1).text("DD");
                                                                                                                                                                                                                                $("#element_" + A.id + "_1").val("");
                                                                                                                                                                                                                                $("#element_" + A.id + "_2").val("");
                                                                                                                                                                                                                                $("#element_" + A.id + "_3").val("");
                                                                                                                                                                                                                                $("#element_default_date").val("");
                                                                                                                                                                                                                                A.default_value = ""
                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                if (x == "europe_date") {
                                                                                                                                                                                                                                    $("#" + active_element + " > span > label").eq(0).text("DD").end().eq(1).text("MM");
                                                                                                                                                                                                                                    $("#element_" + A.id + "_1").val("");
                                                                                                                                                                                                                                    $("#element_" + A.id + "_2").val("");
                                                                                                                                                                                                                                    $("#element_" + A.id + "_3").val("");
                                                                                                                                                                                                                                    $("#element_default_date").val("");
                                                                                                                                                                                                                                    A.default_value = ""
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                            }
                                                                                                                                                                                                                            if (x == "simple_phone") {
                                                                                                                                                                                                                                $("#" + active_element + " > span:not(.description)").remove();
                                                                                                                                                                                                                                var y = '<div><input id="element_' + A.id + '" name="element_' + A.id + '" class="element text medium" type="text" maxlength="255" value=""/></div>';
                                                                                                                                                                                                                                $("#" + active_element).append(y);
                                                                                                                                                                                                                                $("#element_default_value").val("");
                                                                                                                                                                                                                                A.default_value = "";
                                                                                                                                                                                                                                $("#prop_default_phone").hide();
                                                                                                                                                                                                                                $("#prop_default_value").show()
                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                if (x == "phone") {
                                                                                                                                                                                                                                    var u = "element_" + A.id + "_1";
                                                                                                                                                                                                                                    var t = "element_" + A.id + "_2";
                                                                                                                                                                                                                                    var q = "element_" + A.id + "_3";
                                                                                                                                                                                                                                    var e = '<span><input id="' + u + '" name="' + u + '" class="element text" size="3" maxlength="3" value="" type="text" /> -<label for="' + u + '">###</label></span><span><input id="' + t + '" name="' + t + '" class="element text" size="3" maxlength="3" value="" type="text" /> -<label for="' + t + '">###</label></span><span><input id="' + q + '" name="' + q + '" class="element text" size="4" maxlength="4" value="" type="text" /><label for="' + q + '">####</label></span>';
                                                                                                                                                                                                                                    $("#" + active_element + " > div").remove();
                                                                                                                                                                                                                                    $("#" + active_element).append(e);
                                                                                                                                                                                                                                    A.default_value = "";
                                                                                                                                                                                                                                    $("#element_default_phone1").val("");
                                                                                                                                                                                                                                    $("#element_default_phone2").val("");
                                                                                                                                                                                                                                    $("#element_default_phone3").val("");
                                                                                                                                                                                                                                    $("#prop_default_value").hide();
                                                                                                                                                                                                                                    $("#prop_default_phone").show()
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                            }
                                                                                                                                                                                                                            A.type = x
                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                            if (D == "element_time_showsecond") {
                                                                                                                                                                                                                                if (x == 1) {
                                                                                                                                                                                                                                    var J = '<span><input type="text" size="2" class="text" readonly="readonly"> : <label>MM</label></span><span><input type="text" size="2" class="text" readonly="readonly"><label>SS</label></span>';
                                                                                                                                                                                                                                    $("#" + active_element + " > span:not('.description')").first().next().replaceWith(J)
                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                    var H = '<span><input type="text" size="2" class="text" readonly="readonly"><label>MM</label></span>';
                                                                                                                                                                                                                                    $("#" + active_element + " > span:not('.description')").first().next().next().remove().end().prev().next().replaceWith(H)
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                A.time_showsecond = x
                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                if (D == "element_time_24hour") {
                                                                                                                                                                                                                                    if (x == 1) {
                                                                                                                                                                                                                                        $("#" + active_element + " > span").last().remove()
                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                        var Q = '<span><select name="element_' + A.id + '_4" id="element_' + A.id + '_4" style="width: 4em;" class="element select"><option value="AM">AM</option><option value="PM">PM</option></select><label>AM/PM</label></span>';
                                                                                                                                                                                                                                        $("#" + active_element).append(Q)
                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                    A.time_24hour = x
                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                    if (D == "submit_use_image") {
                                                                                                                                                                                                                                        $("#" + active_element + " input").removeClass("hide");
                                                                                                                                                                                                                                        if (x == 1) {
                                                                                                                                                                                                                                            $("#" + active_element + " input.btn_submit").addClass("hide");
                                                                                                                                                                                                                                            if (A.submit_primary_img == "" || A.submit_primary_img == null) {
                                                                                                                                                                                                                                                $("#submit_primary_img").val("http://")
                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                $("#submit_primary_img").val(A.submit_primary_img)
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            if (A.submit_secondary_img == "" || A.submit_secondary_img == null) {
                                                                                                                                                                                                                                                $("#submit_secondary_img").val("http://")
                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                $("#submit_secondary_img").val(A.submit_secondary_img)
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            if ($("#" + active_element).hasClass("firstpage")) {
                                                                                                                                                                                                                                                $("#lbl_submit_secondary_img").hide();
                                                                                                                                                                                                                                                $("#submit_secondary_img").hide()
                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                $("#lbl_submit_secondary_img").show();
                                                                                                                                                                                                                                                $("#submit_secondary_img").show()
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            $("#div_submit_use_text").hide();
                                                                                                                                                                                                                                            $("#div_submit_use_image").show()
                                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                                            $("#" + active_element + " input.img_submit").addClass("hide");
                                                                                                                                                                                                                                            $("#submit_primary_text").val(A.submit_primary_text);
                                                                                                                                                                                                                                            $("#submit_secondary_text").val(A.submit_secondary_text);
                                                                                                                                                                                                                                            if ($("#" + active_element).hasClass("firstpage")) {
                                                                                                                                                                                                                                                $("#lbl_submit_secondary_text").hide();
                                                                                                                                                                                                                                                $("#submit_secondary_text").hide()
                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                $("#lbl_submit_secondary_text").show();
                                                                                                                                                                                                                                                $("#submit_secondary_text").show()
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            $("#div_submit_use_image").hide();
                                                                                                                                                                                                                                            $("#div_submit_use_text").show()
                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                        A.submit_use_image = x
                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                        if (D == "submit_primary_text") {
                                                                                                                                                                                                                                            $("#btn_submit_" + A.id).val(x);
                                                                                                                                                                                                                                            A.submit_primary_text = x
                                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                                            if (D == "submit_secondary_text") {
                                                                                                                                                                                                                                                $("#btn_prev_" + A.id).val(x);
                                                                                                                                                                                                                                                A.submit_secondary_text = x
                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                if (D == "submit_primary_img") {
                                                                                                                                                                                                                                                    $("#img_submit_" + A.id).attr("src", x);
                                                                                                                                                                                                                                                    A.submit_primary_img = x
                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                    if (D == "submit_secondary_img") {
                                                                                                                                                                                                                                                        $("#img_prev_" + A.id).attr("src", x);
                                                                                                                                                                                                                                                        A.submit_secondary_img = x
                                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                                        if (D == "element_matrix_allow_multiselect") {
                                                                                                                                                                                                                                                            var j = "";
                                                                                                                                                                                                                                                            var c = "";
                                                                                                                                                                                                                                                            if (x == 1) {
                                                                                                                                                                                                                                                                j = "checkbox";
                                                                                                                                                                                                                                                                c = "radio"
                                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                                j = "radio";
                                                                                                                                                                                                                                                                c = "checkbox"
                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                            var n = "";
                                                                                                                                                                                                                                                            var w = "";
                                                                                                                                                                                                                                                            var L = "";
                                                                                                                                                                                                                                                            $("#" + active_element + " table tbody").find("tr").each(function() {
                                                                                                                                                                                                                                                                $(this).find("td input[type=" + c + "]").each(function() {
                                                                                                                                                                                                                                                                    n = $(this).attr("id");
                                                                                                                                                                                                                                                                    w = $(this).attr("name");
                                                                                                                                                                                                                                                                    L = $(this).val();
                                                                                                                                                                                                                                                                    $(this).replaceWith($('<input type="' + j + '" id="' + n + '" name="' + w + '" value="' + L + '" />'))
                                                                                                                                                                                                                                                                })
                                                                                                                                                                                                                                                            });
                                                                                                                                                                                                                                                            A.matrix_allow_multiselect = x
                                                                                                                                                                                                                                                        } else {
                                                                                                                                                                                                                                                            if (D == "element_address_hideline2") {
                                                                                                                                                                                                                                                                if (x == 1) {
                                                                                                                                                                                                                                                                    $("#" + active_element + "_span_2").slideUp()
                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                    $("#" + active_element + "_span_2").slideDown()
                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                A.address_hideline2 = x
                                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                                if (D == "element_address_us_only") {
                                                                                                                                                                                                                                                                    if (x == 1) {
                                                                                                                                                                                                                                                                        $("#element_" + A.id + "_6").val("United States").prop("disabled", true);
                                                                                                                                                                                                                                                                        $("#element_countries").val("United States").prop("disabled", true);
                                                                                                                                                                                                                                                                        var g = '<select class="element select large" id="element_' + A.id + '_4" name="element_' + A.id + '_4"><option value="">Select a State</option><option value="Alabama" >Alabama</option><option value="Alaska" >Alaska</option><option value="Arizona" >Arizona</option><option value="Arkansas" >Arkansas</option><option value="California" >California</option><option value="Colorado" >Colorado</option><option value="Connecticut" >Connecticut</option><option value="Delaware" >Delaware</option><option value="Delaware" >District of Columbia</option><option value="Florida" >Florida</option><option value="Georgia" >Georgia</option><option value="Hawaii" >Hawaii</option><option value="Idaho" >Idaho</option><option value="Illinois" >Illinois</option><option value="Indiana" >Indiana</option><option value="Iowa" >Iowa</option><option value="Kansas" >Kansas</option><option value="Kentucky" >Kentucky</option><option value="Louisiana" >Louisiana</option><option value="Maine" >Maine</option><option value="Maryland" >Maryland</option><option value="Massachusetts" >Massachusetts</option><option value="Michigan" >Michigan</option><option value="Minnesota" >Minnesota</option><option value="Mississippi" >Mississippi</option><option value="Missouri" >Missouri</option><option value="Montana" >Montana</option><option value="Nebraska" >Nebraska</option><option value="Nevada" >Nevada</option><option value="New Hampshire" >New Hampshire</option><option value="New Jersey" >New Jersey</option><option value="New Mexico" >New Mexico</option><option value="New York" >New York</option><option value="North Carolina" >North Carolina</option><option value="North Dakota" >North Dakota</option><option value="Ohio" >Ohio</option><option value="Oklahoma" >Oklahoma</option><option value="Oregon" >Oregon</option><option value="Pennsylvania" >Pennsylvania</option><option value="Rhode Island" >Rhode Island</option><option value="South Carolina" >South Carolina</option><option value="South Dakota" >South Dakota</option><option value="Tennessee" >Tennessee</option><option value="Texas" >Texas</option><option value="Utah" >Utah</option><option value="Vermont" >Vermont</option><option value="Virginia" >Virginia</option><option value="Washington" >Washington</option><option value="West Virginia" >West Virginia</option><option value="Wisconsin" >Wisconsin</option><option value="Wyoming" >Wyoming</option></select>';
                                                                                                                                                                                                                                                                        $("#element_" + A.id + "_4").replaceWith(g)
                                                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                                                        $("#element_" + A.id + "_6").val("United States").prop("disabled", false);
                                                                                                                                                                                                                                                                        $("#element_countries").val("United States").prop("disabled", false);
                                                                                                                                                                                                                                                                        var b = '<input type="text" value="" class="element text large" name="element_' + A.id + '_4" id="element_' + A.id + '_4">';
                                                                                                                                                                                                                                                                        $("#element_" + A.id + "_4").replaceWith(b)
                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                    A.address_us_only = x;
                                                                                                                                                                                                                                                                    A.default_value = "United States"
                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                            }
                                                                                                                                                                                                                        }
                                                                                                                                                                                                                    }
                                                                                                                                                                                                                }
                                                                                                                                                                                                            }
                                                                                                                                                                                                        }
                                                                                                                                                                                                    }
                                                                                                                                                                                                }
                                                                                                                                                                                            }
                                                                                                                                                                                        }
                                                                                                                                                                                    }
                                                                                                                                                                                }
                                                                                                                                                                            }
                                                                                                                                                                        }
                                                                                                                                                                    }
                                                                                                                                                                }
                                                                                                                                                            }
                                                                                                                                                        }
                                                                                                                                                    }
                                                                                                                                                }
                                                                                                                                            }
                                                                                                                                        }
                                                                                                                                    }
                                                                                                                                }
                                                                                                                            }
                                                                                                                        }
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

function add_element(a) {
    var c = $("#form_header").data("form_properties").id;
    if ($("#li_dummy").length) {
        $("#li_dummy").remove();
        $("#no_fields_notice").remove()
    }
    var b = $("#form_builder_sortable > li").not(".li_pagination").size() + 1;
    var d = b;
    if ($("#li_lastpage").length == 0) {
        $('<li id="li_temp_' + d + '" class="click_processing"><img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> creating field ...</li>').appendTo("#form_builder_sortable").hide().fadeIn()
    } else {
        $('<li id="li_temp_' + d + '" class="click_processing"><img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> creating field ...</li>').insertBefore("#li_lastpage").hide().fadeIn()
    }
    $.ajax({
        type: "POST",
        async: true,
        url: "/backend.php/forms/addfield",
        data: {
            element_type: a,
            form_id: c,
            holder_id: "li_temp_" + d,
            position: b,
            action: "click_new"
        },
        cache: false,
        global: true,
        dataType: "json",
        error: function(h, f, g) {
            $("#form_builder_sortable .click_processing").remove()
        },
        success: function(f) {
            if (f.status == "ok") {
                var e = $(f.markup);
                e.data("field_properties", f.field_properties).addClass("synched");
                $("#" + f.holder_id).replaceWith(e);
                $("#li_" + f.element_id).hide().fadeIn();
                reorganize_page_break();
                $("#form_builder_sortable").sortable("refreshPositions")
            } else {
                $("#dialog-message").dialog("open");
                $("#form_builder_sortable .click_processing").remove()
            }
        }
    })
}

function add_matrix_row(d) {
    var h = $("#form_header").data("form_properties").id;
    var j = $("#" + active_element);
    var f = $("#form_builder_sortable > li").not(".li_pagination").size() + 1;
    var a = f + $("#form_builder_sortable li.matrix table tr").length;
    var e = d.attr("id").split("_");
    var b = e[1];
    var i = $("#" + active_element + " table th").length;
    var g = '<tr id="mr_temp_' + a + '" class="matrix_row_processing"><td colspan="' + i + '"><img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> creating new row ...</td></tr>';
    var k = $("#element_matrix_row li").index(d.parent()) + 1;
    $("#" + active_element + " table tr:eq(" + k + ")").after(g);
    var c = '<li id="li_mr_temp_' + a + '" class="li_mr_temp_holder"><input type="text" disabled="disabled" value="creating new row ..." autocomplete="off" class="text" id="matrixrow_' + a + '" /> <img class="add_choice" title="Add" alt="Add" src="/form_builder/images/icons/add.png" style="vertical-align: middle" id="matrixrowadd_' + a + '"><img class="del_choice" title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png" style="vertical-align: middle" id="matrixrowdel_' + a + '"></li>';
    d.parent().after(c);
    $.ajax({
        type: "POST",
        async: true,
        url: "/backend.php/forms/addmatrixrow",
        data: {
            form_id: h,
            row_holder_id: "mr_temp_" + a,
            prop_holder_id: "li_mr_temp_" + a,
            allow_multiselect: j.data("field_properties").matrix_allow_multiselect,
            matrix_parent_id: j.data("field_properties").id,
            position: k + 1,
            column_data: j.data("field_properties").options[j.data("field_properties").id].column_data,
            total_column: i - 1
        },
        cache: false,
        global: true,
        dataType: "json",
        error: function(n, l, m) {
            $("#mr_temp_" + a).remove();
            $("#li_mr_temp_" + a).remove()
        },
        success: function(o) {
            if (o.status == "ok") {
                var m = o.element_id;
                var l = j.data("field_properties").options;
                l[m] = o.new_row_data;
                j.data("field_properties").options = l;
                $("#" + o.row_holder_id).replaceWith(o.new_row_markup);
                $("#" + o.prop_holder_id).replaceWith(o.new_prop_markup);
                $("#matrixrow_" + m).focus();
                var n = $("#matrixrow_" + m).parent().nextAll().children().filter("input.text");
                $.each(n, function(p, r) {
                    var q = null;
                    q = $(r).attr("id").split("_");
                    j.data("field_properties").options[q[1]].position++
                })
            } else {
                $("#dialog-message").dialog("open");
                $("#mr_temp_" + a).remove();
                $("#li_mr_temp_" + a).remove()
            }
        }
    })
}

function delete_matrix_row(g) {
    var f = g.attr("id").split("_");
    var a = f[1];
    var b = $("#" + active_element);
    var c = $("#form_header").data("form_properties").id;
    if ($("#element_matrix_row > li").size() <= 1) {
        $("#ui-dialog-title-dialog-warning").html("Unable to delete this row!");
        $("#dialog-warning-msg").html("You cannot delete all rows! <br />At least 1 row is required.");
        $("#dialog-warning").dialog("open");
        return false
    }
    var e = $("#" + active_element + " table th").length;
    var d = $("#mr_" + a).html();
    $("#mr_" + a).addClass("matrix_row_processing").html('<td colspan="' + e + '"><img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> deleting row ...</td>');
    $("#matrixrow_" + a).prop("disabled", true);
    $("#matrixrowadd_" + a).fadeOut("fast");
    $("#matrixrowdel_" + a).fadeOut("fast");
    $.ajax({
        type: "POST",
        async: true,
        url: "/backend.php/forms/deletematrixrow",
        data: {
            form_id: c,
            element_id: a
        },
        cache: false,
        global: true,
        dataType: "json",
        error: function(j, h, i) {
            $("#mr_" + a).removeClass("matrix_row_processing").html(d);
            $("#matrixrow_" + a).prop("disabled", false);
            $("#matrixrowadd_" + a).show();
            $("#matrixrowdel_" + a).show()
        },
        success: function(l) {
            if (l.status == "ok") {
                var i = l.element_id;
                var j = $("#" + active_element);
                var h = j.data("field_properties").options;
                delete h[i];
                j.data("field_properties").options = h;
                var k = $("#matrixrow_" + i).parent().nextAll().children().filter("input.text");
                $.each(k, function(m, o) {
                    var n = null;
                    n = $(o).attr("id").split("_");
                    j.data("field_properties").options[n[1]].position--
                });
                $("#matrixrow_" + i).parent().fadeOut("normal", function() {
                    $(this).remove()
                });
                $("#mr_" + i).fadeOut("normal", function() {
                    $(this).remove();
                    if ($("#" + active_element + " tbody tr:gt(0)").length > 0) {
                        var n = $("#" + active_element + " tbody tr:gt(0)");
                        var m = new Array();
                        n.each(function(p) {
                            var q = null;
                            q = $(this).attr("id").split("_");
                            m[p] = q[1]
                        });
                        var o = m.join(",");
                        j.data("field_properties").constraint = o
                    } else {
                        j.data("field_properties").constraint = ""
                    }
                })
            } else {
                $("#dialog-message").dialog("open");
                $("#mr_" + i).removeClass("matrix_row_processing").html(d);
                $("#matrixrow_" + i).prop("disabled", false);
                $("#matrixrowadd_" + i).show();
                $("#matrixrowdel_" + i).show()
            }
        }
    })
}

function add_matrix_column(a) {
    var h = a.attr("id").split("_");
    var m = h[1];
    var q = $("#" + active_element);
    var c = q.data("field_properties").id;
    var l = parseInt(q.data("field_properties").last_option_id) + 1;
    q.data("field_properties").last_option_id = l;
    var k = $("#mc_" + c + "_" + m).index();
    $("#mc_" + c + "_" + m).after('<th scope="col" id="mc_' + c + "_" + l + '">&nbsp;</th>');
    var n = "";
    if (q.data("field_properties").matrix_allow_multiselect == 1) {
        n = "checkbox"
    } else {
        n = "radio"
    }
    var p = new Array();
    var d = "";
    $("#" + active_element + " table tbody").find("tr").each(function() {
        p = $(this).attr("id").split("_");
        d = p[1];
        $(this).find("td").eq(k).after('<td><input type="' + n + '" value="' + l + '" name="element_' + d + '" id="element_' + d + "_" + l + '"></td>')
    });
    var i = $("#" + active_element + " table th").length;
    var o = 100 / i;
    var e = 2 * o;
    e = Math.round(e);
    var g = (100 - e) / (i - 1);
    g = Math.round(g);
    $("#" + active_element + " table th:eq(0)").css("width", e + "%");
    $("#" + active_element + " table th:gt(0)").css("width", g + "%");
    var f = '<li><input type="text" value="" autocomplete="off" class="text" id="matrixcol_' + l + '" /> <img class="add_choice" title="Add" alt="Add" src="/form_builder/images/icons/add.png" style="vertical-align: middle" id="matrixcoladd_' + l + '"><img class="del_choice" title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png" style="vertical-align: middle" id="matrixcoldel_' + l + '"></li>';
    a.parent().after(f);
    $("#matrixcol_" + l).focus();
    var j = q.data("field_properties").options[c].column_data;
    var b = k + 1;
    j[l] = {
        column_title: "",
        is_db_live: 0,
        position: b
    };
    q.data("field_properties").options[c].column_data = j;
    var r = $("#matrixcol_" + l).parent().nextAll().children().filter("input.text");
    $.each(r, function(s, u) {
        var t = null;
        t = $(u).attr("id").split("_");
        q.data("field_properties").options[c].column_data[t[1]].position++
    })
}

function delete_matrix_column(h) {
    var g = h.attr("id").split("_");
    var e = g[1];
    var c = $("#" + active_element);
    var a = c.data("field_properties").id;
    if ($("#element_matrix_column > li").size() <= 1) {
        $("#ui-dialog-title-dialog-warning").html("Unable to delete this column!");
        $("#dialog-warning-msg").html("You cannot delete all columns! <br />At least 1 column is required.");
        $("#dialog-warning").dialog("open");
        return false
    }
    var d = c.data("field_properties").options[a].column_data;
    delete d[e];
    c.data("field_properties").options[a].column_data = d;
    var f = $("#matrixcol_" + e).parent().nextAll().children().filter("input.text");
    $.each(f, function(i, k) {
        var j = null;
        j = $(k).attr("id").split("_");
        c.data("field_properties").options[a].column_data[j[1]].position--
    });
    $("#matrixcol_" + e).parent().remove();
    var b = $("#mc_" + a + "_" + e).index();
    $("#mc_" + a + "_" + e).remove();
    $("#" + active_element + " table tbody").find("tr").each(function() {
        $(this).find("td").eq(b).remove()
    })
}

function set_form_properties(l, a) {
    var d = $("#form_header").data("form_properties");
    if (l == "form_title") {
        $("#form_header_title").html(a.replace(/\n/g, "<br />"));
        d.name = a
    } else {
        if (l == "form_description") {
            $("#form_header_desc").html(a.replace(/\n/g, "<br />"));
            d.description = a
        } else {
            if(l == "form_idn")
        {
            d.idn = a
        }
        else
        {
        if(l == "form_code")
        {
            d.code = a
        }
        else
        {
        if(l == "form_stage")
        {
            d.stage = a
        }
        else
        {
        if(l == "form_group")
        {
            d.group = a
        }
        else
        {
        if(l == "form_department")
        {
            d.department = a
        }
        else
        {
        if(l == "form_department_stage")
        {
            d.department_stage = a
        }
        else
        {
         if(l == "form_type")
         {
            d.type = a
         }
         else
         {
            if (l == "form_review_title") {
                d.review_title = a
            } else {
                if (l == "form_review_description") {
                    d.review_description = a
                } else {
                    if (l == "form_resume_subject") {
                        d.resume_subject = a
                    } else {
                        if (l == "form_resume_content") {
                            d.resume_content = a
                        } else {
                            if (l == "form_resume_from_name") {
                                d.resume_from_name = a
                            } else {
                                if (l == "form_resume_from_email_address") {
                                    d.resume_from_email_address = a
                                } else {
                                    if (l == "form_label_alignment") {
                                        $("#form_builder_sortable > li").not(".li_pagination").css("width", "");
                                        $("#form_builder_preview").removeClass("top_label right_label left_label").addClass(a);
                                        d.label_alignment = a
                                    } else {
                                        if (l == "form_language") {
                                            d.language = a
                                        } else {
                                            if (l == "form_redirect_enable") {
                                                if (a == 1) {
                                                    $("#form_success_message").hide();
                                                    $("#form_redirect_url").show().focus().select()
                                                } else {
                                                    $("#form_success_message").show().focus().select();
                                                    $("#form_redirect_url").hide()
                                                }
                                                d.redirect_enable = a
                                            } else {
                                                if (l == "form_review_use_image") {
                                                    if (a == 1) {
                                                        $("#div_review_use_image").show();
                                                        $("#div_review_use_text").hide()
                                                    } else {
                                                        $("#div_review_use_image").hide();
                                                        $("#div_review_use_text").show()
                                                    }
                                                    d.review_use_image = a
                                                } else {
                                                    if (l == "form_success_message") {
                                                        d.success_message = a
                                                    } else {
                                                        if (l == "form_redirect") {
                                                            d.redirect = a
                                                        } else {
                                                            if (l == "form_custom_script_url") {
                                                                d.custom_script_url = a
                                                            } else {
                                                                if (l == "form_review") {
                                                                    d.review = a;
                                                                    if (($("#li_lastpage").length == 1) && (a == 1)) {
                                                                        if ($("#li_lastpage").data("field_properties").submit_primary_text == "Submit") {
                                                                            $("#li_lastpage").data("field_properties").submit_primary_text = "Continue";
                                                                            $("#btn_submit_lastpage").val("Continue")
                                                                        }
                                                                    } else {
                                                                        if (($("#li_lastpage").length == 1) && (a == 0)) {
                                                                            if ($("#li_lastpage").data("field_properties").submit_primary_text == "Continue") {
                                                                                $("#li_lastpage").data("field_properties").submit_primary_text = "Submit";
                                                                                $("#btn_submit_lastpage").val("Submit")
                                                                            }
                                                                        }
                                                                    }
                                                                    if (a == 1) {
                                                                        $("#form_prop_review").slideDown("slow", function() {
                                                                            adjust_main_height();
                                                                            $("#form_prop_review").show()
                                                                        })
                                                                    } else {
                                                                        $("#form_prop_review").slideUp("slow", function() {
                                                                            adjust_main_height()
                                                                        })
                                                                    }
                                                                } else {
                                                                    if (l == "review_primary_text") {
                                                                        d.review_primary_text = a
                                                                    } else {
                                                                        if (l == "review_primary_img") {
                                                                            d.review_primary_img = a
                                                                        } else {
                                                                            if (l == "review_secondary_text") {
                                                                                d.review_secondary_text = a
                                                                            } else {
                                                                                if (l == "review_secondary_img") {
                                                                                    d.review_secondary_img = a
                                                                                } else {
                                                                                    if (l == "form_resume_enable") {
                                                                                        d.resume_enable = a;
                                                                                        if (a == 1) {
                                                                                            $("#form_prop_resume").slideDown("slow", function() {
                                                                                                adjust_main_height();
                                                                                                $("#form_prop_resume").show()
                                                                                            })
                                                                                        } else {
                                                                                            $("#form_prop_resume").slideUp("slow", function() {
                                                                                                adjust_main_height()
                                                                                            })
                                                                                        }
                                                                                    } else {
                                                                                        if (l == "form_password") {
                                                                                            d.password = a
                                                                                        } else {
                                                                                            if (l == "form_limit") {
                                                                                                d.limit = parseInt(a)
                                                                                            } else {
                                                                                                if (l == "form_unique_ip_maxcount") {
                                                                                                    d.unique_ip_maxcount = parseInt(a)
                                                                                                } else {
                                                                                                    if (l == "form_pagination_type") {
                                                                                                        var j = $("#form_builder_sortable > li.page_break");
                                                                                                        var e = j.length;
                                                                                                        var g = 1;
                                                                                                        var b = "";
                                                                                                        var k = "";
                                                                                                        var f = "";
                                                                                                        j.each(function(m) {
                                                                                                            var n = $(this);
                                                                                                            g = m + 1;
                                                                                                            if (n.attr("id") != "li_lastpage") {
                                                                                                                var o = n.data("field_properties").id;
                                                                                                                $("#pagenum_" + o).text(g);
                                                                                                                $("#pagetotal_" + o).text("Page " + g + " of " + e);
                                                                                                                if (g == 1) {
                                                                                                                    k = " ap_tp_num_active";
                                                                                                                    f = " ap_tp_text_active"
                                                                                                                } else {
                                                                                                                    k = "";
                                                                                                                    f = ""
                                                                                                                }
                                                                                                                if (a == "steps") {
                                                                                                                    b += '<td align="center"><span id="page_num_' + g + '" class="ap_tp_num' + k + '">' + g + '</span><span id="page_title_' + g + '" class="ap_tp_text' + f + '">' + n.data("field_properties").page_title + '</span></td><td align="center" class="ap_tp_arrow">&gt;</td>'
                                                                                                                }
                                                                                                            } else {
                                                                                                                $("#pagenum_lastpage").text(g);
                                                                                                                $("#pagetotal_lastpage").text("Page " + g + " of " + e);
                                                                                                                b += '<td align="center"><span id="page_num_' + g + '" class="ap_tp_num">' + g + '</span><span id="page_title_' + g + '" class="ap_tp_text">' + n.data("field_properties").page_title + "</span></td>"
                                                                                                            }
                                                                                                        });
                                                                                                        $("#pagination_header").children().remove();
                                                                                                        if (a == "steps") {
                                                                                                            var i = '<table class="ap_table_pagination" width="100%" border="0" cellspacing="0" cellpadding="0"><tr>' + b + "</tr></table>";
                                                                                                            $("#pagination_header").append(i)
                                                                                                        } else {
                                                                                                            if (a == "percentage") {
                                                                                                                var h = '<h3 id="page_title_1">Page 1 of 1 - Page Title</h3><div class="mf_progress_container"><div id="mf_progress_percentage" class="mf_progress_value" style="width: 1%"><span>1%</span></div></div>';
                                                                                                                $("#pagination_header").append(h);
                                                                                                                $("#page_title_1").html("Page 1 of " + e + " - " + j.eq(0).data("field_properties").page_title);
                                                                                                                var c = Math.round((1 / e) * 100);
                                                                                                                $("#mf_progress_percentage > span").html(c + "%");
                                                                                                                $("#mf_progress_percentage").animate({
                                                                                                                    width: c + "%"
                                                                                                                }, {
                                                                                                                    duration: 200,
                                                                                                                    queue: false
                                                                                                                })
                                                                                                            } else {
                                                                                                                $("#pagination_header").append('<h3 class="no_header">Pagination Header Disabled</h3>')
                                                                                                            }
                                                                                                        }
                                                                                                        if (a == "disabled") {
                                                                                                            $("#prop_pagination_style legend").css({
                                                                                                                "background-color": "#DC6666",
                                                                                                                "border-color": "#DC6666"
                                                                                                            });
                                                                                                            $("#prop_pagination_style fieldset").css("border-color", "#DC6666");
                                                                                                            $("#prop_pagination_titles").fadeOut("slow")
                                                                                                        } else {
                                                                                                            $("#prop_pagination_style legend").css({
                                                                                                                "background-color": "#3D6C10",
                                                                                                                "border-color": "#3D6C10"
                                                                                                            });
                                                                                                            $("#prop_pagination_style fieldset").css("border-color", "#3D6C10");
                                                                                                            if (d.pagination_type == "disabled") {
                                                                                                                $("#prop_pagination_titles").slideDown()
                                                                                                            }
                                                                                                        }
                                                                                                        d.pagination_type = a
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
         }
        }
        }
        }
        }
        }
            }
        }
    }
}

function adjust_main_height() {
    var d = 75;
    if ($.browser.msie) {
        d = -10
    }
    var b = ($("#sidebar").offset().top + $("#sidebar").outerHeight()) - d;
    var c = $("#header").outerHeight() + $("#navigation").outerHeight() + $("#content").outerHeight();
    if (b > c) {
        var a = (b - c);
        $("#main").animate({
            "padding-bottom": a + "px"
        }, {
            duration: 200,
            queue: true
        })
    }
}

function update_date_range_min_linked(b) {
    $("#date_range_min_mm").val(b.length ? b[0].getMonth() + 1 : "");
    $("#date_range_min_dd").val(b.length ? b[0].getDate() : "");
    $("#date_range_min_yyyy").val(b.length ? b[0].getFullYear() : "");
    var a = $("#date_range_min_yyyy").val() + "-" + $("#date_range_min_mm").val() + "-" + $("#date_range_min_dd").val();
    set_properties("element_date_range_min", a)
}

function update_date_range_max_linked(b) {
    $("#date_range_max_mm").val(b.length ? b[0].getMonth() + 1 : "");
    $("#date_range_max_dd").val(b.length ? b[0].getDate() : "");
    $("#date_range_max_yyyy").val(b.length ? b[0].getFullYear() : "");
    var a = $("#date_range_max_yyyy").val() + "-" + $("#date_range_max_mm").val() + "-" + $("#date_range_max_dd").val();
    set_properties("element_date_range_max", a)
}

function update_scheduling_start_linked(b) {
    $("#scheduling_start_mm").val(b.length ? b[0].getMonth() + 1 : "");
    $("#scheduling_start_dd").val(b.length ? b[0].getDate() : "");
    $("#scheduling_start_yyyy").val(b.length ? b[0].getFullYear() : "");
    var a = $("#scheduling_start_yyyy").val() + "-" + $("#scheduling_start_mm").val() + "-" + $("#scheduling_start_dd").val();
    $("#form_header").data("form_properties").schedule_start_date = a
}

function update_scheduling_end_linked(b) {
    $("#scheduling_end_mm").val(b.length ? b[0].getMonth() + 1 : "");
    $("#scheduling_end_dd").val(b.length ? b[0].getDate() : "");
    $("#scheduling_end_yyyy").val(b.length ? b[0].getFullYear() : "");
    var a = $("#scheduling_end_yyyy").val() + "-" + $("#scheduling_end_mm").val() + "-" + $("#scheduling_end_dd").val();
    $("#form_header").data("form_properties").schedule_end_date = a
}

function reorganize_page_break() {
    var i = $("#form_builder_sortable > li.page_break");
    var d = $("#form_header").data("form_properties").pagination_type;
    var h = '<li class="no_fields_in_page" title="Drag the button on the right side here to add new field."><span style="float: none;font-family: machform;margin-bottom: 20px;color: #529214;font-size: 50px;display: block" class="icon-arrow-right"></span><h3>This page has no fields!</h3><p><span style="color: #529214; font-weight: bold; float: none">Drag the button</span> on the right sidebar here to add new field.</p></li>';
    if (i.not("#li_lastpage").length >= 1) {
        if ($("#li_lastpage").length == 1) {
            $("#li_lastpage").appendTo("#form_builder_sortable")
        }
        if (i.not("#li_lastpage").length == 1) {
            if ($("#pagination_header").length == 0) {
                var c = '<li id="pagination_header" style="display: none" class="li_pagination" title="Click to edit"><table class="ap_table_pagination" width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="center"><span id="page_num_1" class="ap_tp_num ap_tp_num_active">1</span><span id="page_title_1" class="ap_tp_text ap_tp_text_active">Untitled Page</span></td><td align="center" class="ap_tp_arrow">&gt;</td><td align="center"><span id="page_num_2" class="ap_tp_num">2</span><span id="page_title_2" class="ap_tp_text">Untitled Page</span></td></tr></table></li>';
                $("#form_builder_sortable").prepend(c);
                $("#pagination_header").fadeIn();
                $("#pagination_header").click(function(k) {
                    $("#form_header").data("coming_from_click", 1);
                    var l = $(this).attr("id");
                    select_form_header(l)
                })
            } else {
                if (d == "steps") {
                    $("#pagination_header td:gt(2)").remove();
                    $("#page_num_1").html("1");
                    $("#page_num_2").html("2");
                    $("#page_title_1").html($("#form_builder_sortable > li.page_break").not(".li_pagination").not("#li_lastpage").data("field_properties").page_title);
                    $("#page_title_2").html($("#li_lastpage").data("field_properties").page_title)
                } else {
                    if (d == "percentage") {
                        $("#page_title_1").html("Page 1 of 2 - " + $("#form_builder_sortable > li.page_break").not(".li_pagination").not("#li_lastpage").data("field_properties").page_title);
                        $("#mf_progress_percentage").animate({
                            width: "50%"
                        }, {
                            duration: 200,
                            queue: false
                        });
                        $("#mf_progress_percentage > span").html("50%")
                    }
                }
            }
            if ($("#li_lastpage").length == 0) {
                $('<li id="li_lastpage" class="page_break" title="Click to edit">' + i.html().replace(/_[0-9]+/g, "_lastpage") + "</li>").appendTo("#form_builder_sortable").addClass("synched");
                $("#pagenum_lastpage").text("2");
                $("#pagetotal_lastpage").text("Page 2 of 2");
                $("#btn_submit_lastpage").val("Submit");
                if ($("#li_lastpage").prev().hasClass("page_break")) {
                    $("#li_lastpage").before(h)
                }
                $("#li_lastpage").data("field_properties", {
                    id: "lastpage",
                    type: "page_break",
                    page_title: "Untitled Page",
                    submit_primary_text: "Submit",
                    submit_secondary_text: "Previous"
                })
            } else {
                var e = 2;
                $("#form_builder_sortable > li.page_break").each(function(l) {
                    var k = l + 1;
                    if ($(this).attr("id") != "li_lastpage") {
                        var m = $(this).data("field_properties").id;
                        $("#pagenum_" + m).text(k);
                        $("#pagetotal_" + m).text("Page " + k + " of " + e)
                    } else {
                        $("#pagenum_lastpage").text(k);
                        $("#pagetotal_lastpage").text("Page " + k + " of " + e)
                    }
                })
            }
            if (i.index() == 1) {
                if ($("#form_builder_sortable > li").not(".li_pagination").not(".page_break").not(".li_lastpage").length == 0) {
                    i.before(h);
                    i.after(h)
                } else {
                    i.before(h)
                }
            }
        } else {
            var e = i.length;
            var g = 1;
            var a = "";
            var j = "";
            var f = "";
            if (d == "steps") {
                $("#pagination_header td").remove()
            }
            i.each(function(k) {
                var l = $(this);
                if (l.index() == 1) {
                    l.before(h)
                }
                if (l.prev().hasClass("page_break")) {
                    l.before(h)
                }
                if (l.next().hasClass("page_break")) {
                    l.after(h)
                }
                g = k + 1;
                if (l.attr("id") != "li_lastpage") {
                    var m = l.data("field_properties").id;
                    $("#pagenum_" + m).text(g);
                    $("#pagetotal_" + m).text("Page " + g + " of " + e);
                    if (g == 1) {
                        j = " ap_tp_num_active";
                        f = " ap_tp_text_active"
                    } else {
                        j = "";
                        f = ""
                    }
                    if (d == "steps") {
                        a += '<td align="center"><span id="page_num_' + g + '" class="ap_tp_num' + j + '">' + g + '</span><span id="page_title_' + g + '" class="ap_tp_text' + f + '">' + l.data("field_properties").page_title + '</span></td><td align="center" class="ap_tp_arrow">&gt;</td>'
                    }
                } else {
                    $("#pagenum_lastpage").text(g);
                    $("#pagetotal_lastpage").text("Page " + g + " of " + e);
                    a += '<td align="center"><span id="page_num_' + g + '" class="ap_tp_num">' + g + '</span><span id="page_title_' + g + '" class="ap_tp_text">' + l.data("field_properties").page_title + "</span></td>"
                }
            });
            if (d == "steps") {
                $("#pagination_header tr").append(a)
            } else {
                if (d == "percentage") {
                    $("#page_title_1").html("Page 1 of " + e + " - " + i.eq(0).data("field_properties").page_title);
                    var b = Math.round((1 / e) * 100);
                    $("#mf_progress_percentage").animate({
                        width: b + "%"
                    }, {
                        duration: 200,
                        queue: false
                    });
                    $("#mf_progress_percentage > span").html(b + "%")
                }
            }
        }
        $("#form_builder_sortable > li.no_fields_in_page").each(function(l) {
            var k = $(this);
            if (k.index() == 1) {
                if (k.next().hasClass("page_break") == false) {
                    k.remove()
                }
            } else {
                if (k.prev().hasClass("page_break") == false || k.next().hasClass("page_break") == false) {
                    k.remove()
                }
            }
        });
        $("#form_builder_sortable > li.firstpage").removeClass("firstpage");
        $("#form_builder_sortable > li.page_break:eq(0)").addClass("firstpage");
        $("#form_resume").prop("disabled", false)
    } else {
        $("#pagination_header").remove();
        $("#li_lastpage").remove();
        if ($("#form_builder_sortable > li").not(".no_fields_in_page").length > 0) {
            $("#form_builder_sortable > li.no_fields_in_page").remove()
        } else {
            $("#form_builder_sortable > li.no_fields_in_page").remove();
            $("#form_builder_sortable").append(h)
        }
        $("#form_header").data("form_properties").resume_enable = 0;
        $("#form_resume").prop("checked", false);
        $("#form_resume").prop("disabled", true)
    }
}

function rebuild_quantity_link_dropdown() {
    var b = $("#form_builder_sortable > li.multiple_choice,#form_builder_sortable > li.dropdown,#form_builder_sortable > li.checkboxes,#form_builder_sortable > li.price");
    var c = 0;
    var d = new Array();
    var a = new Array();
    b.each(function(f) {
        var h = null;
        var e = 0;
        h = $(this).data("field_properties");
        if ($("#pagination_header").length > 0) {
            e = h.position
        } else {
            e = parseInt(h.position) + 1
        }
        if (h.type == "checkbox") {
            var g = h.options;
            $.each(g, function(i, j) {
                d[c] = j.option + " (#" + e + ")";
                a[c] = "element_" + h.id + "_" + i;
                c++
            })
        } else {
            d[c] = h.title + " (#" + e + ")";
            a[c] = "element_" + h.id;
            c++
        }
    });
    $("#prop_number_quantity_link > option").remove();
    $("#prop_number_quantity_link").append('<option value="">-- Please Select --</option>');
    $.each(d, function(e, g) {
        var f = '<option value="' + a[e] + '">' + g + "</option>";
        $("#prop_number_quantity_link").append(f)
    });
    if (d.length == 0) {
        $("#prop_number_quantity_link > option").remove();
        $("#prop_number_quantity_link").append('<option value=""> -- No supported fields available --</option>')
    }
}

function random_string(d, c) {
    var a = "";
    for (var b = d; b > 0; --b) {
        a += c[Math.round(Math.random() * (c.length - 1))]
    }
    return a
}

function rebuild_default_text_random_preview() {
    var d = $("#" + active_element);
    var c = d.data("field_properties");
    var b = "";
    var a = parseInt(c.text_default_length);
    if (a == 0) {
        a = 10
    }
    if (c.text_default_random_type == "letter") {
        switch (c.text_default_case) {
            case "u":
                b = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                break;
            case "l":
                b = "abcdefghijklmnopqrstuvwxyz";
                break;
            case "b":
                b = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
                break
        }
    } else {
        if (c.text_default_random_type == "number") {
            b = "0123456789"
        } else {
            if (c.text_default_random_type == "alphanum") {
                switch (c.text_default_case) {
                    case "u":
                        b = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        break;
                    case "l":
                        b = "0123456789abcdefghijklmnopqrstuvwxyz";
                        break;
                    case "b":
                        b = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
                        break
                }
            } else {
                if (c.text_default_random_type == "all") {
                    switch (c.text_default_case) {
                        case "u":
                            b = "\"'`~!@#$%^&*()_-+={}[]|\\:;<>,.?/0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                            break;
                        case "l":
                            b = "\"'`~!@#$%^&*()_-+={}[]|\\:;<>,.?/0123456789abcdefghijklmnopqrstuvwxyz";
                            break;
                        case "b":
                            b = "\"'`~!@#$%^&*()_-+={}[]|\\:;<>,.?/0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
                            break
                    }
                }
            }
        }
    }
    $("#" + active_element + " :input").val(c.text_default_prefix + random_string(a, b))
}
$(function() {
    $("#tab_field_properties").show();
    $("#tab_form_properties").show();
    $("#builder_tabs_btn").show();
    $("#builder_tabs").tabs();
    $("#builder_tabs").bind("tabsshow", function(c, d) {
        if (d.index === 0) {
            $("#" + active_element).removeClass("highlighted");
            active_element = null;
            $("#form_header .highlighted").removeClass("highlighted");
            $("#pagination_header").removeClass("highlighted");
            $("#selected_field_image").css("visibility", "hidden");
            $("#form_prop_toggle_a").text("show more options");
            $("#form_prop_toggle_img").attr("src", "/form_builder/images/icons/resultset_next.gif");
            $("#all_form_properties .advanced_prop").hide();
            $("#bottom_bar_field_action").fadeOut("fast", function() {
                $("#bottom_bar_content").animate({
                    "margin-left": "225px"
                }, {
                    duration: 200,
                    queue: false
                })
            });
            check_synch_fields()
        } else {
            if (d.index === 1) {
                $("#form_header .highlighted").removeClass("highlighted");
                $("#pagination_header").removeClass("highlighted");
                $("#form_prop_toggle_a").text("show more options");
                $("#form_prop_toggle_img").attr("src", "/form_builder/images/icons/resultset_next.gif");
                $("#all_form_properties .advanced_prop").hide();
                if (active_element === null) {
                    $("#element_properties_form").hide();
                    $("#element_inactive_msg").show()
                }
            } else {
                if (d.index === 2) {
                    if ($("#form_header").data("coming_from_click") != 1) {
                        select_form_header("form_header_title")
                    }
                }
            }
        }
    });
    $("#form_builder_sortable").sortable({
        items: "li:not(.li_pagination,#li_lastpage)",
        axis: "y",
        opacity: 0.7,
        placeholder: "ui-state-highlight",
        scrollSensitivity: 100,
        scrollSpeed: 40,
        receive: function(g, i) {
            var e = $("#form_header").data("form_properties").id;
            if ($("#li_dummy").length) {
                $("#li_dummy").remove();
                $("#no_fields_notice").remove()
            }
            var h = $("#form_builder_sortable > li").not(".li_pagination").size() + 1;
            var f = $(i.sender).attr("id");
            $("#form_builder_sortable .ui-draggable").attr("id", "li_temp_" + h).removeClass("box ui-draggable").addClass("drop_processing").html('<img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> creating field ...').hide().fadeIn();
            var d = $("#li_temp_" + h).index() + 1;
            var c;
            switch (f) {
                case "btn_single_line_text":
                    c = "text";
                    break;
                case "btn_number":
                    c = "number";
                    break;
                case "btn_paragraph_text":
                    c = "textarea";
                    break;
                case "btn_checkboxes":
                    c = "checkbox";
                    break;
                case "btn_multiple_choice":
                    c = "radio";
                    break;
                case "btn_drop_down":
                    c = "select";
                    break;
                case "btn_name":
                    c = "simple_name";
                    break;
                case "btn_date":
                    c = "date";
                    break;
                case "btn_time":
                    c = "time";
                    break;
                case "btn_phone":
                    c = "phone";
                    break;
                case "btn_address":
                    c = "address";
                    break;
                case "btn_website":
                    c = "url";
                    break;
                case "btn_price":
                    c = "money";
                    break;
                case "btn_email":
                    c = "email";
                    break;
                case "btn_section_break":
                    c = "section";
                    break;
                case "btn_file_upload":
                    c = "file";
                    break;
                case "btn_page_break":
                    c = "page_break";
                    break;
                case "btn_matrix":
                    c = "matrix";
                    break;
                case "btn_signature":
                    c = "signature";
                    break;
				//OTB patch - btn_plinth_area
				case "btn_plinth_area":
					c = "plinth_area";
					break;
			   //OTB patch - btn_total_plinth_area
				case "btn_total_plinth_area":
					c = "total_plinth_area";
					break;
                default:
                    c = "text";
                    break;
            }
            $.ajax({
                type: "POST",
                async: true,
                url: "/backend.php/forms/addfield",
                data: {
                    element_type: c,
                    form_id: e,
                    holder_id: "li_temp_" + h,
                    position: d,
                    action: "drag_new"
                },
                cache: false,
                global: true,
                dataType: "json",
                error: function(l, j, k) {
                    $("#form_builder_sortable .drop_processing").remove()
                },
                success: function(k) {
                    if (k.status == "ok") {
                        var j = $(k.markup);
                        j.data("field_properties", k.field_properties).addClass("synched");
                        $("#" + k.holder_id).replaceWith(j);
                        $("#li_" + k.element_id).hide().fadeIn();
                        reorganize_page_break();
                        $("#form_builder_sortable").sortable("refreshPositions")
                    } else {
                        $("#dialog-message").dialog("open");
                        $("#form_builder_sortable .drop_processing").remove()
                    }
                }
            })
        },
        update: function(c, d) {
            if ($(d.item).hasClass("no_fields_in_page")) {
                $(this).sortable("cancel")
            } else {
                if ($(d.item).hasClass("drop_processing") === false) {
                    $("#builder_tabs").tabs("select", 1);
                    select_element("#" + $(d.item).attr("id"))
                }
                reorganize_page_break();
                if ($(d.item).hasClass("firstpage") == true) {
                    select_element("#" + $(d.item).attr("id"))
                }
            }
        }
    });
    $("#form_builder_sortable").disableSelection();
    $(document).ready(function() {
        sidebar_scroll_pos = $("#sidebar").position().top;
        if (isScrolledIntoView("#bottom_bar_limit")) {
            $("#bottom_bar").css("position", "relative").css("background-color", "#fff")
        } else {
            $("#bottom_bar").css("position", "fixed").css("background-color", "#effab4")
        }
        $(window).scroll(function() {
            // if (isScrolledIntoView("#navigation")) {
            //     sidebar_pos = sidebar_scroll_pos
            // } else {
            //     sidebar_pos = sidebar_scroll_pos - 135
            // }
            // var c = sidebar_pos + $(document).scrollTop() + "px";
            // if ($("#builder_tabs").tabs("option", "selected") === 0) {
            //     $("#sidebar").animate({
            //         top: c
            //     }, {
            //         duration: 500,
            //         queue: false
            //     })
            // }
            // if (isScrolledIntoView("#bottom_bar_limit")) {
            //     $("#bottom_bar").css("position", "relative").css("background-color", "#fff")
            // } else {
            //     $("#bottom_bar").css("position", "fixed").css("background-color", "#effab4")
            // }
        })
    });
    $("#form_builder_preview").delegate("li:not(#pagination_header):not(.no_fields_in_page)", "click", function(c) {
        $("#builder_tabs").tabs("select", 1);
        select_element("#" + $(this).attr("id"))
    });
    $("#form_header_title,#form_header_desc,#pagination_header").click(function(c) {
        $("#form_header").data("coming_from_click", 1);
        var d = $(this).attr("id");
        select_form_header(d)
    });
    $("#bottom_bar_add_field").click(function() {
        $("#builder_tabs").tabs("select", 0);
        if (isScrolledIntoView("#navigation")) {
            sidebar_pos = sidebar_scroll_pos
        } else {
            sidebar_pos = sidebar_scroll_pos - 100
        }
        var c = sidebar_pos + $(document).scrollTop() + "px";
        $("#sidebar").animate({
            top: c
        }, {
            duration: 500,
            queue: false
        });
        $("#" + active_element).removeClass("highlighted");
        active_element = null;
        $("#form_header .highlighted").removeClass("highlighted");
        $("#selected_field_image").css("visibility", "hidden");
        $("#bottom_bar_field_action").fadeOut("fast", function() {
            $("#bottom_bar_content").animate({
                "margin-left": "225px"
            }, {
                duration: 200,
                queue: false
            })
        });
        return false
    });
    $("#bottom_bar_save_form").click(function() {
        if ($("#form_builder_sortable > li.no_fields_in_page").length > 0) {
            $("#ui-dialog-title-dialog-warning").html("Unable to save. Your form contain empty pages!");
            $("#dialog-warning-msg").html("Your form contain one or more empty pages<br />Please add a field into the empty page or delete the empty page.");
            $("#dialog-warning").dialog("open");
            return false
        }
        $("#bottom_bar_msg").text("Please wait... Synching...");
        $("#bottom_bar_content").fadeOut("fast", function() {
            $("#bottom_bar_loader").fadeIn()
        });
        var c = $("#form_header").data("form_properties").id;
        var e = $("#form_builder_sortable > li").not(".dblive").not(".synched").not(".li_pagination").not("#li_lastpage");
        if (e.length >= 1) {
            var d = new Array();
            e.each(function(f) {
                d[f] = $(this).data("field_properties")
            });
            $.ajax({
                type: "POST",
                async: true,
                url: "/backend.php/forms/synchfields",
                data: {
                    form_id: c,
                    fp: d
                },
                cache: false,
                global: false,
                dataType: "json",
                error: function(h, f, g) {},
                success: function(f) {
                    if (f.status == "ok") {
                        $(f.updated_element_id).addClass("synched");
                        save_form()
                    }
                }
            })
        } else {
            save_form()
        }
        return false
    });
    $("#field_properties_pane img[title].helpmsg,#form_properties_pane img[title].helpmsg").tooltip({
        position: "bottom center",
        offset: [10, 20],
        effect: "fade",
        opacity: 0.8,
        events: {
            def: "click,mouseout"
        }
    });
    $("#element_label").bind("keyup mouseout", function() {
        set_properties("element_label", $(this).val())
    });
    $("#element_required").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = "1"
        } else {
            c = "0"
        }
        set_properties("element_required", c)
    });
    $("#element_unique").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = "1"
        } else {
            c = "0"
        }
        set_properties("element_unique", c)
    });
    $("#element_readonly").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = "1"
        } else {
            c = "0"
        }
        set_properties("element_readonly", c)
    });
    $("#element_public").bind("click", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = "0"
        } else {
            c = "1"
        }
        set_properties("element_visibility", c)
    });
    $("#element_hidden").bind("click", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = "2"
        } else {
            c = "0"
        }
        set_properties("element_visibility", c)
    });
    $("#element_private").bind("click", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = "1"
        } else {
            c = "0"
        }
        set_properties("element_visibility", c)
    });
    $("#element_default_value_text_static").bind("click", function() {
        if ($(this).prop("checked") == true) {
            set_properties("element_text_default_type", "static")
        }
    });
    $("#element_default_value_text_random").bind("click", function() {
        if ($(this).prop("checked") == true) {
            set_properties("element_text_default_type", "random")
        }
    });
    $("#element_text_default_length").bind("keyup mouseout", function() {
        set_properties("element_text_default_length", $(this).val())
    });
    $("#element_text_default_prefix").bind("keyup mouseout", function() {
        set_properties("element_text_default_prefix", $(this).val())
    });
    $("#element_text_default_random_type").bind("change", function() {
        set_properties("element_text_default_random_type", $(this).val())
    });
    $("#element_text_default_case").bind("change", function() {
        set_properties("element_text_default_case", $(this).val())
    });
    $("#element_instructions").bind("keyup mouseout", function() {
        set_properties("element_guidelines", $(this).val())
    });
    $("#element_custom_css").bind("keyup mouseout", function() {
        set_properties("element_custom_css", $(this).val())
    });
    //OTB patch - For plinth area
    $("#element_footprint").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_footprint", b)
    });
    //
    $("#element_plotsize").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_plotsize", b)
    });
    $("#element_actualplotratio").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_actualplotratio", b)
    });
    $("#element_permittedgroundcoverage").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_permittedgroundcoverage", b)
    });
    
    $("#element_zone").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_zone", b)
    });
    
     $("#element_projectcost").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_projectcost", b)
    });
    // OTB patch - grandtotalplintharea onchange -- 
    $("#element_grandtotalplintharea").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_grandtotalplintharea", b)
    });
    // OTB patch - element_grandtotalfootprintarea onchange -- 
    $("#element_grandtotalfootprintarea").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_grandtotalfootprintarea", b)
    });
     $("#element_groundcoveragereason").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_groundcoveragereason", b)
    });
     $("#element_plotratioreason").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_plotratioreason", b)
    });
    
    $("#element_file_ifc").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_file_ifc", b)
    });
    $("#element_permitteduser").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_permitteduser", b)
    });
    $("#element_plot_no").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_plot_no", b)
    });
    $("#element_ownertype").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = "1"
        } else {
            b = "0"
        }
        set_properties("element_ownertype", b)
    });
    ////
    $("#element_choices").delegate("input.text", "keyup", function(p) {
        var n = $(this);
        var g = n.attr("id").split("_");
        var i = g[1];
        var h = "&nbsp;";
        var r = $("#" + active_element);
        var l = r.data("field_properties").options[i].position;
        var d = r.data("field_properties").id;
        var f = r.data("field_properties").type;
        if (p.keyCode == 13) {
            var k = parseInt(r.data("field_properties").last_option_id) + 1;
            var m = "";
            if (f == "radio" || f == "select") {
                m = "radio"
            } else {
                if (f == "checkbox") {
                    m = "checkbox"
                }
            }
            var c = '<li><input type="' + m + '" title="Select this choice as the default." class="choices_default" name="choices_default" id="choicedef_' + k + '" /><input type="text" value="" autocomplete="off" class="text" id="choice_' + k + '" /> <img class="add_choice" title="Add" alt="Add" src="/form_builder/images/icons/add.png" style="vertical-align: middle" id="choiceadd_' + k + '" ><img class="del_choice" title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png" style="vertical-align: middle" id="choicedel_' + k + '" ></li>';
            n.parent().after(c);
            r.data("field_properties").last_option_id = k;
            var o = "";
            if (f == "radio") {
                o = '<span><input type="radio" value="1" class="element radio" name="element_' + d + '" id="element_' + d + "_" + k + '"><label for="element_' + d + "_" + k + '" class="choice">&nbsp;</label></span>';
                $("#element_" + d + "_" + i).parent().after(o)
            } else {
                if (f == "checkbox") {
                    o = '<span><input type="checkbox" value="1" class="element checkbox" name="element_' + d + '" id="element_' + d + "_" + k + '"><label for="element_' + d + "_" + k + '" class="choice">&nbsp;</label></span>';
                    $("#element_" + d + "_" + i).parent().after(o)
                } else {
                    if (f == "select") {
                        o = '<option value="' + k + '">&nbsp;</option>';
                        $("#element_" + d + " option[value=" + i + "]").after(o)
                    }
                }
            }
            $("#choice_" + k).focus();
            var j = r.data("field_properties").options;
            j[k] = {
                position: l + 1,
                option: "",
                is_default: 0,
                is_db_live: "0"
            };
            r.data("field_properties").options = j;
            var q = $("#choice_" + k).parent().nextAll().children().filter("input.text");
            $.each(q, function(e, t) {
                var s = null;
                s = $(t).attr("id").split("_");
                r.data("field_properties").options[s[1]].position++
            })
        } else {
            if (n.val() != "") {
                h = n.val()
            }
            if (f == "select") {
                $("#element_" + d + " option[value=" + i + "]").text(h)
            } else {
                $("#element_" + d + "_" + i).next().html(h)
            }
            r.data("field_properties").options[i].option = n.val()
        }
    });
    $("#element_choices").delegate("input.choices_default", "click", function(k) {
        var i = $(this);
        var g = i.attr("id").split("_");
        var h = g[1];
        var c = active_element;
        var j = c.split("_");
        var d = j[1];
        var l = $("#" + active_element);
        var f = l.data("field_properties").type;
        if (f == "radio" || f == "select") {
            if (l.data("field_properties").options[h].is_default == "0") {
                if (f == "radio") {
                    $("#element_" + d + "_" + h).prop("checked", true)
                } else {
                    if (f == "select") {
                        $("#element_" + d).val(h)
                    }
                }
                $(l.data("field_properties").options).each(function(e, m) {
                    $.each(m, function(n, o) {
                        l.data("field_properties").options[n].is_default = "0"
                    })
                });
                l.data("field_properties").options[h].is_default = "1"
            } else {
                if (f == "radio") {
                    $("#element_" + d + "_" + h).prop("checked", false)
                } else {
                    if (f == "select") {
                        if ($("#element_" + d).find('option[value=""]').length == 0) {
                            $("#element_" + d).prepend('<option value=""></option>')
                        }
                        $("#element_" + d).val("")
                    }
                }
                $(l.data("field_properties").options).each(function(e, m) {
                    $.each(m, function(n, o) {
                        l.data("field_properties").options[n].is_default = "0"
                    })
                });
                $(this).prop("checked", false)
            }
        } else {
            if (f == "checkbox") {
                if (i.prop("checked") == true) {
                    $("#element_" + d + "_" + h).prop("checked", true);
                    l.data("field_properties").options[h].is_default = "1"
                } else {
                    $("#element_" + d + "_" + h).prop("checked", false);
                    l.data("field_properties").options[h].is_default = "0"
                }
            }
        }
    });
    $("#element_choices").delegate("img.add_choice", "click", function(p) {
        var n = $(this);
        var g = n.attr("id").split("_");
        var i = g[1];
        var h = "&nbsp;";
        var r = $("#" + active_element);
        var l = r.data("field_properties").options[i].position;
        var d = r.data("field_properties").id;
        var f = r.data("field_properties").type;
        var m = "";
        if (f == "radio" || f == "select") {
            m = "radio"
        } else {
            if (f == "checkbox") {
                m = "checkbox"
            }
        }
        var k = parseInt(r.data("field_properties").last_option_id) + 1;
        var c = '<li><input type="' + m + '" title="Select this choice as the default." class="choices_default" name="choices_default" id="choicedef_' + k + '" /><input type="text" value="" autocomplete="off" class="text" id="choice_' + k + '" /> <img class="add_choice" title="Add" alt="Add" src="/form_builder/images/icons/add.png" style="vertical-align: middle" id="choiceadd_' + k + '" ><img class="del_choice" title="Delete" alt="Delete" src="/form_builder/images/icons/delete.png" style="vertical-align: middle" id="choicedel_' + k + '" ></li>';
        n.parent().after(c);
        r.data("field_properties").last_option_id = k;
        var o = "";
        if (f == "radio") {
            o = '<span><input type="radio" value="1" class="element radio" name="element_' + d + '" id="element_' + d + "_" + k + '"><label for="element_' + d + "_" + k + '" class="choice">&nbsp;</label></span>'
        } else {
            if (f == "checkbox") {
                o = '<span><input type="checkbox" value="1" class="element checkbox" name="element_' + d + '" id="element_' + d + "_" + k + '"><label for="element_' + d + "_" + k + '" class="choice">&nbsp;</label></span>'
            } else {
                if (f == "select") {
                    o = '<option value="' + k + '">&nbsp;</option>';
                    $("#element_" + d + " option[value=" + i + "]").after(o)
                }
            }
        }
        $("#element_" + d + "_" + i).parent().after(o);
        $("#choice_" + k).focus();
        var j = r.data("field_properties").options;
        j[k] = {
            position: l + 1,
            option: "",
            is_default: 0,
            is_db_live: "0"
        };
        r.data("field_properties").options = j;
        var q = $("#choice_" + k).parent().nextAll().children().filter("input.text");
        $.each(q, function(e, t) {
            var s = null;
            s = $(t).attr("id").split("_");
            r.data("field_properties").options[s[1]].position++
        })
    });
    $("#element_choices").delegate("img.del_choice", "click", function(k) {
        if ($("#element_choices > li").size() <= 1) {
            $("#ui-dialog-title-dialog-warning").html("Unable to delete this choice!");
            $("#dialog-warning-msg").html("You cannot delete all choices! <br />At least 1 choice is required.");
            $("#dialog-warning").dialog("open");
            return false
        }
        var j = $(this);
        var d = j.attr("id").split("_");
        var g = d[1];
        var m = $("#" + active_element);
        var i = m.data("field_properties").options[g].position;
        var c = m.data("field_properties").id;
        var f = m.data("field_properties").type;
        if (m.data("field_properties").is_db_live == 1 && m.data("field_properties").deletion_confirmed == undefined) {
            $("#dialog-confirm-choice-delete").dialog("open");
            m.data("field_properties").deletion_confirmed = 1;
            return false
        }
        var h = m.data("field_properties").options;
        delete h[g];
        m.data("field_properties").options = h;
        var l = $("#choice_" + g).parent().nextAll().children().filter("input.text");
        $.each(l, function(e, o) {
            var n = null;
            n = $(o).attr("id").split("_");
            m.data("field_properties").options[n[1]].position--
        });
        $("#choicedel_" + g).parent().remove();
        if (f == "radio" || f == "checkbox") {
            $("#element_" + c + "_" + g).parent().remove()
        } else {
            if (f == "select") {
                $("#element_" + c + " option[value=" + g + "]").remove()
            }
        }
    });
    $("#choice_max_entry").bind("keyup mouseout", function() {
        set_properties("element_choice_max_entry", $(this).val())
    });
    $("#prop_choice_max_entry_enable").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            $("#form_choice_max_entry").slideDown()
        } else {
            $("#choice_max_entry").val(0);
            set_properties("element_choice_max_entry", 0);
            $("#form_choice_max_entry").hide()
        }
    });
    $("#bulk_import_choices").click(function() {
        $("#bulk_insert_choices").val("");
        if ($("#" + active_element).data("field_properties").options[1] !== undefined) {
            if (($("#" + active_element).data("field_properties").options[1].option == "First option") && ($("#element_choices > li").size() == 3)) {
                $("#bulk_insert_choices").val("New option 1\nNew option 2\nNew option 3")
            }
        }
        $("#dialog-insert-choices").next().children().eq(1).addClass("negative_btn");
        $("#dialog-insert-choices").dialog("open");
        $("#bulk_insert_choices").focus();
        return false
    });
    $("#bulk_import_matrix_row").click(function() {
        $("#bulk_insert_rows").val("");
        if ($("#" + active_element + " table td.first_col").eq(0).text() == "First Question" && $("#" + active_element + " table td.first_col").eq(1).text() == "Second Question" && $("#" + active_element + " table td.first_col").eq(2).text() == "Third Question" && $("#" + active_element + " table td.first_col").eq(3).text() == "Fourth Question") {
            $("#bulk_insert_rows").val("New question 1\nNew question 2\nNew question 3")
        }
        $("#dialog-insert-matrix-rows").next().children().eq(1).addClass("negative_btn");
        $("#dialog-insert-matrix-rows").dialog("open");
        $("#bulk_insert_rows").focus();
        return false
    });
    $("#bulk_import_matrix_column").click(function() {
        $("#bulk_insert_columns").val("");
        if ($("#" + active_element + " table thead th").eq(1).text() == "Answer A" && $("#" + active_element + " table thead th").eq(2).text() == "Answer B" && $("#" + active_element + " table thead th").eq(3).text() == "Answer C" && $("#" + active_element + " table thead th").eq(4).text() == "Answer D") {
            $("#bulk_insert_columns").val("Answer E\nAnswer F\nAnswer G")
        }
        $("#dialog-insert-matrix-columns").next().children().eq(1).addClass("negative_btn");
        $("#dialog-insert-matrix-columns").dialog("open");
        $("#bulk_insert_columns").focus();
        return false
    });
    $("#element_choice_columns").bind("change", function() {
        set_properties("element_choice_columns", $(this).val())
    });
    $("#prop_choice_limit_rule").bind("change", function() {
        set_properties("element_choice_limit_rule", $(this).val())
    });
    $("#prop_choice_limit_qty").bind("keyup mouseout", function() {
        set_properties("element_choice_limit_qty", $(this).val())
    });
    $("#prop_choice_limit_range_min").bind("keyup mouseout", function() {
        set_properties("element_choice_limit_range_min", $(this).val())
    });
    $("#prop_choice_limit_range_max").bind("keyup mouseout", function() {
        set_properties("element_choice_limit_range_max", $(this).val())
    });
    $("#element_range_min").bind("keyup mouseout", function() {
        set_properties("element_range_min", $(this).val())
    });
    $("#element_range_max").bind("keyup mouseout", function() {
        set_properties("element_range_max", $(this).val())
    });
    $("#element_range_limit_by").bind("change", function() {
        set_properties("element_range_limit_by", $(this).val())
    });
    $("#element_range_number_limit_by").bind("change", function() {
        set_properties("element_range_limit_by", $(this).val())
    });
    $("#element_type").bind("change", function() {
        set_properties("element_type_change", $(this).val())
    });
    $("#element_size").bind("change", function() {
        set_properties("element_size", $(this).val())
    });
    $("#element_select_options").bind("change", function() {
        set_properties("element_select_options", $(this).val());
        if($(this).val() == 'default'){
            $("#prop_choices").show();
            $('#prop_table_name_conf').hide();
            $('#prop_option_query_conf').hide();
            $('#prop_application_conf').hide();
        }
        if($(this).val() == 'table'){
            $("#prop_choices").hide();
            $('#prop_option_query_conf').hide();
            $('#prop_table_name_conf').show();
            $('#prop_application_conf').hide();
        }
        if($(this).val() == 'query'){
            $("#prop_choices").hide();
            $('#prop_table_name_conf').hide();
            $('#prop_option_query_conf').show();
            $('#prop_application_conf').hide();
        }
        if($(this).val() == 'application'){
            $("#prop_choices").hide();
            $('#prop_table_name_conf').hide();
            $('#prop_option_query_conf').hide();
            $('#prop_application_conf').show();
        }
    });
    $("#name_format").bind("change", function() {
        set_properties("element_type", $(this).val())
    });
    $("#phone_format").bind("change", function() {
        set_properties("element_type", $(this).val())
    });
    $("#element_default_value,#element_default_value_text").bind("keyup mouseout", function() {
        set_properties("element_default_value", $(this).val())
    });
    $("#element_default_phone1,#element_default_phone2,#element_default_phone3").bind("keyup mouseout", function() {
        var c = $("#element_default_phone1").val() + $("#element_default_phone2").val() + $("#element_default_phone3").val();
        set_properties("element_default_value", c)
    });
    $("#element_default_value_textarea").bind("keyup mouseout", function() {
        set_properties("element_default_value", $(this).val())
    });
    $("#element_default_date").bind("keyup mouseout", function() {
        set_properties("element_default_value", $(this).val());
        var c = $("#" + active_element).data("field_properties");
        $.ajax({
            type: "POST",
            async: true,
            url: "/backend.php/forms/stringtotime",
            data: {
                default_date: $(this).val(),
                date_format: c.type
            },
            cache: false,
            global: false,
            dataType: "json",
            error: function(g, d, f) {},
            success: function(e) {
                if (e.status == "ok") {
                    if (c.type == "date") {
                        var d = e.default_date.split("-");
                        $("#element_" + c.id + "_1").val(d[1]);
                        $("#element_" + c.id + "_2").val(d[0]);
                        $("#element_" + c.id + "_3").val(d[2])
                    } else {
                        if (c.type == "europe_date") {
                            var d = e.default_date.split("-");
                            $("#element_" + c.id + "_1").val(d[0]);
                            $("#element_" + c.id + "_2").val(d[1]);
                            $("#element_" + c.id + "_3").val(d[2])
                        }
                    }
                }
            }
        })
    });
    $("#element_default_time").bind("keyup mouseout", function() {
        set_properties("element_default_value", $(this).val());
        var c = $("#" + active_element).data("field_properties");
        $.ajax({
            type: "POST",
            async: true,
            url: "/backend.php/forms/stringtotime",
            data: {
                default_time: $(this).val(),
                time_24hour: c.time_24hour
            },
            cache: false,
            global: false,
            dataType: "json",
            error: function(g, d, f) {},
            success: function(e) {
                if (e.status == "ok") {
                    var d = e.default_time.split("-");
                    $("#element_" + c.id + "_1").val(d[0]);
                    $("#element_" + c.id + "_2").val(d[1]);
                    $("#element_" + c.id + "_3").val(d[2]);
                    $("#element_" + c.id + "_4").val(d[3])
                }
            }
        })
    });
    $("#prop_other_choices_label").bind("keyup mouseout", function() {
        set_properties("element_choice_other_label", $(this).val())
    });
    $("#prop_choices_other_checkbox").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_choice_has_other", c)
    });
    $("#prop_choices_randomize").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = "random"
        } else {
            c = ""
        }
        set_properties("constraint", c)
    });
    $("#prop_text_as_password").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = "password"
        } else {
            c = ""
        }
        set_properties("constraint", c)
    });

    //Set Remote check values for text fields
    $("#prop_remote_url").bind("keyup mouseout", function () {
        set_properties("prop_remote_url", $(this).val())
    });

    $("#prop_remote_criteria").bind("change", function () {
        set_properties("prop_remote_criteria", $(this).val())
    });

    $("#prop_remote_value").bind("keyup mouseout", function () {
        set_properties("prop_remote_value", $(this).val())
    });

    $("#prop_field_value").bind("keyup mouseout", function () {
        set_properties("prop_field_value", $(this).val())
    });

    $("#prop_field_error_message").bind("keyup mouseout", function () {
        set_properties("prop_field_error_message", $(this).val())
    });

    $("#prop_remote_username").bind("keyup mouseout", function () {
        set_properties("prop_remote_username", $(this).val())
    });

    $("#prop_remote_password").bind("keyup mouseout", function () {
        set_properties("prop_remote_password", $(this).val())
    });

    $("#prop_remote_server_field").bind("keyup mouseout", function () {
        set_properties("prop_remote_server_field", $(this).val())
    });

    $("#prop_remote_url_pr").bind("keyup mouseout", function () {
        set_properties("prop_remote_url", $(this).val())
    });

    $("#prop_remote_username_pr").bind("keyup mouseout", function () {
        set_properties("prop_remote_username", $(this).val())
    });

    $("#prop_remote_password_pr").bind("keyup mouseout", function () {
        set_properties("prop_remote_password", $(this).val())
    });

    $("#prop_remote_server_field_pr").bind("keyup mouseout", function () {
        set_properties("prop_remote_server_field", $(this).val())
    });

    $("#prop_remote_value_pr").bind("change", function() {
        set_properties("prop_remote_value", $(this).val())
    });

     $("#price_class").bind("change", function() {
        set_properties("price_class", $(this).val())
    });

    $("#element_enable_placeholder,#element_enable_placeholder_text").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_enable_placeholder", c)
    });
    $("#prop_section_email_display").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_section_display_in_email", c)
    });
    $("#prop_section_size").bind("change", function() {
        set_properties("element_size", $(this).val())
    });
    $("#prop_section_enable_scroll").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_section_enable_scroll", c)
    });
    $("#prop_time_showsecond").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_time_showsecond", c)
    });
    $("#prop_time_24hour").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_time_24hour", c)
    });
    $("#element_countries").bind("change", function() {
        set_properties("element_default_value", $(this).val())
    });
    $("#money_format").bind("change", function() {
        set_properties("constraint", $(this).val())
    });
    $("#prop_address_hideline2").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_address_hideline2", c)
    });
    $("#prop_address_us_only").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_address_us_only", c)
    });
    $("#prop_date_range").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_date_enable_range", c)
    });
    $("#prop_date_range_format_switcher").bind("change", function() {
        set_properties("element_date_range_format", $(this).val())
    });
    $("#date_range_min_relative").bind("keyup mouseout", function() {
        var d = parseInt($(this).val());
        if (d < 0) {
            var c = ("0000" + Math.abs(d)).slice(-4) + "-00-01"
        } else {
            var c = ("0000" + d).slice(-4) + "-00-00"
        }
        set_properties("element_date_range_min", c)
    });
    $("#date_range_max_relative").bind("keyup mouseout", function() {
        var c = parseInt($(this).val());
        if (c < 0) {
            var d = ("0000" + Math.abs(c)).slice(-4) + "-00-01"
        } else {
            var d = ("0000" + c).slice(-4) + "-00-00"
        }
        set_properties("element_date_range_max", d)
    });
    $("#prop_date_selection_limit").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_date_enable_selection_limit", c)
    });
    $("#prop_date_past_future_selection").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_date_disable_past_future", c)
    });
    $("#date_selection_max").bind("keyup mouseout", function() {
        set_properties("element_date_selection_max", $(this).val())
    });
    $("#prop_date_disable_specific").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_date_disable_specific", c)
    });
    $("#date_disabled_list").bind("keyup mouseout", function() {
        set_properties("element_date_disabled_list", $(this).val())
    });
    $("#prop_date_past_future").bind("change", function() {
        set_properties("date_past_future", $(this).val())
    });
    $("#prop_date_disable_weekend").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_date_disable_weekend", c)
    });
    $("#date_type").bind("change", function() {
        set_properties("element_type", $(this).val())
    });
    $("#linked_picker_range_min").datepick({
        onSelect: update_date_range_min_linked,
        showTrigger: "#date_range_min_pick_img"
    });
    $("#date_range_min_mm,#date_range_min_dd,#date_range_min_yyyy").bind("blur mouseout", function() {
        var c = parseInt($("#date_range_min_dd").val(), 10);
        var f = parseInt($("#date_range_min_mm").val(), 10) - 1;
        var d = parseInt($("#date_range_min_yyyy").val(), 10);
        if (!isNaN(c) && !isNaN(f) && !isNaN(d) && (c != 0) && (f != -1)) {
            $("#linked_picker_range_min").datepick("setDate", new Date(d, f, c));
            var e = $("#date_range_min_yyyy").val() + "-" + $("#date_range_min_mm").val() + "-" + $("#date_range_min_dd").val();
            set_properties("element_date_range_min", e)
        }
    });
    $("#linked_picker_range_max").datepick({
        onSelect: update_date_range_max_linked,
        showTrigger: "#date_range_max_pick_img"
    });
    $("#date_range_max_mm,#date_range_max_dd,#date_range_max_yyyy").bind("blur mouseout", function() {
        var f = parseInt($("#date_range_max_dd").val(), 10);
        var e = parseInt($("#date_range_max_mm").val(), 10) - 1;
        var c = parseInt($("#date_range_max_yyyy").val(), 10);
        if (!isNaN(f) && !isNaN(e) && !isNaN(c) && (f != 0) && (e != -1)) {
            $("#linked_picker_range_max").datepick("setDate", new Date(c, e, f));
            var d = $("#date_range_max_yyyy").val() + "-" + $("#date_range_max_mm").val() + "-" + $("#date_range_max_dd").val();
            set_properties("element_date_range_max", d)
        }
    });
    $("#date_disabled_list").datepick({
        multiSelect: 999,
        multiSeparator: ", ",
        showTrigger: "#date_disable_specific_pick_img",
        onClose: function(e) {
            var c = e.length;
            for (var d = 0; d < c; d++) {
                e[d] = $.datepick.formatDate("mm/dd/yyyy", e[d])
            }
            var f = e.join(", ");
            set_properties("element_date_disabled_list", f)
        }
    });
    $("#prop_file_enable_type_limit").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_file_enable_type_limit", c)
    });
    $("#prop_file_block_or_allow").bind("change", function() {
        set_properties("element_file_block_or_allow", $(this).val())
    });
    $("#file_type_list").bind("keyup mouseout", function() {
        set_properties("element_file_type_list", $(this).val())
    });
    $("#prop_file_as_attachment").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_file_as_attachment", c)
    });
    $("#prop_file_enable_advance").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_file_enable_advance", c)
    });
    $("#prop_file_auto_upload").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_file_auto_upload", c)
    });
    $("#prop_file_multi_upload").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_file_enable_multi_upload", c)
    });
    $("#file_max_selection").bind("keyup mouseout change", function() {
        set_properties("element_file_max_selection", $(this).val())
    });
    $("#prop_file_limit_size").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_file_enable_size_limit", c)
    });
    $("#file_size_max").bind("keyup mouseout", function() {
        set_properties("element_file_size_max", $(this).val())
    });
	//OTB Start File QR Code verification
    $("#prop_mark_file_with_qr_code").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = 1
        } else {
            b = 0
        }
        set_properties("prop_mark_file_with_qr_code", b)
    });
    $("#prop_file_qr_all_pages").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = 1
        } else {
            b = 0
        }
        set_properties("prop_file_qr_all_pages", b)
    });
    $("#prop_file_qr_page_position").bind("change", function () {
        var b;
        if ($(this).val() == "top_left") {
            b = "top_left"
        } else if ($(this).val() == "top_right") {
            b = "top_right"
        } else if ($(this).val() == "bottom_left") {
            b = "bottom_left"
        } else if ($(this).val() == "bottom_right") {
            b = "bottom_right"
        }
        set_properties("prop_file_qr_page_position", b)
    });
    $("#prop_file_qr_users").bind("change", function () {
        var b;
        if ($(this).val() == "clients") {
            b = "clients"
        } else if ($(this).val() == "reviewers") {
            b = "reviewers"
        } else if ($(this).val() == "both") {
            b = "both"
        }
        set_properties("prop_file_qr_users", b)
    });
	//OTB End File QR Code verification
	//OTB Start Copy notification to other contacts
    $("#element_notify_contact").bind("change", function () {
        var b;
        if ($(this).prop("checked") == true) {
            b = 1
        } else {
            b = 0
        }
        set_properties("element_notify_contact", b)
    });
	//OTB End Copy notification to other contacts
    $("#prop_number_enable_quantity").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_number_enable_quantity", c)
    });
    $("#prop_number_quantity_link").bind("change", function() {
        set_properties("element_number_quantity_link", $(this).val())
    });
    $("#prop_submit_use_text").bind("click", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 0
        } else {
            c = 1
        }
        set_properties("submit_use_image", c)
    });
    $("#prop_submit_use_image").bind("click", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("submit_use_image", c)
    });
    $("#submit_primary_text").bind("keyup mouseout", function() {
        set_properties("submit_primary_text", $(this).val())
    });
    $("#submit_secondary_text").bind("keyup mouseout", function() {
        set_properties("submit_secondary_text", $(this).val())
    });
    $("#submit_primary_img").bind("keyup mouseout", function() {
        set_properties("submit_primary_img", $(this).val())
    });
    $("#submit_secondary_img").bind("keyup mouseout", function() {
        set_properties("submit_secondary_img", $(this).val())
    });
    $("#element_matrix_row").delegate("input.text", "keyup", function(g) {
        var i = $(this);
        var h = i.attr("id").split("_");
        var c = h[1];
        var f = "&nbsp;";
        var d = $("#" + active_element);
        if (g.keyCode == 13) {
            if ($("#element_matrix_row li.li_mr_temp_holder").length == 0) {
                add_matrix_row(i)
            }
        } else {
            if (i.parent().hasClass("li_mr_temp_holder") == false) {
                if (i.val() != "") {
                    f = i.val()
                }
                $("#mr_" + c + " td.first_col").html(f);
                d.data("field_properties").options[c].row_title = i.val()
            }
        }
    });
    $("#element_matrix_row").delegate("img.add_choice", "click", function(c) {
        var d = $(this);
        if ($("#element_matrix_row li.li_mr_temp_holder").length == 0) {
            add_matrix_row(d)
        }
    });
    $("#element_matrix_row").delegate("img.del_choice", "click", function(c) {
        var d = $(this);
        delete_matrix_row(d)
    });
    $("#element_matrix_column").delegate("input.text", "keyup", function(h) {
        var j = $(this);
        var i = j.attr("id").split("_");
        var f = i[1];
        var g = "&nbsp;";
        var d = $("#" + active_element);
        var c = d.data("field_properties").id;
        if (h.keyCode == 13) {
            add_matrix_column(j)
        } else {
            if (j.val() != "") {
                g = j.val()
            }
            $("#mc_" + c + "_" + f).html(g);
            d.data("field_properties").options[c].column_data[f].column_title = j.val()
        }
    });
    $("#element_matrix_column").delegate("img.add_choice", "click", function(c) {
        var d = $(this);
        add_matrix_column(d)
    });
    $("#element_matrix_column").delegate("img.del_choice", "click", function(c) {
        var d = $(this);
        delete_matrix_column(d)
    });
    $("#prop_matrix_allow_multiselect").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        set_properties("element_matrix_allow_multiselect", c)
    });
    $("#form_title").bind("keyup mouseout", function() {
        set_form_properties("form_title", $(this).val())
    });
    $("#form_description").bind("keyup mouseout", function() {
        set_form_properties("form_description", $(this).val())
    });
    $("#form_idn").bind("keyup mouseout", function () {
        set_form_properties("form_idn", $(this).val())
    });
    $("#form_code").bind("keyup mouseout", function () {
        set_form_properties("form_code", $(this).val())
    });
    $("#form_success_message_option").click(function() {
        set_form_properties("form_redirect_enable", 0)
    });
    $("#form_redirect_option").click(function() {
        set_form_properties("form_redirect_enable", 1)
    });
    $("#form_success_message").bind("keyup mouseout", function() {
        set_form_properties("form_success_message", $(this).val())
    });
    $("#form_redirect_url").bind("keyup mouseout", function() {
        set_form_properties("form_redirect", $(this).val())
    });
    $("#form_review,#form_resume").bind("change", function() {
        var c;
        if ($(this).prop("checked") == true) {
            c = 1
        } else {
            c = 0
        }
        if ($(this).attr("id") == "form_review") {
            set_form_properties("form_review", c)
        } else {
            set_form_properties("form_resume_enable", c)
        }
    });
    $("#form_review_title").bind("keyup mouseout", function() {
        set_form_properties("form_review_title", $(this).val())
    });
    $("#form_review_description").bind("keyup mouseout", function() {
        set_form_properties("form_review_description", $(this).val())
    });
    $("#form_review_use_text").click(function() {
        set_form_properties("form_review_use_image", 0)
    });
    $("#form_review_use_image").click(function() {
        set_form_properties("form_review_use_image", 1)
    });
    $("#review_primary_text").bind("keyup mouseout", function() {
        set_form_properties("review_primary_text", $(this).val())
    });
    $("#review_primary_img").bind("keyup mouseout", function() {
        set_form_properties("review_primary_img", $(this).val())
    });
    $("#review_secondary_text").bind("keyup mouseout", function() {
        set_form_properties("review_secondary_text", $(this).val())
    });
    $("#review_secondary_img").bind("keyup mouseout", function() {
        set_form_properties("review_secondary_img", $(this).val())
    });
    $("#form_resume_subject").bind("keyup mouseout", function() {
        set_form_properties("form_resume_subject", $(this).val())
    });
    $("#form_resume_content").bind("keyup mouseout", function() {
        set_form_properties("form_resume_content", $(this).val())
    });
    $("#form_resume_from_name").bind("keyup mouseout", function() {
        set_form_properties("form_resume_from_name", $(this).val())
    });
    $("#form_resume_from_email_address").bind("keyup mouseout", function() {
        set_form_properties("form_resume_from_email_address", $(this).val())
    });
    $("#form_password_option").bind("change", function() {
        $("#form_header").data("form_properties").password = "";
        $("#form_password_data").val("");
        if ($(this).prop("checked") == true) {
            $("#form_password").slideDown()
        } else {
            $("#form_password").fadeOut("slow")
        }
    });
    $("#form_password_data").bind("keyup mouseout", function() {
        set_form_properties("form_password", $(this).val())
    });
    $("#form_captcha").bind("change", function() {
        if ($(this).prop("checked") == true) {
            $("#form_header").data("form_properties").captcha = 1;
            $("#form_captcha_type_option").slideDown()
        } else {
            $("#form_header").data("form_properties").captcha = 0;
            $("#form_captcha_type_option").fadeOut("slow")
        }
    });
    $("#form_captcha_type").bind("change", function() {
        $("#form_header").data("form_properties").captcha_type = $(this).val()
    });
    $("#form_unique_ip").bind("change", function() {
        if ($(this).prop("checked") == true) {
            $("#form_unique_ip_div").slideDown();
            $("#form_header").data("form_properties").unique_ip = 1
        } else {
            $("#form_unique_ip_div").fadeOut("slow");
            $("#form_header").data("form_properties").unique_ip = 0
        }
    });
    $("#form_unique_ip_maxcount").bind("keyup mouseout", function() {
        set_form_properties("form_unique_ip_maxcount", $(this).val())
    });
    $("#form_unique_ip_period").bind("change", function() {
        $("#form_header").data("form_properties").unique_ip_period = $(this).val()
    });
    $("#form_name_hide").bind("change", function() {
        if ($(this).prop("checked") == true) {
            $("#form_header").data("form_properties").name_hide = 1
        } else {
            $("#form_header").data("form_properties").name_hide = 0
        }
    });
    $("#form_limit_option").bind("change", function() {
        if ($(this).prop("checked") == true) {
            $("#form_limit_div").slideDown();
            $("#form_header").data("form_properties").limit_enable = 1
        } else {
            $("#form_limit_div").fadeOut("slow");
            $("#form_header").data("form_properties").limit_enable = 0
        }
    });
    $("#form_limit").bind("keyup mouseout", function() {
        set_form_properties("form_limit", $(this).val())
    });
    $("#form_prop_toggle_a,#form_prop_toggle_img").click(function() {
        if ($("#form_prop_toggle_a").text() == "show more options") {
            $("#form_prop_toggle_a").text("show less options");
            $("#form_prop_toggle_img").attr("src", "/form_builder/images/icons/resultset_up.gif");
            $("#all_form_properties .advanced_prop").not("#form_prop_review").not("#form_prop_resume").show();
            if ($("#form_review").prop("checked") == true) {
                $("#form_prop_review").show()
            }
            if ($("#form_resume").prop("checked") == true) {
                $("#form_prop_resume").show()
            }
            adjust_main_height();
            $("#form_prop_toggle_a").data("expand", 1)
        } else {
            $("#form_prop_toggle_a").text("show more options");
            $("#form_prop_toggle_img").attr("src", "/form_builder/images/icons/resultset_next.gif");
            $("#form_prop_review,#form_prop_resume").hide();
            $("#all_form_properties .advanced_prop").slideUp("slow", function() {
                adjust_main_height()
            });
            $("#form_prop_toggle_a").data("expand", 0)
        }
        return false
    });
    $("#form_label_alignment").bind("change", function() {
        set_form_properties("form_label_alignment", $(this).val())
    });
    $("#form_language").bind("change", function() {
        set_form_properties("form_language", $(this).val())
    });
    $("#form_stage").bind("change", function () {
        set_form_properties("form_stage", $(this).val())
    });
    $("#form_group").bind("change", function () {
        set_form_properties("form_group", $(this).val())
    });
    $("#form_department").bind("change", function () {
        set_form_properties("form_department", $(this).val())
    });
    $("#form_department_stage").bind("change", function () {
        set_form_properties("form_department_stage", $(this).val())
    });
    $("#form_type").bind("change", function () {
        set_form_properties("form_type", $(this).val());
        if($(this).val() == 1)
        {
            $("#form_application_properties").show();
            $("#form_commentsheet_properties").hide();
        }
        else if($(this).val() == 2)
        {
            $("#form_application_properties").hide();
            $("#form_commentsheet_properties").show();
        }
        else
        {
           $("#form_application_properties").hide();
           $("#form_commentsheet_properties").hide();
        }
    });
    $("#table_name").bind("change", function() {
        set_properties("table_name", $(this).val())
    });
    $("#field_value").bind("change", function() {
        set_properties("field_value", $(this).val())
    });
    $("#field_name").bind("change", function() {
        set_properties("field_name", $(this).val())
    });
    $("#option_query").bind("change", function() {
        set_properties("option_query", $(this).val())
    });
    $("#field_value_option_query").bind("change", function() {
        set_properties("field_value", $(this).val())
    });
    $("#field_name_option_query").bind("change", function() {
        set_properties("field_name", $(this).val())
    });
    $("#existing_form").bind("change", function() {
        set_properties("existing_form", $(this).val())
    });
    $("#existing_stage").bind("change", function() {
        set_properties("existing_stage", $(this).val())
    });
    $("#pagination_style_steps,#pagination_style_percentage,#pagination_style_disabled").bind("click", function() {
        var d;
        var c = $(this).attr("id");
        if (c == "pagination_style_steps") {
            d = "steps"
        } else {
            if (c == "pagination_style_percentage") {
                d = "percentage"
            } else {
                if (c == "pagination_style_disabled") {
                    d = "disabled"
                }
            }
        }
        set_form_properties("form_pagination_type", d)
    });
    $("#pagination_title_list").delegate("input.text", "keyup", function(j) {
        var h = $(this);
        var k = h.attr("id").split("_");
        var c = parseInt(k[1]);
        var g = c + 1;
        var i = $("#form_header").data("form_properties");
        var d = $("#form_builder_sortable > li.page_break");
        var f = i.pagination_type;
        if (f == "steps") {
            if (j.keyCode == 13) {
                $("#pagetitleinput_" + g).focus().select()
            } else {
                $("#page_title_" + c).text(h.val())
            }
        } else {
            if (f == "percentage") {
                if (j.keyCode == 13) {
                    $("#pagetitleinput_" + g).focus().select()
                } else {
                    if (c == 1) {
                        $("#page_title_1").html("Page 1 of " + d.length + " - " + h.val())
                    }
                }
            }
        }
        d.eq(c - 1).data("field_properties").page_title = h.val();
        d.eq(c - 1).removeClass("synched")
    });
    $("#form_schedule_enable").bind("change", function() {
        if ($(this).prop("checked") == true) {
            $("#form_header").data("form_properties").schedule_enable = 1;
            $("#form_prop_scheduling_start").fadeIn();
            $("#form_prop_scheduling_end").fadeIn()
        } else {
            $("#form_header").data("form_properties").schedule_enable = 0;
            $("#form_prop_scheduling_start").fadeOut("slow");
            $("#form_prop_scheduling_end").fadeOut("slow")
        }
    });
    $("#linked_picker_scheduling_start").datepick({
        onSelect: update_scheduling_start_linked,
        showTrigger: "#scheduling_start_pick_img"
    });
    $("#scheduling_start_mm,#scheduling_start_dd,#scheduling_start_yyyy").bind("blur mouseout", function() {
        var c = parseInt($("#scheduling_start_dd").val(), 10);
        var f = parseInt($("#scheduling_start_mm").val(), 10) - 1;
        var e = parseInt($("#scheduling_start_yyyy").val(), 10);
        if (!isNaN(c) && !isNaN(f) && !isNaN(e) && (c != 0) && (f != -1)) {
            $("#linked_picker_scheduling_start").datepick("setDate", new Date(e, f, c));
            var d = $("#scheduling_start_yyyy").val() + "-" + $("#scheduling_start_mm").val() + "-" + $("#scheduling_start_dd").val();
            $("#form_header").data("form_properties").schedule_start_date = d
        }
    });
    $("#scheduling_start_hour,#scheduling_start_minute,#scheduling_start_ampm").bind("change", function() {
        $("#form_header").data("form_properties").schedule_start_hour = $("#scheduling_start_hour").val() + ":" + $("#scheduling_start_minute").val() + ":" + $("#scheduling_start_ampm").val()
    });
    $("#linked_picker_scheduling_end").datepick({
        onSelect: update_scheduling_end_linked,
        showTrigger: "#scheduling_end_pick_img"
    });
    $("#scheduling_end_mm,#scheduling_end_dd,#scheduling_end_yyyy").bind("blur mouseout", function() {
        var c = parseInt($("#scheduling_end_dd").val(), 10);
        var f = parseInt($("#scheduling_end_mm").val(), 10) - 1;
        var d = parseInt($("#scheduling_end_yyyy").val(), 10);
        if (!isNaN(c) && !isNaN(f) && !isNaN(d) && (c != 0) && (f != -1)) {
            $("#linked_picker_scheduling_end").datepick("setDate", new Date(d, f, c));
            var e = $("#scheduling_end_yyyy").val() + "-" + $("#scheduling_end_mm").val() + "-" + $("#scheduling_end_dd").val();
            $("#form_header").data("form_properties").schedule_end_date = e
        }
    });
    $("#scheduling_end_hour,#scheduling_end_minute,#scheduling_end_ampm").bind("change", function() {
        $("#form_header").data("form_properties").schedule_end_hour = $("#scheduling_end_hour").val() + ":" + $("#scheduling_end_minute").val() + ":" + $("#scheduling_end_ampm").val()
    });
    $("#form_custom_script_enable").bind("change", function() {
        if ($(this).prop("checked") == true) {
            $("#form_header").data("form_properties").custom_script_enable = 1;
            $("#form_custom_script_div").slideDown()
        } else {
            $("#form_header").data("form_properties").custom_script_enable = 0;
            $("#form_custom_script_div").fadeOut("slow")
        }
    });
    $("#form_custom_script_url").bind("keyup mouseout", function() {
        set_form_properties("form_custom_script_url", $(this).val())
    });
    $("#social a").click(function() {
        var d = $(this).attr("id");
        var c;
        switch (d) {
            case "a_single_line_text":
                c = "text";
                break;
            case "a_number":
                c = "number";
                break;
            case "a_paragraph_text":
                c = "textarea";
                break;
            case "a_checkboxes":
                c = "checkbox";
                break;
            case "a_multiple_choice":
                c = "radio";
                break;
            case "a_drop_down":
                c = "select";
                break;
            case "a_name":
                c = "simple_name";
                break;
            case "a_date":
                c = "date";
                break;
            case "a_time":
                c = "time";
                break;
            case "a_phone":
                c = "phone";
                break;
            case "a_address":
                c = "address";
                break;
            case "a_website":
                c = "url";
                break;
            case "a_price":
                c = "money";
                break;
            case "a_email":
                c = "email";
                break;
            case "a_section_break":
                c = "section";
                break;
            case "a_file_upload":
                c = "file";
                break;
            case "a_page_break":
                c = "page_break";
                break;
            case "a_matrix":
                c = "matrix";
                break;
            case "a_signature":
                c = "signature";
                break;
			//OTB patch - Plinth Areas
			case "a_plinth_area":
				c = "plinth_area";
				break;
			case "a_total_plinth_area":
				c = "total_plinth_area";
				break;
            default:
                c = "text";
                break
        }
        add_element(c);
        return false
    });
    $("#dialog-message").dialog({
        modal: true,
        autoOpen: false,
        closeOnEscape: false,
        width: 600,
        draggable: false,
        resizable: false,
        position: ["center", "center"],
        open: function() {
            $(this).next().find("button").blur()
        },
        buttons: [{
            text: "Close",
            "class": "bb_button bb_small bb_green",
            click: function() {
                $("#bottom_bar_loader").hide();
                $("#bottom_bar_content").show();
                $(this).dialog("close")
            }
        }]
    });
    $("#dialog-warning").dialog({
        modal: true,
        autoOpen: false,
        closeOnEscape: false,
        width: 600,
        position: ["center", "center"],
        draggable: false,
        resizable: false,
        open: function() {
            $(this).next().find("button").blur()
        },
        buttons: [{
            text: "OK",
            "class": "bb_button bb_small bb_green",
            click: function() {
                $(this).dialog("close")
            }
        }]
    });
    $("#dialog-confirm-field-delete").dialog({
        modal: true,
        autoOpen: false,
        closeOnEscape: false,
        width: 550,
        resizable: false,
        draggable: false,
        position: ["center", "center"],
        open: function() {
            $("#btn-field-delete-ok").blur()
        },
        buttons: [{
            text: "Yes. Delete this field",
            id: "btn-field-delete-ok",
            "class": "bb_button bb_small bb_green",
            click: function() {
                var d = $("#" + active_element);
                var c = d.html();
                var e = $("#form_header").data("form_properties").id;
                d.html('<img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> deleting field ...').addClass("delete_processing");
                $(this).dialog("close");
                $.ajax({
                    type: "POST",
                    async: true,
                    url: "/backend.php/forms/deletelivefield",
                    data: {
                        form_id: e,
                        element_id: d.data("field_properties").id
                    },
                    cache: false,
                    global: true,
                    dataType: "json",
                    error: function(h, f, g) {
                        d.html(c).removeClass("delete_processing")
                    },
                    success: function(f) {
                        if (f.status == "ok") {
                            $("#li_" + f.element_id).fadeOut("normal", function() {
                                $(this).remove();
                                reorganize_page_break();
                                $("#form_builder_sortable").sortable("refreshPositions")
                            });
                            active_element = null;
                            $("#selected_field_image").css("visibility", "hidden");
                            $("#bottom_bar_field_action").fadeOut("fast", function() {
                                $("#bottom_bar_content").animate({
                                    "margin-left": "225px"
                                }, {
                                    duration: 200,
                                    queue: false
                                })
                            });
                            $("#builder_tabs").tabs("select", 0)
                        } else {
                            $(this).dialog("close");
                            d.html(c).removeClass("delete_processing");
                            $("#dialog-message").dialog("open")
                        }
                    }
                })
            }
        }, {
            text: "Cancel",
            id: "btn-field-delete-cancel",
            "class": "btn_secondary_action",
            click: function() {
                $(this).dialog("close")
            }
        }]
    });
    $("#dialog-confirm-choice-delete").dialog({
        modal: true,
        autoOpen: false,
        closeOnEscape: false,
        width: 550,
        resizable: false,
        draggable: false,
        position: ["center", "center"],
        open: function() {
            $("#btn-choice-delete-ok").blur()
        },
        buttons: [{
            text: "Yes. I understand",
            id: "btn-choice-delete-ok",
            "class": "bb_button bb_small bb_green",
            click: function() {
                $(this).dialog("close")
            }
        }, {
            text: "Cancel",
            id: "btn-field-delete-choice-cancel",
            "class": "btn_secondary_action",
            click: function() {
                $(this).dialog("close")
            }
        }]
    });
    $("#dialog-form-saved").dialog({
        modal: true,
        autoOpen: false,
        closeOnEscape: false,
        width: 450,
        resizable: false,
        draggable: false,
        position: ["center", "center"],
        open: function() {
            $("#btn-form-saved-ok").blur()
        },
        buttons: [{
            text: "Yes. Continue editing",
            id: "btn-form-saved-ok",
            "class": "bb_button bb_small bb_green",
            click: function() {
                var c = $("#form_header").data("form_properties").id;
                var d = new Date().getTime();
                window.location.replace("/backend.php/forms/form?id=" + c + "&t=" + d)
            }
        }, {
            text: "I'm finished",
            id: "btn-form-saved-cancel",
            "class": "btn_secondary_action",
            click: function() {
                var c = $("#form_header").data("form_properties").id;
                window.location.replace("/backend.php/forms/index?id=" + c + "&hl=1")
            }
        }]
    });
    $("#dialog-insert-choices").dialog({
        modal: true,
        autoOpen: false,
        closeOnEscape: false,
        width: 515,
        position: ["center", "center"],
        draggable: false,
        resizable: false,
        open: function() {
            $(this).next().find("button").blur()
        },
        buttons: [{
            text: "Add Choices",
            "class": "bb_button bb_small bb_green",
            click: function() {
                if ($("#bulk_insert_choices").val() == "") {
                    $(this).dialog("close")
                } else {
                    var c = $("#bulk_insert_choices").val().split("\n");
                    var k = $("#" + active_element);
                    var f = parseInt(k.data("field_properties").last_option_id) + 1;
                    var g = f - 1;
                    var i = $("#element_choices > li").size() + 1;
                    var h = k.data("field_properties").options;
                    var j = "";
                    var d = k.data("field_properties").id;
                    var e = k.data("field_properties").type;
                    $.each(c, function(l, m) {
                        h[f] = {
                            position: i,
                            option: m,
                            is_default: 0,
                            is_db_live: "0"
                        };
                        if (e == "radio") {
                            j += '<span><input type="radio" value="1" class="element radio" name="element_' + d + '" id="element_' + d + "_" + f + '"><label for="element_' + d + "_" + f + '" class="choice">' + m + "</label></span>"
                        } else {
                            if (e == "checkbox") {
                                j += '<span><input type="checkbox" value="1" class="element checkbox" name="element_' + d + '" id="element_' + d + "_" + f + '"><label for="element_' + d + "_" + f + '" class="choice">' + m + "</label></span>"
                            } else {
                                if (e == "select") {
                                    j += '<option value="' + f + '">' + m + "</option>"
                                }
                            }
                        }
                        f++;
                        i++
                    });
                    if (e == "radio") {
                        if (k.data("field_properties").choice_has_other == 1) {
                            $("#element_" + d + "_other").parent().before(j)
                        } else {
                            $("#li_" + d + " > div").append(j)
                        }
                    } else {
                        if (e == "checkbox") {
                            if (k.data("field_properties").choice_has_other == 1) {
                                $("#element_" + d + "_other").parent().before(j)
                            } else {
                                $("#li_" + d + " > div").append(j)
                            }
                        } else {
                            if (e == "select") {
                                $("#element_" + d).append(j)
                            }
                        }
                    }
                    k.data("field_properties").options = h;
                    k.data("field_properties").last_option_id = f - 1;
                    $(this).dialog("close");
                    load_properties("#" + active_element)
                }
            }
        }, {
            text: "Cancel",
            "class": "btn_secondary_action",
            click: function() {
                $(this).dialog("close")
            }
        }]
    });
    $("#dialog-insert-matrix-rows").dialog({
        modal: true,
        autoOpen: false,
        closeOnEscape: false,
        width: 500,
        draggable: false,
        resizable: false,
        open: function() {
            $(this).next().find("button").blur()
        },
        position: ["center", "center"],
        buttons: [{
            text: "Add Rows",
            "class": "bb_button bb_small bb_green",
            click: function() {
                if ($("#bulk_insert_rows").val() == "") {
                    $(this).dialog("close")
                } else {
                    var e = $("#form_header").data("form_properties").id;
                    var g = $("#bulk_insert_rows").val().split("\n");
                    var d = $("#" + active_element);
                    var c = $("#form_builder_sortable > li").not(".li_pagination").size() + 1;
                    var h = c + $("#form_builder_sortable li.matrix table tr").length;
                    var i = $("#" + active_element + " table th").length;
                    var f = '<tr id="mr_temp_' + h + '" class="matrix_row_processing"><td colspan="' + i + '"><img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> creating new rows ...</td></tr>';
                    $("#" + active_element + " table tbody").append(f);
                    $.ajax({
                        type: "POST",
                        async: true,
                        url: "/backend.php/forms/addmatrixrow",
                        data: {
                            form_id: e,
                            row_holder_id: "mr_temp_" + h,
                            allow_multiselect: d.data("field_properties").matrix_allow_multiselect,
                            matrix_parent_id: d.data("field_properties").id,
                            position: $("#" + active_element + " table tbody tr").length,
                            total_column: i - 1,
                            column_data: d.data("field_properties").options[d.data("field_properties").id].column_data,
                            rows_titles: g
                        },
                        cache: false,
                        global: true,
                        dataType: "json",
                        error: function(l, j, k) {
                            $("#mr_temp_" + h).remove()
                        },
                        success: function(k) {
                            if (k.status == "ok") {
                                var j = d.data("field_properties").options;
                                $.each(k.new_row_data, function(l, m) {
                                    j[l] = m
                                });
                                d.data("field_properties").options = j;
                                $("#" + k.row_holder_id).replaceWith(k.new_row_markup);
                                load_properties("#" + active_element)
                            } else {
                                $("#dialog-message").dialog("open");
                                $("#mr_temp_" + h).remove()
                            }
                        }
                    });
                    $(this).dialog("close")
                }
            }
        }, {
            text: "Cancel",
            "class": "btn_secondary_action",
            click: function() {
                $(this).dialog("close")
            }
        }]
    });
    $("#dialog-insert-matrix-columns").dialog({
        modal: true,
        autoOpen: false,
        closeOnEscape: false,
        draggable: false,
        resizable: false,
        open: function() {
            $(this).next().find("button").blur()
        },
        width: 515,
        position: ["center", "center"],
        buttons: [{
            text: "Add Columns",
            "class": "bb_button bb_small bb_green",
            click: function() {
                if ($("#bulk_insert_columns").val() == "") {
                    $(this).dialog("close")
                } else {
                    var o = $("#form_header").data("form_properties").id;
                    var m = $("#" + active_element + " table th").length;
                    var j = $("#bulk_insert_columns").val().split("\n");
                    var t = $("#" + active_element);
                    var d = t.data("field_properties").id;
                    var n = parseInt(t.data("field_properties").last_option_id) + 1;
                    var h = n;
                    t.data("field_properties").last_option_id = n + j.length - 1;
                    var f = "";
                    var u = n;
                    $.each(j, function(v, w) {
                        f += '<th scope="col" id="mc_' + d + "_" + u + '">' + w + "</th>";
                        u++
                    });
                    $("#" + active_element + " table thead tr").append(f);
                    var p = "";
                    if (t.data("field_properties").matrix_allow_multiselect == 1) {
                        p = "checkbox"
                    } else {
                        p = "radio"
                    }
                    var r = new Array();
                    var e = "";
                    var s = "";
                    $("#" + active_element + " table tbody").find("tr").each(function() {
                        r = $(this).attr("id").split("_");
                        e = r[1];
                        s = "";
                        u = n;
                        $.each(j, function(v, w) {
                            s += '<td><input type="' + p + '" value="' + u + '" name="element_' + e + '" id="element_' + e + "_" + u + '"></td>';
                            u++
                        });
                        n++;
                        $(this).append(s)
                    });
                    var k = $("#" + active_element + " table th").length;
                    var q = 100 / k;
                    var g = 2 * q;
                    g = Math.round(g);
                    var i = (100 - g) / (k - 1);
                    i = Math.round(i);
                    $("#" + active_element + " table th:eq(0)").css("width", g + "%");
                    $("#" + active_element + " table th:gt(0)").css("width", i + "%");
                    var l = t.data("field_properties").options[d].column_data;
                    var c = m;
                    $.each(j, function(v, w) {
                        l[h] = {
                            column_title: w,
                            is_db_live: 0,
                            position: c
                        };
                        c++;
                        h++
                    });
                    t.data("field_properties").options[d].column_data = l;
                    load_properties("#" + active_element);
                    $(this).dialog("close")
                }
            }
        }, {
            text: "Cancel",
            "class": "btn_secondary_action",
            click: function() {
                $(this).dialog("close")
            }
        }]
    });
    $(document).ajaxError(function(f, g, d, c) {
        $("#dialog-message").dialog("open")
    });
    $("#social li").draggable({
        connectToSortable: "#form_builder_sortable",
        helper: "clone",
        revert: "invalid",
        revertDuration: 300,
        addClasses: true
    });
    $("#form_builder_sortable li").not(".li_pagination").addClass("synched dblive");
    $("#bottom_bar_duplicate_field").click(function() {
        var e = $("#form_builder_sortable > li").not(".li_pagination").size() + 1;
        $('<li id="li_temp_' + e + '" class="duplicate_processing"><img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> duplicating field ...</li>').insertAfter("#" + active_element).hide().fadeIn();
        var d = $("#form_header").data("form_properties").id;
        var f = $("#" + active_element).data("field_properties");
        var c = f.position + 1;
        $.ajax({
            type: "POST",
            async: true,
            url: "/backend.php/forms/addfield",
            data: {
                element_type: f.type,
                form_id: d,
                holder_id: "li_temp_" + e,
                position: c,
                action: "duplicate",
                field_properties: f
            },
            cache: false,
            global: true,
            dataType: "json",
            error: function(i, g, h) {
                $("#form_builder_sortable .duplicate_processing").remove()
            },
            success: function(h) {
                if (h.status == "ok") {
                    var g = $(h.markup);
                    g.data("field_properties", h.field_properties);
                    $("#" + h.holder_id).replaceWith(g);
                    if (g.data("field_properties").type == "section") {
                        $("#li_" + h.element_id + " > h3").html($("#li_" + h.element_id + " > h3").html().replace(/\n/g, "<br />"))
                    } else {
                        if (g.data("field_properties").type == "page_break") {} else {
                            if (g.data("field_properties").type == "matrix") {} else {
                                if ($("#li_" + h.element_id + " > label").length > 0) {
                                    $("#li_" + h.element_id + " > label").html($("#li_" + h.element_id + " > label").html().replace(/\n/g, "<br />"))
                                } else {
                                    if ($("#li_" + h.element_id + " > span.description").length > 0) {
                                        $("#li_" + h.element_id + " > span.description").html($("#li_" + h.element_id + " > span.description").html().replace(/\n/g, "<br />"))
                                    }
                                }
                            }
                        }
                    }
                    $("#li_" + h.element_id).hide().fadeIn();
                    reorganize_page_break();
                    $("#form_builder_sortable").sortable("refreshPositions")
                } else {
                    $("#dialog-message").dialog("open");
                    $("#form_builder_sortable .duplicate_processing").remove()
                }
            }
        });
        return false
    });
    $("#bottom_bar_delete_field").click(function() {
        var d = $("#" + active_element);
        if ($("#form_builder_sortable > li").not(".li_pagination").not(".page_break").size() <= 1) {
            $("#ui-dialog-title-dialog-warning").html("Unable to delete this field!");
            $("#dialog-warning-msg").html("You can't delete all fields in your form! <br />You must have at least 1 field.");
            $("#dialog-warning").dialog("open")
        } else {
            if (d.data("field_properties").type == "page_break") {
                d.data("field_properties").is_db_live = 0
            }
            if (d.data("field_properties").is_db_live == 0) {
                var c = d.html();
                var e = $("#form_header").data("form_properties").id;
                d.html('<img src="/form_builder/images/loader_small.gif" style="vertical-align: middle"/> deleting field ...').addClass("delete_processing");
                $.ajax({
                    type: "POST",
                    async: true,
                    url: "/backend.php/forms/deletedraftfield",
                    data: {
                        form_id: e,
                        element_id: d.data("field_properties").id,
                        element_type: d.data("field_properties").type
                    },
                    cache: false,
                    global: true,
                    dataType: "json",
                    error: function(h, f, g) {
                        d.html(c).removeClass("delete_processing")
                    },
                    success: function(f) {
                        if (f.status == "ok") {
                            $("#li_" + f.element_id).fadeOut("normal", function() {
                                $(this).remove();
                                reorganize_page_break();
                                $("#form_builder_sortable").sortable("refreshPositions")
                            });
                            active_element = null;
                            $("#selected_field_image").css("visibility", "hidden");
                            $("#bottom_bar_field_action").fadeOut("fast", function() {
                                $("#bottom_bar_content").animate({
                                    "margin-left": "225px"
                                }, {
                                    duration: 200,
                                    queue: false
                                })
                            });
                            $("#builder_tabs").tabs("select", 0)
                        } else {
                            d.html(c).removeClass("delete_processing");
                            $("#dialog-message").dialog("open")
                        }
                    }
                })
            } else {
                $("#dialog-confirm-field-delete").dialog("open")
            }
        }
        return false
    });
    $("#bottom_bar").fadeIn();
    $("#editor_loading").fadeOut("slow");
    $(window).on("beforeunload", function() {
        var c = b();
        return c
    });

    function b() {
        if ($(".form_editor").data("form_saved") === true) {
            return null
        } else {
            return "Your changes haven't been saved! \nLeaving this page will discard all changes."
        }
    }

    function a() {
        var c = $("#form_header").data("form_properties").id;
        $.ajax({
            type: "POST",
            async: true,
            url: "/backend.php/forms/ping",
            data: {
                form_id: c
            },
            cache: false,
            global: true,
            dataType: "json",
            complete: function() {
                setTimeout(a, 300000)
            }
        })
    }
    window.setTimeout(a, 300000)
});