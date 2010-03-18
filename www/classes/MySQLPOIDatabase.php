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
require_once('POIDatabase.php');

class MySQLPOIDatabase extends POIDatabase {
	var $connection = 0;

	protected function getConnection() {
		return $this->connection;
	}

	protected function setConnection($connection) {
		$this->connection = $connection;
	}

	public function getWpts($min_lat, $max_lat, $min_lon, $max_lon, $zoom) {
		$sql = "SELECT lat, lon, name, descr, sym FROM poi WHERE lat BETWEEN '" . mysql_real_escape_string($min_lat, $this->getConnection()) . "' AND '" . mysql_real_escape_string($max_lat, $this->getConnection()) . "' AND lon BETWEEN '" . mysql_real_escape_string($min_lon, $this->getConnection()) . "' AND '" . mysql_real_escape_string($max_lon, $this->getConnection()) . "' AND zoom <= '" . mysql_real_escape_string($zoom, $this->getConnection()) . " ORDER BY RAND() LIMIT 500';";
		$result = @mysql_query($sql) or die(mysql_error());
		$gpx = new gpx();

		while ($row = mysql_fetch_row($result)) {
			$wpt = new wpt($row[0],$row[1]);
			$wpt->setName($row[2]);
			$wpt->setDesc($row[3]);
			$wpt->setSym($row[4]);

			$gpx->addWpt($wpt);
		}

		return $gpx;
	}

	public function connect() {
		$connection = @mysql_connect($this->getHostname(), $this->getUsername(), $this->getPassword()) or die(mysql_error());
		$this->setConnection($connection);
		@mysql_select_db($this->getDatabase(), $this->getConnection()) or die(mysql_error());

		@mysql_set_charset('utf8', $this->getConnection()) or die(mysql_error());

		@mysql_query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'", $this->getConnection()) or die(mysql_error());
		@mysql_query("SET CHARACTER SET 'utf8'", $this->getConnection()) or die(mysql_error());
		@mysql_query("SET collation_connection = 'utf8_general_ci'", $this->getConnection()) or die(mysql_error());
	}

	public function isConnected() {
		return ($this->getConnection() != 0);
	}

	public function disconnect() {
		if ($this->isConnected()) {
			@mysql_close($this->getConnection()) or die(mysql_error());		
			$this->setConnection(0);
		}
	}
}

?>
