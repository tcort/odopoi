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

	$result = mysql_query("SELECT lat, lon FROM poi WHERE zoom <= '" . $zoom . "' ORDER BY RAND();");
	while ($row = mysql_fetch_row($result)) {
		if (dist($lat, $lon, $row[0], $row[1]) < $mindist[$zoom]) {
			mysql_free_result($result);
			return 0;
		}
	}
	mysql_free_result($result);
	return 1;
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
