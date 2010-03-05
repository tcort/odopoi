/* Copyright (c) 2006-2007 MetaCarta, Inc., published under a modified BSD license.
 * See http://svn.openlayers.org/trunk/openlayers/repository-license.txt 
 * for the full text of the license. */


/**
 * @requires OpenLayers/Layer.js
 * 
 * Class: OpenLayers.Layer.MarkerGrid
 * Base class for layers that use a lattice of tiles.  Create a new grid
 * layer with the <OpenLayers.Layer.MarkerGrid> constructor.
 *
 * Inherits from:
 *  - <OpenLayers.Layer>
 */
OpenLayers.Layer.MarkerGrid = OpenLayers.Class(OpenLayers.Layer.Grid, {
    

    /**
     * APIProperty: isBaseLayer
     * {Boolean}
     */
    isBaseLayer: true,

	 /**
     * APIProperty: tileOrigin
     * {<OpenLayers.Pixel>}
     */
    tileOrigin: null,

    /**
     * Property: markers
     * Array({<OpenLayers.Marker>}) internal marker list
     */
    markers: null,


    /**
     * Property: drawn
     * {Boolean} internal state of drawing. This is a workaround for the fact
     * that the map does not call moveTo with a zoomChanged when the map is
     * first starting up. This lets us catch the case where we have *never*
     * drawn the layer, and draw it even if the zoom hasn't changed.
     */
    drawn: false,

    /**
     * Constructor: OpenLayers.Layer.MarkerGrid
     * Create a new grid layer
     *
     * Parameters:
     * name - {String}
     * options - {Object} Hashtable of extra options to tag onto the layer
     */
    initialize: function(name, options) {
       
        var newArguments = [];
        newArguments.push(name, null, {}, options);
        OpenLayers.Layer.Grid.prototype.initialize.apply(this, newArguments);
        this.markers = [];
    },

    /**
     * APIMethod: destroy
     * Deconstruct the layer and clear the grid.
     */
    destroy: function() {
        
		this.clearGrid();

		if (this.markers.lenght>0) this.clearMarkers();

        this.markers = null;

		OpenLayers.Layer.Grid.prototype.destroy.apply(this, arguments);  
    },

    /**
     * APIMethod: clone
     *
     * Parameters:
     * obj - {Object} Is this ever used?
     * 
     * Returns:
     * {<OpenLayers.Layer.MarkerGrid>} An exact clone of this OpenLayers.Layer.MarkerGrid
     */
    clone: function (obj) {
        
        if (obj == null) {
            obj = new OpenLayers.Layer.MarkerGrid(this.name,
                                            this.options);
        }

        //get all additions from superclasses
        obj = OpenLayers.Layer.Grid.prototype.clone.apply(this, [obj]);

        // copy/set any non-init, non-simple values here
        obj.markers=[];

        return obj;
    },    

    /**
     * Method: moveTo
     * This function is called whenever the map is moved. All the moving
     * of actual 'tiles' is done by the map, but moveTo's role is to accept
     * a bounds and make sure the data that that bounds requires is pre-loaded.
     *
     * Parameters:
     * bounds - {<OpenLayers.Bounds>}
     * zoomChanged - {Boolean}
     * dragging - {Boolean}
     */
    moveTo:function(bounds, zoomChanged, dragging) {
        OpenLayers.Layer.Grid.prototype.moveTo.apply(this, arguments);

        if (zoomChanged || !this.drawn) {
            for(i=0; i < this.markers.length; i++) {
                this.drawMarker(this.markers[i]);
            }
            this.drawn = true;
        }
    },


     /**
     * Method: getUrl
     *
     * Parameters:
     * bounds - {<OpenLayers.Bounds>}
     *
     * Returns:
     * {String} A string with the layer's url and parameters and also the
     *          passed-in bounds and appropriate tile size specified as
     *          parameters
     */

     getURL: function (bounds) {
        // to bee implemented by user

    },




    /**
     * APIMethod: addTile
     * Gives subclasses of Grid the opportunity to create an 
     *
     * Parameters:
     * bounds - {<OpenLayers.Bounds>}
     *
     * Returns:
     * {<OpenLayers.Tile>} The added OpenLayers.Tile
     */
    addTile:function(bounds, position) {
            //return new OpenLayers.Tile.Image(this, position, bounds,
            //                                                 null, this.tileSize);
            return new OpenLayers.Tile.MarkerTile(this, position, bounds,
                                                             null, this.tileSize);
    },
    

    /** 
     * APIMethod: setMap
     * When the layer is added to a map, then we can fetch our origin 
     *    (if we don't have one.) 
     * 
     * Parameters:
     * map - {<OpenLayers.Map>}
     */
    setMap: function(map) {
        OpenLayers.Layer.Grid.prototype.setMap.apply(this, arguments);
        
        if (!this.tileOrigin) { 
            this.tileOrigin = new OpenLayers.LonLat(this.map.maxExtent.left,
                                                this.map.maxExtent.bottom);
        }                                       
    },


    Refresh: function() {
    // total bounds of the tiles
            var tilesBounds = this.getTilesBounds();
            this.initGriddedTiles(tilesBounds);
    },

     /**
     * APIMethod: addMarker
     *
     * Parameters:
     * marker - {<OpenLayers.Marker>}
     */
    addMarker: function(marker) {
        this.markers.push(marker);
        if (this.map && this.map.getExtent()) {
            marker.map = this.map;
            this.drawMarker(marker);
        }
    },

    /**
     * APIMethod: removeMarker
     *
     * Parameters:
     * marker - {<OpenLayers.Marker>}
     */
    removeMarker: function(marker) {
        OpenLayers.Util.removeItem(this.markers, marker);
        if ((marker.icon != null) && (marker.icon.imageDiv != null) &&
            (marker.icon.imageDiv.parentNode == this.div) ) {
            this.div.removeChild(marker.icon.imageDiv);
            marker.drawn = false;
        }
    },

    /**
     * Method: clearMarkers
     * This method removes all markers from a layer. The markers are not
     * destroyed by this function, but are removed from the list of markers.
     */
    clearMarkers: function() {
        if (this.markers != null) {
            while(this.markers.length > 0) {
                this.removeMarker(this.markers[0]);
            }
        }
    },

    /**
     * Method: drawMarker
     * Calculate the pixel location for the marker, create it, and
     *    add it to the layer's div
     *
     * Parameters:
     * marker - {<OpenLayers.Marker>}
     */
    drawMarker: function(marker) {
        var px = this.map.getLayerPxFromLonLat(marker.lonlat);
        if (px == null) {
            marker.display(false);
        } else {
            var markerImg = marker.draw(px);
            if (!marker.drawn) {
                this.div.appendChild(markerImg);
                marker.drawn = true;
            }
        }
    },
    
    CLASS_NAME: "OpenLayers.Layer.MarkerGrid"
});
