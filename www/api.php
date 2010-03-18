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

// UTF-8 enable this script
mb_language('uni');
mb_internal_encoding('UTF-8');

require_once('config.php');
require_once('classes/FileCache.php');
require_once('classes/MySQLPOIDatabase.php');

if (strcmp($_REQUEST["action"], "getPOI") == 0) {

	// Input parameters
	$tllon = $_REQUEST["tllon"];
	$tllat = $_REQUEST["tllat"];
	$brlon = $_REQUEST["brlon"];
	$brlat = $_REQUEST["brlat"];
	$zoom  = $_REQUEST["zoom"];

	// Validate the input parameters
	if (is_numeric($tllon) && -180.0 <= $tllon && $tllon < 180.0 && is_numeric($tllat) && -90.0 <= $tllat && $tllat <= 90.0 && is_numeric($brlon) && -180.0 <= $brlon && $brlon < 180.0 && is_numeric($brlat) && -90.0 <= $brlat && $brlat <= 90.0 && is_numeric($zoom) && $zoom >= 0 && $zoom < 20) {
		$min_lat = ($tllat < $brlat) ? $tllat : $brlat;
		$max_lat = ($tllat > $brlat) ? $tllat : $brlat;
		$min_lon = ($tllon < $brlon) ? $tllon : $brlon;
		$max_lon = ($tllon > $brlon) ? $tllon : $brlon;

		$key = "poi_" . $min_lat . "_" . $max_lat . "_" . $min_lon . "_" . $max_lon . "_" . $zoom;
		$cache = new FileCache();
		$xml = $cache->get($key);
		if ($xml == FALSE) {
			$db = new MySQLPOIDatabase($hostname, $database, $username, $password);
			$db->connect();
			$gpx = $db->getWpts($min_lat, $max_lat, $min_lon, $max_lon, $zoom);
			$xml = $gpx->toXml();
			$db->disconnect();

			$cache->put($key,$xml);
		}

		header("Content-type: text/xml; charset=utf-8");
		print $xml;
	} else {
		header("Content-type: text/plain; charset=utf-8");
		print "Invalid Input";
	}
} else {
	header("Content-type: text/plain; charset=utf-8");
	print "Unsupported Action";
}

?>
