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

mb_language('uni');
mb_internal_encoding('UTF-8');

print "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';\n";
print "SET CHARACTER SET 'utf8';\n";
print "SET collation_connection = 'utf8_general_ci';\n";

$poi_title = "Bus Stop";

if (($handle = fopen("agency.txt", "r")) !== FALSE) {
	$first_line = 1;
	$agency_name = 0;
	$agency_url = 1;
	while (($line = fgetcsv($handle, 1024, ",")) !== FALSE) {
		if ($first_line == 1) {
			$first_line = 0;
			$agency_name = array_search("agency_name", $line);
			$agency_url = array_search("agency_url", $line);
		} else {
			$poi_title = '<a href="' . str_replace("'", "''", trim($line[$agency_url])) . '">' . str_replace("'", "''", trim($line[$agency_name])) . '</a> Stop';
		}
	}
	fclose($handle);
}

if (($handle = fopen("stops.txt", "r")) !== FALSE) {
	$first_line = 1;
	$stop_name = 1;
	$stop_lat = 3;
	$stop_lon = 4;
	while (($line = fgetcsv($handle, 1024, ",")) !== FALSE) {
		if ($first_line == 1) {
			$first_line = 0;
			$stop_name = array_search("stop_name", $line);
			$stop_lat = array_search("stop_lat", $line);
			$stop_lon = array_search("stop_lon", $line);
		} else {
			$sql = "INSERT INTO poi (lat,lon,zoom,name,descr,sym) VALUES ('" . trim($line[$stop_lat]) . "','" . trim($line[$stop_lon]) . "','0','" . $poi_title . "','" . str_replace("'", "''", trim($line[$stop_name])) . "','bus');\n";
			print $sql;
		}
	}
	fclose($handle);
}
?>
