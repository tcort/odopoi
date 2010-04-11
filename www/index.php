<?php
/*
 * Copyright (C) 2010 Thomas Cort <linuxgeek@gmail.com>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted (subject to the limitations in the
 * disclaimer below) provided that the following conditions are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the
 *    distribution.
 *
 *  * Neither the name of Thomas Cort nor the names of its
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * NO EXPRESS OR IMPLIED LICENSES TO ANY PARTY'S PATENT RIGHTS ARE
 * GRANTED BY THIS LICENSE.  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT
 * HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
 * BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

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
    function set_cookie(c_key, c_val) {
      var c = c_key + '=' + c_val;

      // cookie expires in 1 month
      var dt = new Date();
      dt.setTime(dt.getTime() + (30 * 24 * 60 * 60 * 1000));
      c = c + '; expires=' + dt.toGMTString();
      c = c + '; path=/';
      document.cookie = c;
    }

    function get_cookie(c_key) {
      var c_key_eq = c_key + "=";
      var cookies = document.cookie.split(';');
      var i;
      for(i = 0; i < cookies.length; i++) {
        var cookie = cookies[i];
        while (cookie.charAt(0)==' ') { 
          cookie = cookie.substring(1, cookie.length);
        }

        if (cookie.indexOf(c_key_eq) == 0) {
          return cookie.substring(c_key_eq.length, cookie.length);
        }
      }

      return null;
    }

    // Coordinates for Ottawa, ON or values saved in cookies
    var lat = get_cookie('lat') != null ? parseFloat(get_cookie('lat').replace(/^\s+|\s+$/g,"")) : 45.420833;
    var lon = get_cookie('lon') != null ? parseFloat(get_cookie('lon').replace(/^\s+|\s+$/g,"")) : -75.69;
    var zoom = get_cookie('zoom') != null ? parseInt(get_cookie('zoom').replace(/^\s+|\s+$/g,"")) : 12;

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
            feature.data.popupContentHTML = '<p><b>' + $(this).find("name").text() + '</b></p>' + $(this).find("desc").text() + '<p><small>This point of interest is copyright <a href="http://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-By-SA</a>.</small></p>';
            feature.data.overflow = "auto";
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

      var centerLonLat = map.getLonLatFromPixel(new OpenLayers.Pixel(mapsize.w / 2, mapsize.h / 2)). transform(map.getProjectionObject(),map.displayProjection);

      set_cookie('lon', centerLonLat.lon);
      set_cookie('lat', centerLonLat.lat);
      set_cookie('zoom', map.getZoom());
    }

    // Initialize the map
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
 
      var layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");
      map.addLayer(layerMapnik);

      var layerTah = new OpenLayers.Layer.OSM.Osmarender("Tiles@Home");
      map.addLayer(layerTah);

      var layerCycleMap = new OpenLayers.Layer.OSM.CycleMap("CycleMap");
      map.addLayer(layerCycleMap);

      markers = new OpenLayers.Layer.Markers("Points of Interest");
      map.addLayer(markers);

      map.addControl(new OpenLayers.Control.LayerSwitcher());

      var lonLat = new OpenLayers.LonLat(lon, lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
      map.setCenter(lonLat, zoom);
    }

  </script>
</head>
<body onload="init();">
  <div id="map"></div>
  <div id="sidebar">
    <div id="sidetxt">
      <div id="header"><h4>OpenDataMap.ca</h4></div>
      <p><big>Site Details</big></p>
      <p><small><b>Software</b>: The software that generated this page and interacts with a <a href="http://mysql.org">MySQL</a> database is called <em><a href="http://github.com/tcort/odopoi">odopoi</a></em>. It is <a href="http://www.gnu.org/philosophy/free-sw.html">Free Software</a> written in <a href="http://php.net/">PHP</a>. <em>odopoi</em> uses <a href="http://jquery.org">jQuery</a> for <a href="http://en.wikipedia.org/wiki/Ajax_%28programming%29">AJAX</a> and <a href="http://openlayers.org/">OpenLayers</a> for managing the map.</small></p>
      <p><small><b>Data</b>: The maps and points of interest are copyright <a href="http://www.openstreetmap.org/">OpenStreetMap</a> contributors. Both are licenced <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>. To find out more about using and editing these maps, visit <a href="http://openstreetmap.ca/">OpenStreetMap.ca</a> or <a href="http://openstreetmap.org">OpenStreetMap.org</a>.</small></p>
      <p><small><b>Icons</b>: The marker icons used are from the <a href="http://code.google.com/p/google-maps-icons/">Maps icons collection</a> which is licensed <a href="http://creativecommons.org/licenses/by-sa/3.0/">CC-By-SA</a>.</small></p>
      <p><big>Disclaimer</big></p>
      <p><small>OPENDATAMAP.CA PROVIDES INFORMATION ON AN "AS-IS" BASIS. OPENDATAMAP.CA MAKES NO WARRANTIES REGARDING THE INFORMATION PROVIDED, AND DISCLAIMS LIABILITY FOR DAMAGES RESULTING FROM ITS USE.</small></p>
    </div>
  </div>
</body>
</html>
