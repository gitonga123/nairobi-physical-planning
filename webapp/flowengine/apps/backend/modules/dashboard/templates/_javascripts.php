<?php

/**
 * _javascripts.php template.
 *
 * Displays javascripts on the layout
 *
 * @package    backend
 * @subpackage dashboard
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>
<script src="/assets_backend/js/jquery-1.11.1.min.js"></script>
<script src="/assets_backend/js/jquery-migrate-1.2.1.min.js"></script>
<script src="/assets_backend/js/bootstrap.min.js"></script>
<script src="/assets_backend/js/modernizr.min.js"></script>
<script src="/assets_backend/js/jquery.sparkline.min.js"></script>
<script src="/assets_backend/js/toggles.min.js"></script>
<script src="/assets_backend/js/jquery.cookies.js"></script>
<script src="/assets_backend/js/jquery-ui-1.10.3.min.js"></script>

<script src="/assets_backend/js/flot/jquery.flot.min.js"></script>
<script src="/assets_backend/js/flot/jquery.flot.resize.min.js"></script>
<script src="/assets_backend/js/flot/jquery.flot.symbol.min.js"></script>

<script src="/assets_backend/js/morris.min.js"></script>
<script src="/assets_backend/js/raphael-2.1.0.min.js"></script>
<script src="/assets_backend/js/flot/jquery.flot.symbol.min.js"></script>
<script src="/assets_backend/js/flot/jquery.flot.resize.min.js"></script>
<script src="/assets_backend/js/flot/jquery.flot.crosshair.min.js"></script>
<script src="/assets_backend/js/flot/jquery.flot.categories.min.js"></script>
<script src="/assets_backend/js/flot/jquery.flot.pie.min.js"></script>

<script src="/assets_backend/js/jquery.datatables.min.js"></script>
<script src="/assets_backend/js/jquery.autogrow-textarea.js"></script>
<script src="/assets_backend/js/bootstrap-fileupload.min.js"></script>
<script src="/assets_backend/js/bootstrap-timepicker.min.js"></script>
<script src="/assets_backend/js/jquery.maskedinput.min.js"></script>
<script src="/assets_backend/js/jquery.tagsinput.min.js"></script>
<script src="/assets_backend/js/jquery.mousewheel.js"></script>
<script src="/assets_backend/js/dropzone.min.js"></script>
<script src="/assets_backend/js/colorpicker.js"></script>
<script src="/assets_backend/js/fullcalendar.min.js"></script>

<script src="/assets_backend/js/jquery.prettyPhoto.js"></script>
<script src="/assets_backend/js/holder.js"></script>
<script src="/assets_backend/js/bootstrap-wizard.min.js"></script>
<script src="/assets_backend/js/jquery.validate.min.js"></script>

<script src="/assets_backend/js/wysihtml5-0.3.0.min.js"></script>
<script src="/assets_backend/js/bootstrap-wysihtml5.js"></script>
<script src="/assets_backend/js/easy-number-separator.js"></script>


<script src="/assets_backend/js/select2.min.js"></script>
<script src="/assets_backend/js/custom.js"></script>
<script type="text/javascript" src="<?php echo public_path('assets_unified/js/bootstrap-duallistbox/src/jquery.bootstrap-duallistbox.js') ?>"></script>

<?php
if ($sf_context->getModuleName() == "tasks") {
?>
  <script src="/assets_backend/js/slide-in/js/main.js"></script> <!-- Resource jQuery NEW JS -->
  <script src="/assets_backend/js/slide-in/js/modernizr.js"></script> <!-- Modernizr NEW JS -->
<?php
}
?>

<script>
  $(document).ready(function() {
    $('#filter_applications_id').select2();
    $('#select_fee_2').select2();
    $('#select_fee_3').select2();
    $('#select_fee_4').select2();
    $('#select_fee_5').select2();
    $('#select_fee_1').select2();
    $('#select_fee_6').select2();
    $('#select_fee_7').select2();
  });
  jQuery(document).ready(function() {

    "use strict";

    jQuery('#table1').dataTable();

    jQuery('#table2').dataTable({
      "sPaginationType": "full_numbers",

      // Using aoColumnDefs
      "aoColumnDefs": [{
        "bSortable": false,
        "aTargets": ['no-sort']
      }]
    });

    jQuery("a[rel^='prettyPhoto']").prettyPhoto();

    //Replaces data-rel attribute to rel.
    //We use data-rel because of w3c validation issue
    jQuery('a[data-rel]').each(function() {
      jQuery(this).attr('rel', jQuery(this).data('rel'));
    });

    // Basic Wizard
    jQuery('#basicWizard').bootstrapWizard();


    // Date Picker
    jQuery('#from_dateblt1').datepicker();
    jQuery('#to_date').datepicker();
    jQuery('#from_date1').datepicker();
    jQuery('#to_date1').datepicker();
    jQuery('#from_date8').datepicker();
    jQuery('#to_date8').datepicker();
    jQuery('#from_date10').datepicker();
    jQuery('#to_date10').datepicker();
    jQuery('#from_date12').datepicker();
    jQuery('#to_date12').datepicker();
    jQuery('#from_date_r2').datepicker();
    jQuery('#to_date_r2').datepicker();
    jQuery('#from_date_r3').datepicker();
    jQuery('#to_date_r3').datepicker();
    jQuery('#from_date_r5').datepicker();
    jQuery('#to_date_r5').datepicker();
    jQuery('#from_date_r6').datepicker();
    jQuery('#to_date_r6').datepicker();
    jQuery('#from_date_r8').datepicker();
    jQuery('#to_date_r8').datepicker();
    jQuery('#from_date_r9').datepicker();
    jQuery('#to_date_r9').datepicker();
    jQuery('#from_date_r10').datepicker();
    jQuery('#to_date_r10').datepicker();
    jQuery('#from_date17').datepicker();
    jQuery('#to_date17').datepicker();
    jQuery('#from_date18').datepicker();
    jQuery('#to_date18').datepicker();
    jQuery('#from_date19').datepicker();
    jQuery('#to_date19').datepicker();
    jQuery('#from_date12a').datepicker();
    jQuery('#to_date12a').datepicker();
    jQuery('#from_date12b').datepicker();
    jQuery('#to_date12b').datepicker();
    jQuery('#announcements_start_date').datepicker();
    jQuery('#announcements_end_date').datepicker();
    jQuery('#from_date_approval').datepicker();
    jQuery('#to_date_approval').datepicker();
  });

  function ajaxresolve(strURL, div) {
    var xmlHttpReq1 = false;
    var self1 = this;
    // Mozilla/Safari

    if (window.XMLHttpRequest) {
      self.xmlHttpReq1 = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
      self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq1.open('POST', strURL, true);
    self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq1.onreadystatechange = function() {
      if (self.xmlHttpReq1.readyState == 4) {
        document.getElementById(div).innerHTML = "<span class='glyphicon glyphicon-ok'></span> Resolved.";
      } else {
        document.getElementById(div).innerHTML = "<img src='/assets_unified/images/loaders/loader1.gif' alt=''> Loading, Please wait...";
      }
    }
    self.xmlHttpReq1.send();
  }

  function ajaxunresolve(strURL, div) {
    var xmlHttpReq1 = false;
    var self1 = this;
    // Mozilla/Safari

    if (window.XMLHttpRequest) {
      self.xmlHttpReq1 = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
      self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq1.open('POST', strURL, true);
    self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq1.onreadystatechange = function() {
      if (self.xmlHttpReq1.readyState == 4) {
        document.getElementById(div).innerHTML = "<span class='glyphicon glyphicon-remove'></span> Not Resolved.";
      } else {
        document.getElementById(div).innerHTML = "<img src='/assets_unified/images/loaders/loader1.gif' alt=''> Loading, Please wait...";
      }
    }
    self.xmlHttpReq1.send();
  }

  function ajaxselect(strURL, div) {
    var xmlHttpReq1 = false;
    var self1 = this;
    // Mozilla/Safari

    if (window.XMLHttpRequest) {
      self.xmlHttpReq1 = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
      self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq1.open('POST', strURL, true);
    self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq1.onreadystatechange = function() {
      if (self.xmlHttpReq1.readyState == 4) {
        //document.getElementById(div).innerHTML = "<span class='glyphicon glyphicon-ok'></span>";
        //OTB Patch
        document.getElementById(div).innerHTML = "<a title='Click to Mark as Not Selected.' href='' onClick=\"ajaxunselect('" + strURL + "','" + div + "'); return false;\"><span class='glyphicon glyphicon-ok'></span>";
        //OTB end
      } else {
        document.getElementById(div).innerHTML = "<img src='/assets_unified/images/loaders/loader1.gif' alt=''> Loading, Please wait...";
      }
    }
    self.xmlHttpReq1.send();
  }

  function ajaxunselect(strURL, div) {
    var xmlHttpReq1 = false;
    var self1 = this;
    // Mozilla/Safari

    if (window.XMLHttpRequest) {
      self.xmlHttpReq1 = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
      self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq1.open('POST', strURL, true);
    self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq1.onreadystatechange = function() {
      if (self.xmlHttpReq1.readyState == 4) {
        //document.getElementById(div).innerHTML = "<span class='glyphicon glyphicon-remove'></span>";
        //OTB Patch - Return link
        document.getElementById(div).innerHTML = "<a title='Click to Mark as Selected' href='' onClick=\"ajaxselect('" + strURL + "','" + div + "'); return false;\">" + "<span class='glyphicon glyphicon-remove'></span>";
        //OTB end
      } else {
        document.getElementById(div).innerHTML = "<img src='/assets_unified/images/loaders/loader1.gif' alt=''> Loading, Please wait...";
      }
    }
    self.xmlHttpReq1.send();
  }
</script>

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

  <!-- Google tag (gtag.js) 
  -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-Z4BM5P1Z0W"></script>
<script>
  window.dataLayer = window.dataLayer || [];

  function gtag() {
    dataLayer.push(arguments);
  }
  gtag('js', new Date());

  gtag('config', 'G-Z4BM5P1Z0W');
</script>