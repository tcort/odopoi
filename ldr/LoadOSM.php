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

ini_set('memory_limit', '384M');

print "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';\n";
print "SET CHARACTER SET 'utf8';\n";
print "SET collation_connection = 'utf8_general_ci';\n";

#settings
$file = 'planet-100317.osm';
$lat_min = 45.00;
$lat_max = 47.00;
$lon_min = -77.00;
$lon_max = -74.00;
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
				} elseif (isset($tags["website"])) {
					$desc = '<p><a href="' . addslashes($tags["website"]) . '">Visit Homepage</a></p>' . addslashes($lic);
				}

				if (isset($tags["cuisine"])) {
					$desc = '<p><em>Cuisine:</em> ' . addslashes($tags["cuisine"]) . '</p>';
				}

				if (isset($tags["opening_hours"])) {
					$desc = '<p><em>Hours:</em> ' . addslashes($tags["opening_hours"]) . '</p>';
				}

				switch ($tags["amenity"]) {
					case "hospital":
					case "cinema":
					case "bank":
					case "library":
					case "restaurant":
					case "convenience":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','" . $tags["amenity"] . "');\n";
						break;
					case "pub":
					case "bar":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','cocktail');\n";
						break;
					case "theatre":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','theater');\n";
						break;
					case "fast_food":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','fastfood');\n";
						break;
					case "cafe":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','coffee');\n";
						break;
					case "fuel":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','gazstation');\n";
						break;
				}
			} elseif (isset($tags["amenity"])) {
				$desc = addslashes($lic);
				switch ($tags["amenity"]) {
					case "toilets":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','Public Washrooms','$desc','toilet');\n";
						break;
					case "fountain":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','Fountain','$desc','fountain');\n";
						break;

				}

			} elseif (isset($tags["shop"]) && isset($tags["name"])) {
				$desc = addslashes($lic);
				if (isset($tags["url"])) {
					$desc = '<p><a href="' . addslashes($tags["url"]) . '">Visit Homepage</a></p>' . addslashes($lic);
				} elseif (isset($tags["website"])) {
					$desc = '<p><a href="' . addslashes($tags["website"]) . '">Visit Homepage</a></p>' . addslashes($lic);
				}

				if (isset($tags["opening_hours"])) {
					$desc = '<p><em>Hours:</em> ' . addslashes($tags["opening_hours"]) . '</p>';
				}

				switch ($tags["shop"]) {
					case "supermarket":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','supermarket');\n";
						break;
				}
			} elseif (isset($tags["tourism"]) && isset($tags["name"])) {
				$desc = addslashes($lic);
				if (isset($tags["url"])) {
					$desc = '<p><a href="' . addslashes($tags["url"]) . '">Visit Homepage</a></p>' . addslashes($lic);
				} elseif (isset($tags["website"])) {
					$desc = '<p><a href="' . addslashes($tags["website"]) . '">Visit Homepage</a></p>' . addslashes($lic);
				}

				switch ($tags["tourism"]) {
					case "hotel":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','hotel');\n";
						break;
				}
			} elseif (isset($tags["historic"]) && isset($tags["name"])) {
				$desc = addslashes($lic);
				if (isset($tags["url"])) {
					$desc = '<p><a href="' . addslashes($tags["url"]) . '">Visit Homepage</a></p>' . addslashes($lic);
				} elseif (isset($tags["website"])) {
					$desc = '<p><a href="' . addslashes($tags["website"]) . '">Visit Homepage</a></p>' . addslashes($lic);
				}

				switch ($tags["historic"]) {
					case "monument":
						print "INSERT INTO poi (lat, lon, name, descr, sym) VALUES ('$lat','$lon','" . addslashes($tags["name"]) . "','$desc','monument');\n";
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

while ($data = fread($fp, 134217728)) {
	if (!xml_parse($xml_parser, $data, feof($fp))) {
		die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
	}
}

xml_parser_free($xml_parser);
?>
