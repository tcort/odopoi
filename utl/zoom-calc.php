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

require_once('../www/config.php');

# X=>Y -- at zoom level X, the minimum distance in miles between markers should be Y to prevent icons from overlaping.
$mindist = array(
	2621.44,
	1310.72,
	655.36,
	327.68,
	163.84,
	81.92,
	40.96,
	20.48,
	10.24,
	5.12,
	2.56,
	1.28,
	0.64,
	0.32,
	0.16,
	0.08,
	0.04,
	0.02,
	0.01
);

# return the distance between the 2 coordinates in miles
function dist($lat_A, $lon_A, $lat_B, $lon_B) {
	return (rad2deg(acos(sin(deg2rad($lat_A)) * sin(deg2rad($lat_B)) + cos(deg2rad($lat_A)) * cos(deg2rad($lat_B)) * cos(deg2rad($lon_A - $lon_B))))) * 69.09;
}

function can_place_at_zoom($lat, $lon, $zoom) {
	global $mindist;

	$result = mysql_query("SELECT dist_calc(" . $lat . ",lat," . $lon . ",lon) AS dist FROM poi WHERE zoom <= '" . $zoom . "' HAVING dist < " . $mindist[$zoom] . ";");

	$rowcnt = mysql_num_rows($result);
	mysql_free_result($result);
	return ($rowcnt == 0);

//	$result = mysql_query("SELECT lat, lon FROM poi WHERE zoom <= '" . $zoom . "' ORDER BY RAND();");
//	while ($row = mysql_fetch_row($result)) {
//		if (dist($lat, $lon, $row[0], $row[1]) < $mindist[$zoom]) {
//			mysql_free_result($result);
//			return 0;
//		}
//	}
//	mysql_free_result($result);
//	return 1;
}

// Connect to the Database
@mysql_connect($hostname, $username, $password) or die("Unable to connect to database");
@mysql_select_db($database) or die("Unable to select database");

// UTF-8 enable the database connection
@mysql_set_charset('utf8');

@mysql_query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
@mysql_query("SET CHARACTER SET 'utf8'");
@mysql_query("SET collation_connection = 'utf8_general_ci'");

// set all poi to zoom 18
mysql_query("UPDATE poi SET zoom = '18';");

// randomly bring one up to the top level
mysql_query("UPDATE poi SET zoom = '0' WHERE zoom = '18' ORDER BY RAND() LIMIT 1;");

for ($current_level = 0; $current_level < 18; $current_level++) {

	// go through the lowest level to see if there are any candidates to bring up
	$result = mysql_query("SELECT lat, lon FROM poi WHERE zoom = '18' ORDER BY RAND();");
	while ($row = mysql_fetch_row($result)) {
		if (can_place_at_zoom($row[0], $row[1], $current_level) == 1) {
			mysql_query("UPDATE poi SET zoom = '" . $current_level . "' WHERE lat = " . $row[0] . " AND lon = " . $row[1] . ";");
		}
	}
	mysql_free_result($result);

	print "Completed Zoom Level: " . $current_level . "\n";
}



mysql_close();
?>
