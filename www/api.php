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

// UTF-8 enable this script
mb_language('uni');
mb_internal_encoding('UTF-8');

require_once('config.php');
require_once('classes/NoCache.php');
require_once('classes/FileCache.php');
require_once('classes/MySQLDatabase.php');
require_once('classes/POIManager.php');

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

		if (strcmp($cache_type,"FileCache") == 0) { 
			$cache = new FileCache();
		} else {
			$cache = new NoCache();
		}

		$xml = $cache->get($key);
		if ($xml == FALSE) {
			$db = new MySQLDatabase($hostname, $database, $username, $password);
			$db->connect();
			$poi = new POIManager($db);
			$gpx = $poi->getWpts($min_lat, $max_lat, $min_lon, $max_lon, $zoom);
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
