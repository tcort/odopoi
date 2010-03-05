/* Copyright (c) 2006-2007 MetaCarta, Inc., published under a modified BSD license.
 * See http://svn.openlayers.org/trunk/openlayers/repository-license.txt 
 * for the full text of the license. */

 
/**
 * @requires OpenLayers/Tile.js
 * 
 * Class: OpenLayers.Tile.MarkerTile
 * Instances of OpenLayers.Tile.MarkerTile are used to manage the image tiles
 * used by various layers.  Create a new image tile with the
 * <OpenLayers.Tile.MarkerTile> constructor.
 *
 * Inherits from:
 *  - <OpenLayers.Tile>
 */
OpenLayers.Tile.MarkerTile = OpenLayers.Class(OpenLayers.Tile, {

    /** 
     * Property: features 
     * {Array(<OpenLayers.Feature>)} list of features in this tile 
     */
    features: null,

    /** 
     * Property: url 
     * {String} 
     */
    url: null,
    
    /** TBD 3.0 - reorder the parameters to the init function to put URL 
     *             as last, so we can continue to call tile.initialize() 
     *             without changing the arguments. 
     * 
     * Constructor: OpenLayers.Tile.MarkerTile
     * Constructor for a new <OpenLayers.Tile.MarkerTile> instance.
     * 
     * Parameters:
     * layer - {<OpenLayers.Layer>} layer that the tile will go in.
     * position - {<OpenLayers.Pixel>}
     * bounds - {<OpenLayers.Bounds>}
     * url - {<String>}
     * size - {<OpenLayers.Size>}
     */   
    initialize: function(layer, position, bounds, url, size) {
        OpenLayers.Tile.prototype.initialize.apply(this, arguments);
        this.url = url;        
        this.features = [];
    },

    /** 
     * APIMethod: destroy
     * nullify references to prevent circular references and memory leaks
     */
    destroy: function() {
        this.destroyAllFeatures();
        this.features = null;
        this.url = null;

    },

    /** 
     * Method: clear
     *  Clear the tile of any bounds/position-related data so that it can 
     *   be reused in a new location.
     */
    clear: function() {
        this.destroyAllFeatures();
    },
    
    /**
     * Method: draw
     * Check that a tile should be drawn, and load features for it.
     * 
     * Returns:
     * {Boolean} Always returns true.
     */

     //!!!dodi - cela funkcia - pridane vola sa layer.getURL(bounds)

    draw: function() {

        if (this.layer != this.layer.map.baseLayer && this.layer.reproject) {
            this.bounds = this.getBoundsFromBaseLayer(this.position);
        }

        if (!OpenLayers.Tile.prototype.draw.apply(this, arguments)) {
            return false; 
        }

        if (this.isLoading) {
        //if we're already loading, send 'reload' instead of 'loadstart'.
            this.events.triggerEvent("reload");
        } else {
            this.isLoading = true;
            this.events.triggerEvent("loadstart");
        }


        this.url = this.layer.getURL(this.bounds);

        this.loadFeaturesForRegion(this.requestSuccess, this.requestFailure);
        // This is a change--------------------------------
        this.drawn = true;
        return true;
    //-------------------------------------------------
    },
    
        /**
     * Method: moveTo
     * Reposition the tile.
     *
     * Parameters:
     * bounds - {<OpenLayers.Bounds>}
     * position - {<OpenLayers.Pixel>}
     * redraw - {Boolean} Call draw method on tile after moving.
     *     Default is true
     */
    moveTo: function (bounds, position, redraw) {

        this.destroyAllFeatures();
            
        OpenLayers.Tile.prototype.moveTo.apply(this, arguments);

        this.url = this.layer.getURL(this.bounds);

        this.loadFeaturesForRegion(this.requestSuccess, this.requestFailure);

    },


    /** 
    * Method: loadFeaturesForRegion
    * get the full request string from the ds and the tile params 
    *     and call the AJAX loadURL(). 
    *
    * Input are function pointers for what to do on success and failure.
    *
    * Parameters:
    * success - {function}
    * failure - {function}
    */
    loadFeaturesForRegion:function(success, failure) {
        OpenLayers.loadURL(this.url, null, this, success,failure);
    },
    
    /**
    * Method: requestSuccess
    * Called on return from request succcess. Adds results via 
    *
    * Parameters:
    * request - {XMLHttpRequest}
    */
    //!!!dodi - vlastne parsovanie z textoveho suboru
    requestFailure: function(request) {
     //alert("requestFailure");
    },

    requestSuccess: function(request) {
    
        this.clear();
    
        var text = request.responseText;
        var lines = text.split('\n');
        var columns;
        var mylocation, title, url;
        var icon, iconSize, iconOffset, popupSize, overflow;

        // length - 1 to allow for trailing new line
        for (var lcv = 0; lcv < (lines.length - 1); lcv++) {
            var currLine = lines[lcv].replace(/^\s*/,'').replace(/\s*$/,'');
            
            if (currLine.charAt(0) != '#') { /* not a comment */

                if (!columns) {
                    //First line is columns
                    columns = currLine.split('\t');
                } else {
                    mylocation = new OpenLayers.LonLat(0,0);
                    var vals = currLine.split('\t');
                    iconSize = new OpenLayers.Size(16,16);
                    iconOffset = new OpenLayers.Pixel(0,0);
                    popupSize = null;
                    title=null;
                    description=null;

                    var set = false;
                    
                    for (var valIndex = 0; valIndex < vals.length; valIndex++) {
                        if (vals[valIndex]) {
                            if (columns[valIndex] == 'point') {
                                var coords = vals[valIndex].split(',');
                                mylocation.lon = parseFloat(coords[0]);
                                mylocation.lat = parseFloat(coords[1]);
                                set = true;
                            } else if (columns[valIndex] == 'lat') {
                                mylocation.lat = parseFloat(vals[valIndex]);
                                set = true;
                            } else if (columns[valIndex] == 'lon') {
                                mylocation.lon = parseFloat(vals[valIndex]);
                                set = true;
                            } else if (columns[valIndex] == 'image' || columns[valIndex] == 'icon') {
                                url = vals[valIndex];
                            } else if (columns[valIndex] == 'iconSize') {
                                var size = vals[valIndex].split(',');
                                iconSize = new OpenLayers.Size(parseFloat(size[0]),parseFloat(size[1]));
                            } else if (columns[valIndex] == 'iconOffset') {
                                var offset = vals[valIndex].split(',');
                                iconOffset = new OpenLayers.Pixel(parseFloat(offset[0]), parseFloat(offset[1]));
                            } else if (columns[valIndex] == 'title') {
                                title = vals[valIndex];
                            } else if (columns[valIndex] == 'description') {
                                description = vals[valIndex];
                            } else if (columns[valIndex] == 'overflow') {
                                overflow = vals[valIndex];
                            } else if (columns[valIndex] == 'popupSize') {
                                var psize = vals[valIndex].split(',');
                                popupSize = new OpenLayers.Size(parseFloat(psize[0]),parseFloat(psize[1]));
                            }
                        }
                    }
                    if (set) {
                      var data = {};
                      
                       var PI = 3.14159265358979323846;
                        // MERCATORIZE
                       
                       mylocation.lon = mylocation.lon * 20037508.34 / 180;
                       mylocation.lat = (Math.log(Math.tan( (90 + mylocation.lat) * PI / 360)) / (PI / 180)) * 20037508.34 / 180;
                       
                       //location.lat = this.bounds.left;
                       //location.lon = this.bounds.top;
                       // var near_icon = OpenLayers.Marker.defaultIcon();

                       //marker = new OpenLayers.Marker(new OpenLayers.LonLat(lon_map, lat_map),near_icon.clone());

                       
                      if (url != null) {
                          data.icon = new OpenLayers.Icon(url, iconSize, iconOffset);
                      } else {
                          data.icon = OpenLayers.Marker.defaultIcon();

                          //allows for the case where the image url is not
                          // specified but the size is. use a default icon
                          // but change the size
                          //if (iconSize != null) {
                          //   data.icon.setSize(iconSize);
                          //}

                      }
                      if ((title != null) && (description != null)) {
                          data['popupContentHTML'] = '<b>'+title+'</b><br/>'+description;
                      }
                      
                      if (popupSize != null) {
                         data.popupSize = popupSize;
                      }

//                      data['overflow'] = overflow || "auto";

                      // We must track both features and markers so they can be properly deallocated later
                      var feature = new OpenLayers.Feature(this.layer, mylocation, data);
                      this.features.push(feature);
                      var marker = feature.createMarker();

                      if ((title != null) && (description != null)) {
                        marker.events.register('click', feature, this.markerClick);
                      }
                      this.layer.addMarker(marker);
                      //alert(ll);
                    }
                }
            }
        }
        if (this.events) {
            this.events.triggerEvent("loadend");
        }
    },
       /**
     * Property: markerClick
     *
     * Parameters:
     * evt - {Event}
     */
     //!!!dodi - registrovana funckia pre click na POI
   markerClick: function(evt) {

        sameMarkerClicked = (this == this.layer.selectedFeature);
        this.layer.selectedFeature = (!sameMarkerClicked) ? this : null;

        for(var i=0; i < this.layer.map.popups.length; i++) {
            this.layer.map.removePopup(this.layer.map.popups[i]);
        }

        this.layer.map.addPopup(this.createPopup());

        OpenLayers.Event.stop(evt);
    },


    /**
     * Method: destroyAllFeatures
     * Iterate through and call destroy() on each feature, removing it from
     *   the local array
     */
     //!!!dodi - ak ma feature marker... potom sa  najprv remove
    destroyAllFeatures: function() {
        if (this.features)
        {
            while(this.features.length > 0)
            {
                var feature = this.features.shift();
                if( feature.marker != null )
                   if( this.layer != null )
                          this.layer.removeMarker(feature.marker);
                feature.destroy();
           }
        }
    },

    CLASS_NAME: "OpenLayers.Tile.MarkerTile"
  }
);
