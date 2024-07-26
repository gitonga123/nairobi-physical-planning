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

<script type="text/javascript"
  src="<?php echo public_path('assets_unified/js/bootstrap-duallistbox/src/jquery.bootstrap-duallistbox.js') ?>"></script>

<?php
if ($sf_context->getModuleName() == "tasks") {
  ?>
  <script src="/assets_backend/js/slide-in/js/main.js"></script> <!-- Resource jQuery NEW JS -->
  <script src="/assets_backend/js/slide-in/js/modernizr.js"></script> <!-- Modernizr NEW JS -->
  <?php
}
?>
<script src="/assets_backend/js/select2.min.js"></script>

<script>
  $(document).ready(function () {

    if ($('#filter_applications_id').length > 0) {
      $('#filter_applications_id').select2();
    }
    if ($('#select_fee_1').length > 0) {
      $('#select_fee_1').select2();
    }
    if ($('#select_fee_2').length > 0) {
      $('#select_fee_2').select2();
    }
    if ($('#select_fee_3').length > 0) {
      $('#select_fee_3').select2();
    }
    if ($('#select_fee_4').length > 0) {
      $('#select_fee_4').select2();
    }
    if ($('#select_fee_5').length > 0) {
      $('#select_fee_5').select2();
    }
    if ($('#select_fee_6').length > 0) {
      $('#select_fee_6').select2();
    }
    if ($('#select_fee_8').length > 0) {
      $('#select_fee_8').select2();
    }

    if ($('#select_fee_9').length > 0) {
      $('#select_fee_9').select2();
    }
    if ($('#select_fee_10').length > 0) {
      $('#select_fee_10').select2();
    }
    if ($('#select_fee_11').length > 0) {
      $('#select_fee_11').select2();
    }
    if ($('#select_fee_12').length > 0) {
      $('#select_fee_12').select2();
    }
    if ($('#select_fee_13').length > 0) {
      $('#select_fee_13').select2();
    }
    if ($('#select_fee_14').length > 0) {
      $('#select_fee_14').select2();
    }
    if ($('#select_fee_15').length > 0) {
      $('#select_fee_15').select2();
    }

  });
  jQuery(document).ready(function () {

    "use strict";

    if ($('#table1').length > 0) {
      jQuery('#table1').dataTable();
    }

    if ($('#table2').length > 0) {
      jQuery('#table2').dataTable({
        "sPaginationType": "full_numbers",

        // Using aoColumnDefs
        "aoColumnDefs": [{
          "bSortable": false,
          "aTargets": ['no-sort']
        }]
      });
    }

    if (typeof $.fn.prettyPhoto !== 'undefined' && $('a[rel^="prettyPhoto"]').length > 0) {
      $('a[rel^="prettyPhoto"]').prettyPhoto();
    }

    //Replaces data-rel attribute to rel.
    //We use data-rel because of w3c validation issue
    if ($('a[data-rel]').length > 0) {
      jQuery('a[data-rel]').each(function () {
        jQuery(this).attr('rel', jQuery(this).data('rel'));
      });
    }

    if ($('#basicWizard').length > 0) {
      // Basic Wizard
      jQuery('#basicWizard').bootstrapWizard();
    }

    function initializeDatepicker(elementId) {
      if ($(elementId).length > 0) {
        $(elementId).datepicker();
      }
    }

    // Date Picker
    initializeDatepicker('#from_dateblt1');
    initializeDatepicker('#to_date');
    initializeDatepicker('#from_date1');
    initializeDatepicker('#to_date1');
    initializeDatepicker('#from_date8');
    initializeDatepicker('#to_date8');
    initializeDatepicker('#from_date10');
    initializeDatepicker('#to_date10');
    initializeDatepicker('#from_date12');
    initializeDatepicker('#to_date12');
    initializeDatepicker('#from_date_r2');
    initializeDatepicker('#to_date_r2');
    initializeDatepicker('#from_date_r3');
    initializeDatepicker('#to_date_r3');
    initializeDatepicker('#from_date_r5');
    initializeDatepicker('#to_date_r5');
    initializeDatepicker('#from_date_r6');
    initializeDatepicker('#to_date_r6');
    initializeDatepicker('#from_date_r8');
    initializeDatepicker('#to_date_r8');
    initializeDatepicker('#from_date_r9');
    initializeDatepicker('#to_date_r9');
    initializeDatepicker('#from_date_r10');
    initializeDatepicker('#to_date_r10');
    initializeDatepicker('#from_date17');
    initializeDatepicker('#to_date17');
    initializeDatepicker('#from_date18');
    initializeDatepicker('#to_date18');
    initializeDatepicker('#from_date19');
    initializeDatepicker('#to_date19');
    initializeDatepicker('#from_date12a');
    initializeDatepicker('#to_date12a');
    initializeDatepicker('#from_date12b');
    initializeDatepicker('#to_date12b');
    initializeDatepicker('#announcements_start_date');
    initializeDatepicker('#announcements_end_date');
    initializeDatepicker('#from_date_approval');
    initializeDatepicker('#to_date_approval');
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
    self.xmlHttpReq1.onreadystatechange = function () {
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
    self.xmlHttpReq1.onreadystatechange = function () {
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
    self.xmlHttpReq1.onreadystatechange = function () {
      if (self.xmlHttpReq1.readyState == 4) {
        //document.getElementById(div).innerHTML = "<span class='glyphicon glyphicon-ok'></span>";
        // Patch
        document.getElementById(div).innerHTML = "<a title='Click to Mark as Not Selected.' href='' onClick=\"ajaxunselect('" + strURL + "','" + div + "'); return false;\"><span class='glyphicon glyphicon-ok'></span>";
        // end
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
    self.xmlHttpReq1.onreadystatechange = function () {
      if (self.xmlHttpReq1.readyState == 4) {
        //document.getElementById(div).innerHTML = "<span class='glyphicon glyphicon-remove'></span>";
        // Patch - Return link
        document.getElementById(div).innerHTML = "<a title='Click to Mark as Selected' href='' onClick=\"ajaxselect('" + strURL + "','" + div + "'); return false;\">" + "<span class='glyphicon glyphicon-remove'></span>";
        // end
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
    (function () {
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

<script src="/assets_backend/js/custom.js"></script>