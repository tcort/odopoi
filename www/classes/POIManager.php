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

require_once('gpx.php');

class POIManager {
	var $db;

	public function POIManager($db) {
		$this->db = $db;
	}

	public function getValue($node_id, $key) {
		$sql = "SELECT v FROM tag WHERE node_id = '" . $this->db->escape($node_id) . "' AND k = '" . $this->db->escape($key) . "';";
		$result = $this->db->query($sql);
		if ($result->hasNext()) {
			$row = $result->next();
			return $row[0];
		} else {
			return "Unknown";
		}
	}

	public function getWebsite($node_id) {
		$website = $this->getValue($node_id, "website");
		if (strcmp("http://",substr($website,0,7))) {
			$website = "http://" . $website;
		}
		return $website;
	}

	public function getName($node_id) {
		$name = $this->getValue($node_id, "name");
		$website = $this->getWebsite($node_id);
		if (!strcmp($website,"http://Unknown")) {
			return $name;
		} else {
			return "<a href=\"" . $website . "\">" . $name . "</a>";
		}
	}

	public function getDesc($node_id) {
		$filter_keys = array("don't put anything here","amenity","tourism","shop","name","website","created_by");

		$desc = "<table class=\"poi\">";

		$sql = "SELECT k,v FROM tag WHERE node_id = '" . $this->db->escape($node_id) . "';";
		$result = $this->db->query($sql);
		if ($result->hasNext()) {
			$row = $result->next();
			if (array_search(trim($row[0]), $filter_keys) == FALSE) {
				$desc .= "<tr><td class=\"k\">" . $row[0] . "</td><td class=\"v\">" . $row[1] . "</td></tr>";
			}
		}

		return $desc . "</table>";
	}

	public function getSymbol($node_id) {
		$sym = $this->getValue($node_id, "amenity");
		if (strcmp($sym, "Unknown")) {
			return $sym;
		}

		$sym = $this->getValue($node_id, "tourism");
		if (strcmp($sym, "Unknown")) {
			return $sym;
		}

		$sym = $this->getValue($node_id, "shop");
		if (strcmp($sym, "Unknown")) {
			return $sym;
		}

		return $sym;
	}

	public function getWpts($min_lat, $max_lat, $min_lon, $max_lon, $zoom) {
		$sql = "SELECT id, lat, lon FROM node WHERE lat BETWEEN '" . $this->db->escape($min_lat) . "' AND '" . $this->db->escape($max_lat) . "' AND lon BETWEEN '" . $this->db->escape($min_lon) . "' AND '" . $this->db->escape($max_lon) . "' AND zoom <= '" . $this->db->escape($zoom) . " ORDER BY RAND() LIMIT 500';";
		$result = $this->db->query($sql);
		$gpx = new gpx();

		while ($result->hasNext()) {
			$row = $result->next();
			$wpt = new wpt($row[1],$row[2]);
			$wpt->setName($this->getName($row[0]));
			$wpt->setDesc($this->getDesc($row[0]));
			$wpt->setSym($this->getSymbol($row[0]));

			$gpx->addWpt($wpt);
		}

		return $gpx;
	}

}

?>
