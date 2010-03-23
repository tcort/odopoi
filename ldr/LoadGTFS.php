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

mb_language('uni');
mb_internal_encoding('UTF-8');

print "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';\n";
print "SET CHARACTER SET 'utf8';\n";
print "SET collation_connection = 'utf8_general_ci';\n";

$poi_title = "Transit Stop";
$transit_co = "";

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
			$transit_co = trim($line[$agency_name]);
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
			$stop_id = array_search("stop_id", $line);
			$stop_name = array_search("stop_name", $line);
			$stop_lat = array_search("stop_lat", $line);
			$stop_lon = array_search("stop_lon", $line);
		} else {
			$stop_detail_link = "";
			if ($transit_co == "OC Transpo") {
				$stop_detail_link = '<p><a href="http://octranspo.net/stops/' . trim($line[$stop_id]) . '">View Schedule</a></p>';
			}

			$sql = "INSERT INTO poi (lat,lon,zoom,name,descr,sym) VALUES ('" . trim($line[$stop_lat]) . "','" . trim($line[$stop_lon]) . "','0','" . $poi_title . "','" . str_replace("'", "''", trim('<p>' . $line[$stop_name]) . '</p>' . $stop_detail_link) . "','bus');\n";
			print $sql;
		}
	}
	fclose($handle);
}
?>
