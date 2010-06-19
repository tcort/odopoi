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
	2600.00,
	1300.00,
	650.00,
	325.00,
	164.00,
	81.00,
	40.50,
	20.40,
	10.20,
	5.10,
	2.50,
	1.20,
	0.60,
	0.30,
	0.15,
	0.07,
	0.03,
	0.01,
	0.01
);

# mindist in degrees to avoid overlap
$dlat = array(
	38.0,
	19.0,
	9.49,
	4.75,
	2.38,
	1.201,
	0.6,
	0.3,
	0.15,
	0.075,
	0.038,
	0.019,
	0.0093,
	0.0047,
	0.0024,
	0.0012,
	0.0006,
	0.0003,
	0.0002
);

# mindist in degrees to avoid overlap
$dlon = array(
	54.8,
	26.96,
	13.45,
	6.73,
	3.36,
	1.68,
	0.84,
	0.42,
	0.21,
	0.105,
	0.053,
	0.027,
	0.014,
	0.0066,
	0.0033,
	0.0017,
	0.0009,
	0.0005,
	0.0003
);

function can_place_at_zoom($lat, $lon, $zoom) {
	global $mindist;
	global $dlat;
	global $dlon;

	$result = mysql_query("SELECT dist_calc(" . $lat . ",lat," . $lon . ",lon) AS dist FROM node WHERE zoom <= '" . $zoom . "' AND (lat BETWEEN " . ($lat - $dlat[$zoom]) . " AND " . ($lat + $dlat[$zoom]) . ") AND (lon BETWEEN " . ($lon - $dlon[$zoom]) . " AND " . ($lon + $dlon[$zoom]) . ") HAVING dist < " . $mindist[$zoom] . ";");
	$rowcnt = mysql_num_rows($result);
	mysql_free_result($result);
	return ($rowcnt == 0);
}

// Connect to the Database
@mysql_connect($hostname, $username, $password) or die("Unable to connect to database");
@mysql_select_db($database) or die("Unable to select database");

// UTF-8 enable the database connection
@mysql_set_charset('utf8');

@mysql_query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
@mysql_query("SET CHARACTER SET 'utf8'");
@mysql_query("SET collation_connection = 'utf8_general_ci'");

// set all node to zoom 18
mysql_query("UPDATE node SET zoom = '18';");

// randomly bring one up to the top level
mysql_query("UPDATE node SET zoom = '0' WHERE zoom = '18' ORDER BY RAND() LIMIT 1;");

for ($current_level = 0; $current_level < 18; $current_level++) {

	// go through the lowest level to see if there are any candidates to bring up
	$result = mysql_query("SELECT lat, lon FROM node WHERE zoom = '18' ORDER BY RAND();");
	while ($row = mysql_fetch_row($result)) {
		if (can_place_at_zoom($row[0], $row[1], $current_level) == 1) {
			mysql_query("UPDATE node SET zoom = '" . $current_level . "' WHERE lat = " . $row[0] . " AND lon = " . $row[1] . ";");
		}
	}
	mysql_free_result($result);

	print "Completed Zoom Level: " . $current_level . "\n";
}



mysql_close();
?>
