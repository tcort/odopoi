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

mb_language('uni');
mb_internal_encoding('UTF-8');

require_once('gpx.php');

class POIManager {
	var $db;

	public function POIManager($db) {
		$this->db = $db;
	}

	public function getWpts($min_lat, $max_lat, $min_lon, $max_lon, $zoom) {
		$sql = "SELECT lat, lon, name, descr, sym FROM poi WHERE lat BETWEEN '" . $this->db->escape($min_lat) . "' AND '" . $this->db->escape($max_lat) . "' AND lon BETWEEN '" . $this->db->escape($min_lon) . "' AND '" . $this->db->escape($max_lon) . "' AND zoom <= '" . $this->db->escape($zoom) . " ORDER BY RAND() LIMIT 500';";
		$result = $this->db->query($sql);
		$gpx = new gpx();

		while ($result->hasNext()) {
			$row = $result->next();
			$wpt = new wpt($row[0],$row[1]);
			$wpt->setName($row[2]);
			$wpt->setDesc($row[3]);
			$wpt->setSym($row[4]);

			$gpx->addWpt($wpt);
		}

		return $gpx;
	}

}

?>
