<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

  <title>Open Data Ottawa Points of Interest</title>

  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

  <script type="text/javascript" src="http://www.openlayers.org/api/OpenLayers.js"></script>
  <script type="text/javascript" src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>

  <script type="text/javascript">
    // Coordinates for Ottawa, ON
    var lat=45.420833
    var lon=-75.69

    var zoom=12
        
    var map;
    var markers;
    var my_markers = new Array();

    function makeRequest(url) {
      var http_request = false;

      if (window.XMLHttpRequest) { // Mozilla, Safari, ...
        http_request = new XMLHttpRequest();
        if (http_request.overrideMimeType) {
          http_request.overrideMimeType('text/xml');
        }
      } else if (window.ActiveXObject) { // IE
        try {
          http_request = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          try {
            http_request = new ActiveXObject("Microsoft.XMLHTTP");
          } catch (e) {
          }
        }
      }

      if (!http_request) {
        alert('Giving up :( Cannot create an XMLHTTP instance');
        return false;
      }

      http_request.onreadystatechange = function() { alertContents(http_request); };
      http_request.open('GET', url, true);
      http_request.send(null);
    }

    function alertContents(http_request) {
      if (http_request.readyState == 4) {
        if (http_request.status == 200) {
          var xmldoc = http_request.responseXML;
          var root = xmldoc.getElementsByTagName('root').item(0);

          if (root != null) {

            var iNode = 0;
            for (iNode = 0; iNode < root.childNodes.length; iNode++) {

              var node = root.childNodes.item(iNode);
              for (i = node.childNodes.length-1; i >= 0; i--) {
                var sibl = node.childNodes.item(i);
                var len = parseInt(sibl.childNodes.length / 2);
                var arr = new Array(len);
                var cnt = 0;
                for (x = 0; x < sibl.childNodes.length; x++) {
                  var sibl2 = sibl.childNodes.item(x);
                  var sibl3;
                  if (sibl2.childNodes.length > 0) {
                    sibl3 = sibl2.childNodes.item(0);
                    arr[cnt] = sibl3.data;
                    cnt++;
                  }
                }
                if (arr.length > 0) {
                  var size = new OpenLayers.Size(21,20);
                  var offset = new OpenLayers.Pixel(0,0);
                  var icon = new OpenLayers.Icon(arr[4],size,offset);
                  var marker = new OpenLayers.Marker(new OpenLayers.LonLat(arr[1], arr[0]).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject()), icon);
                  markers.addMarker(marker);
                  my_markers.push(marker);
                }
              }
            }
          }
        } else {
          alert('There was a problem with the request.');
        }
      }
    }

    function moveend_listener(event) {
      while (my_markers.length > 0) {
        var current_marker = my_markers.pop();
        markers.removeMarker(current_marker);
        current_marker.destroy();
      }

      var zoom = map.getZoom();
      var tlLonLat = map.getLonLatFromPixel(new OpenLayers.Pixel(1,1)).
            transform(map.getProjectionObject(),map.displayProjection);
      var mapsize = map.getSize();
      var brLonLat = map.getLonLatFromPixel(new OpenLayers.Pixel(mapsize.w - 1, mapsize.h - 1)).
            transform(map.getProjectionObject(),map.displayProjection);

      var url = "./api.php?action=getPOI"
           + "&zoom=" + zoom
           + "&tllon=" + tlLonLat.lon
           + "&tllat=" + tlLonLat.lat
           + "&brlon=" + brLonLat.lon
           + "&brlat=" + brLonLat.lat;

      makeRequest(url);
    }

    function init() {

      map = new OpenLayers.Map ("map", {
        controls: [new OpenLayers.Control.Navigation(), new OpenLayers.Control.PanZoomBar()],
        maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
        maxResolution: 156543.0399,
        numZoomLevels: 19,
        units: 'm',
        projection: new OpenLayers.Projection("EPSG:900913"),
        displayProjection: new OpenLayers.Projection("EPSG:4326"),
        eventListeners: { "moveend": moveend_listener }
      } );
 
      layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
      map.addLayer(layerMapnik);

      markers = new OpenLayers.Layer.Markers("Open Data Ottawa Points of Interest");
      map.addLayer(markers);

      map.addControl(new OpenLayers.Control.LayerSwitcher());
 
      var lonLat = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
      map.setCenter(lonLat, zoom);
    }

  </script>
</head>
<body onload="init();">

  <table style="width: 100%; height: 100%" border="0px" cellspacing="0px" cellpadding="0px">
    <tr>
      <th style="width: 100%;">
        Welcome to OpenDataMap.ca<sup>Alpha</sup>!
      </th>
    </tr>
    <tr>
      <td style="width: 100%;">
        <div><p><em>Warning</em>: The points of interest you see on this page are for testing purposes only and may be totally inaccurate. <a href="http://opendataottawa.ca">Help this app get to Beta</a>.</p></div>
      </td>
    </tr>
    <tr>
      <td style="width: 100%; height: 100%">
        <div style="width:100%; height:100%" id="map"></div>
      </td>
    </tr>
    <tr>
      <td style="width: 100%;">
  <div><p><small>The snazzy map you see above is courtesy of <a href="http://openstreetmap.org">OpenStreetMap</a>. OpenStreeMap data is licensed under 
the <a href="http://creativecommons.org/licenses/by-sa/2.0/">Creative Commons Attribution-Share Alike 2.0 Generic License</a>. The 
<a href="http://github.com/tcort/odopoi">code</a> used to generate this page is based on examples available at <a 
href="http://wiki.openstreetmap.org">wiki.OpenStreetMap.org</a> and is licensed under the 
<a href="http://creativecommons.org/licenses/by-sa/2.0/">Creative Commons Attribution-Share Alike 2.0 Generic License</a>.</small></p></div>
      </td>
    </tr>
  </table>

</body>
</html>
