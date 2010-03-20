<?php
# OpenDataMap.ca - Open Data Ottawa Points of Interest
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

// UTF-8 enable the PHP and HTML
mb_language('uni');
mb_internal_encoding('UTF-8');
header('Content-type: text/html; charset=utf-8');

require_once('classes/Version.php');

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>OpenDataMap.ca - Open Data Ottawa Points of Interest</title>

  <meta name="generator" content="<?php $version = new Version(); echo $version->program . " " . $version->version;?>">

  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

  <link rel="stylesheet" type="text/css" href="css/style.css" media="screen">

  <script type="text/javascript" src="./jquery/jquery-1.4.2.min.js" charset="utf-8"></script>
  <script type="text/javascript" src="./openlayers/OpenLayers.js" charset="utf-8"></script>
  <script type="text/javascript" src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js" charset="utf-8"></script>

  <script type="text/javascript" charset="utf-8">
    // Coordinates for Ottawa, ON
    var lat=45.420833;
    var lon=-75.69;

    var zoom=12;
    var last_zoom = zoom;
        
    var map; // holds Map object
    var markers; // holds Markers object
    var my_markers = new Array(); // our list of Markers

    // Set the language to English
    OpenLayers.Lang.setCode("en");

    // Determines if the marker is within the bounds of the visible part of the map at the current zoom level
    function marker_is_in_view(marker) {
      var tlLonLat = map.getLonLatFromPixel(new OpenLayers.Pixel(1,1)).
            transform(map.getProjectionObject(),map.displayProjection);
      var mapsize = map.getSize();
      var brLonLat = map.getLonLatFromPixel(new OpenLayers.Pixel(mapsize.w - 1, mapsize.h - 1)).
            transform(map.getProjectionObject(),map.displayProjection);

      var tlLonLatF = new OpenLayers.LonLat(tlLonLat.lon, tlLonLat.lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
      var brLonLatF = new OpenLayers.LonLat(brLonLat.lon, brLonLat.lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());

      if (tlLonLatF.lon <= marker.lonlat.lon && marker.lonlat.lon <= brLonLatF.lon &&
          tlLonLatF.lat >= marker.lonlat.lat && marker.lonlat.lat >= brLonLatF.lat) {
        return 1;
      } else {
        return 0;
      }
    }

    // Determines if the parameter is in the my_markers array
    function marker_in_my_markers(marker) {
      for (var i = 0; i < my_markers.length; i++) {
        if (my_markers[i].lonlat.lon == marker.lonlat.lon && my_markers[i].lonlat.lat == marker.lonlat.lat) {
          return 1;
        }
      }
      return 0;
    }

    // When the map is moved, fetch some markers
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

      // GET and process some markers
      $.get(url, function(data) { 
        // Remove markers that aren't within the bounds of the visible part of the map at the current zoom level
        // Keep markers that are within the bounds of the visible part of the map at the current zoom level
        var my_markers_2 = new Array();
        while (my_markers.length > 0) {
          var current_marker = my_markers.pop();
          if (last_zoom < map.getZoom() && marker_is_in_view(current_marker) == 1) {
            my_markers_2.push(current_marker);
          } else {
            markers.removeMarker(current_marker);
            current_marker.destroy();
          }
        }
        my_markers = my_markers_2;
        last_zoom = map.getZoom();

        $(data).find('wpt').each(function() {
          var wpt = $(this);

          // Build a new marker
          var size = new OpenLayers.Size(32, 37);
          var offset = new OpenLayers.Pixel(-(size.w / 2), -size.h);
          var icon = new OpenLayers.Icon('./sym/' + $(this).find("sym").text() + '.png', size, offset);
          var lonLatMarker = new OpenLayers.LonLat(wpt.attr('lon'), wpt.attr('lat')).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
          var marker = new OpenLayers.Marker(lonLatMarker, icon);

          if (marker_in_my_markers(marker) == 1) {
            // if we already have this marker on the map, don't try to re-add it
            marker.destroy();
          } else {
            // Add the marker to the map
            var feature = new OpenLayers.Feature(markers, lonLatMarker);
            feature.closeBox = true;
            feature.popupClass = OpenLayers.Class(OpenLayers.Popup.AnchoredBubble, {minSize: new OpenLayers.Size(300, 180) } );
            feature.data.popupContentHTML = '<b>' + $(this).find("name").text() + '</b><br/>' + $(this).find("desc").text();
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
        });
      });
    }

    // Initialize the map
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

      var layerTah = new OpenLayers.Layer.OSM.Osmarender("Tiles@Home");
      map.addLayer(layerTah);

      markers = new OpenLayers.Layer.Markers("Open Data Ottawa Points of Interest");
      map.addLayer(markers);

      map.addControl(new OpenLayers.Control.LayerSwitcher());
 
      var lonLat = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
      map.setCenter(lonLat, zoom);
    }

  </script>
</head>
<body onload="init();">
  <div id="header"><h1>Welcome to OpenDataMap.ca!</h1></div>
  <div id="map"></div>
  <div id="sidebar">
    <div id="sidetxt">
      <p><em>Disclaimer</em>: The points of interest you see on this page are for testing purposes only and may be totally inaccurate. This website is not affiliated with any government or transit agency.</p>
      <p>Copyright &copy; 2010 <a href="http://www.tomcort.com/">Thomas Cort</a><br/><small>This application is <a href="http://www.gnu.org/philosophy/free-sw.html">Free Software</a>. Get the source code <a href="http://github.com/tcort/odopoi">here</a>.</small></p>
      <p><small>Maps are licensed <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-By-SA 2.0</a> by <a href="http://www.openstreetmap.org/">OpenStreetMap</a>. Map display is licensed <a href="http://svn.openlayers.org/trunk/openlayers/license.txt">BSD-style</a> by <a href="http://openlayers.org/">OpenLayers</a>. Marker icons are licensed <a href="http://creativecommons.org/licenses/by-sa/3.0/">CC-By-SA 3.0</a> by <a href="http://code.google.com/p/google-maps-icons/">Maps icons collection</a>.</small></p>
    </div>
  </div>
</body>
</html>
