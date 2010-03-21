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

#settings
$file = 'planet-100317.osm';
$lat_min = 45.00;
$lat_max = 46.00;
$lon_min = -76.00;
$lon_max = -75.00;
$lic = '<p><small>This point of interest is copyright <a href="http://www.openstreetmap.org/">OpenStreetMap</a> and its contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>.</small></p>';

$parsing_node = FALSE;
$lat = 0.0;
$lon = 0.0;
$tags = array();

function startElement($parser, $name, $attrs) {
	global $parsing_node, $lat, $lon, $tags;

	if ($name == "NODE") {
		$parsing_node = TRUE;
		$lat = $attrs["LAT"];
		$lon = $attrs["LON"];
	}

	if ($name == "TAG" && $parsing_node == TRUE) {
		$tags[$attrs["K"]] = $attrs["V"];
	}
}

function endElement($parser, $name) {
	global $parsing_node, $lat, $lon, $tags, $lat_min, $lat_max, $lon_min, $lon_max, $lic;

	if ($name == "NODE" && $parsing_node == TRUE) {
		if (($lat_min <= $lat && $lat <= $lat_max && $lon_min <= $lon && $lon <= $lon_max)) {
			if (isset($tags["amenity"]) && isset($tags["name"])) {
				$desc = addslashes($lic);
				if (isset($tags["url"])) {
					$desc = '<p><a href="' . addslashes($tags["url"]) . '">Visit Homepage</a></p>' . addslashes($lic);
				}

				switch ($tags["amenity"]) {
					case "cafe":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','coffee');\n";
						break;
					case "cinema":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','cinema');\n";
						break;
					case "pub":
					case "bar":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','cocktail');\n";
						break;
					case "restaurant":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','restaurant');\n";
						break;
				}
			}
		}

		$parsing_node = FALSE;
		$lat = 0.0;
		$lon = 0.0;
		$tags = array();
	}

}

$xml_parser = xml_parser_create();

xml_set_element_handler($xml_parser, "startElement", "endElement");

if (!($fp = fopen($file, "r"))) {
	die("could not open XML input");
}

while ($data = fread($fp, 4096)) {
	if (!xml_parse($xml_parser, $data, feof($fp))) {
		die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
	}
}

xml_parser_free($xml_parser);
?>
