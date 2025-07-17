<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Viewing Location For All Plots</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAuzVxoZq_AIcpvn0-opFn1BRSsNSTqs_4unsGhACk9s1WZmHjxxRuwGnpsg_k4KasH9lBW301SfRgdA"
            type="text/javascript"></script>
    <script type="text/javascript">
    
    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map_canvas"));
        map.setCenter(new GLatLng(-1.283333,36.816667), 9);
 
        <?php 
			$q = Doctrine_Query::create()
			 	 ->from('Plot a');
		    $plots = $q->execute();
			foreach($plots as $plot)
			{
		?>
          var latlng = new GLatLng(<?php echo $plot->getPlotLat(); ?>,<?php echo $plot->getPlotLong(); ?>);
		  var marker<?php echo $plot->getId() ?> = new GMarker(latlng);
		  GEvent.addListener(marker<?php echo $plot->getId() ?>, "click", function() {
			marker<?php echo $plot->getId() ?>.openInfoWindowHtml("Plot <?php echo $plot->getPlotNo(); ?>");
		  });
          map.addOverlay(marker<?php echo $plot->getId() ?>);
		  
		<?php
			}
		?>
      }
    }

    </script>
  </head>

  <body onload="initialize()" onunload="GUnload()">
    <div align="center">
    <div id="map_canvas" style="width: 900px; height: 400px"></div>
    </div>
  </body>
</html>
