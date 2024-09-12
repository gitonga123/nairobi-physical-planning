<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Viewing Location of Plot <?php echo $_GET['plot']; ?></title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAuzVxoZq_AIcpvn0-opFn1BRSsNSTqs_4unsGhACk9s1WZmHjxxRuwGnpsg_k4KasH9lBW301SfRgdA"
            type="text/javascript"></script>
    <script type="text/javascript">
    
    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map_canvas"));
        map.setCenter(new GLatLng(<?php echo $_GET['lat']; ?>, <?php echo $_GET['long']; ?>), 13);
 
        
          var latlng = new GLatLng(<?php echo $_GET['lat'] ?>,<?php echo $_GET['long'] ?>);
          map.addOverlay(new GMarker(latlng));
		  
		  map.openInfoWindow(new GLatLng(<?php echo $_GET['lat'] ?>,<?php echo $_GET['long'] ?>), document.createTextNode("Plot <?php echo $_GET['plot'] ?>"));
      }
    }

    </script>
  </head>

  <body onload="initialize()" onunload="GUnload()">
<div class="g12" style="padding-left: 3px;">
			<form style="margin-bottom: 0px;">
			<label style='height: 30px; margin-top: 0px;'>
			<div style='float: left; font-size: 20px; font-weight: 700;'>Plot on Map</div>
<div style="float: right; margin-top: -12px;">
							<button style="height: 34px; font-size: 12px;" onClick="window.location='<?php echo public_path(); ?>plan/plot/index';">Back to Plots</button>
</div>
</label>
			</form>
    <div id="map_canvas" style="width: 800px; height: 400px"></div>
   </div>
  </body>
</html>
