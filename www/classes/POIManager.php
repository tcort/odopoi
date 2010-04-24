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

	public function getWpts($min_lat, $max_lat, $min_lon, $max_lon, $zoom) {
		$sql = "SELECT lat, lon FROM node WHERE lat BETWEEN '" . $this->db->escape($min_lat) . "' AND '" . $this->db->escape($max_lat) . "' AND lon BETWEEN '" . $this->db->escape($min_lon) . "' AND '" . $this->db->escape($max_lon) . "' AND zoom <= '" . $this->db->escape($zoom) . " ORDER BY RAND() LIMIT 500';";
		$result = $this->db->query($sql);
		$gpx = new gpx();

		while ($result->hasNext()) {
			$row = $result->next();
			$wpt = new wpt($row[0],$row[1]);
			$wpt->setName("XXX");
			$wpt->setDesc("XXX");
			$wpt->setSym("pizza");

			$gpx->addWpt($wpt);
		}

		return $gpx;
	}

}

?>
