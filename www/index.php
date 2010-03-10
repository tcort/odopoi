<?php
# Open Data Ottawa Points of Interest
# Copyright (C) 2010 Thomas Cort <linuxgeek@gmail.com>
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>OpenDataMap.ca - Open Data Ottawa Points of Interest</title>

  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

  <link rel="stylesheet" type="text/css" href="css/style.css" media="screen">

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

    OpenLayers.Lang.setCode("en");

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

      while (my_markers.length > 0) {
        var current_marker = my_markers.pop();
        markers.removeMarker(current_marker);
        current_marker.destroy();
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
                  var lonLatMarker = new OpenLayers.LonLat(arr[1], arr[0]).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
                  var marker = new OpenLayers.Marker(lonLatMarker, icon);

                  var feature = new OpenLayers.Feature(markers, lonLatMarker);
                  feature.closeBox = true;
                  feature.popupClass = OpenLayers.Class(OpenLayers.Popup.AnchoredBubble, {minSize: new OpenLayers.Size(300, 180) } );
                  feature.data.popupContentHTML = '<b>' + arr[2] + '</b><br/>' + arr[3];
                  feature.data.overflow = "hidden";
                  marker.feature = feature;

                  var markerClick = function(evt) {
                    if (this.popup == null) {
                      this.popup = this.createPopup(this.closeBox);
                      map.addPopup(this.popup);
                      this.popup.show();
                    } else {
                      this.popup.toggle();
                    }
                    OpenLayers.Event.stop(evt);
                  };

                  marker.events.register("mousedown", feature, markerClick);

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

    function moveend_listener(evt) {
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
        controls: [new OpenLayers.Control.Navigation(), new OpenLayers.Control.PanZoomBar(), new OpenLayers.Control.Attribution()],
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

  <div id="header"><h1>Welcome to OpenDataMap.ca<sup>Alpha</sup>!</h1></div>
  <div id="map"></div>
  <div id="sidebar">
    <div id="sidetxt">
      <p><em>Warning</em>: The points of interest you see on this page are for testing purposes only and may be totally inaccurate.</p>
      <p>Help this app get from Alpha to Beta by contributing data, time, ideas, and/or code to <a href="http://opendataottawa.ca">Open Data Ottawa</a>.</p>
      <p>Copyright &copy; 2010 <a href="http://www.tomcort.com/">Thomas Cort</a><br/><small>This application is <a href="http://www.gnu.org/philosophy/free-sw.html">Free Software</a>. Get the source code <a href="http://github.com/tcort/odopoi">here</a>.</small></p>
    </div>
  </div>
</body>
</html>
