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

class wpt {
	var $id = 0;
	var $lat = 0;
	var $lon = 0;
	var $name = "";
	var $desc = "";
	var $sym = "";

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getLat() {
		return $this->lat;
	}

	public function setLat($lat) {
		if (-90.0 <= $lat && $lat <= 90.0) {
			$this->lat = $lat;
		} else {
			die('latitude value outside of acceptable range (-90.0 <= value <= 90.0)');
		}
	}

	public function getLon() {
		return $this->lon;
	}

	public function setLon($lon) {
		if (-180.0 <= $lon && $lon < 180.0) {
			$this->lon = $lon;
		} else {
			die('longitude value outside of acceptable range (-180.0 <= value < 180.0)');
		}
	}

	function __construct($lat, $lon) {
		$this->setLat($lat);
		$this->setLon($lon);
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setDesc($desc) {
		$this->desc = $desc;
	}

	public function getDesc() {
		return $this->desc;
	}

	public function setSym($sym) {
		$this->sym = $sym;
	}

	public function getSym() {
		return $this->sym;
	}
}

?>
