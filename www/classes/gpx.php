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

require_once('wpt.php');
require_once('Version.php');

class gpx {
	var $version = "1.1";
	var $creator = "OpenDataOttawa.ca";

	var $wpts = array();

	public function setCreator($creator) {
		$this->creator = $creator;
	}

	public function __construct() {
		$ver = new Version();
		$this->setCreator($ver->program . ' ' . $ver->version);
	}

	public function addWpt($wpt) {
		if ($wpt instanceof wpt) {
			array_push($this->wpts, $wpt);
		} else {
			die('Parameter passed to gpx::addWpt($wpt) was not of type wpt.');
		}
	}

	public function toXml() {
		$dom = new DOMDocument('1.0', 'utf-8');		

		$root = $dom->createElementNS('http://www.topografix.com/GPX/1/1', 'gpx');
		$root->setAttribute('version', htmlentities($this->version));
		$root->setAttribute('creator', htmlentities($this->creator));

		foreach ($this->wpts as $wpt) {
			$wptElem = $dom->createElement('wpt');
			$wptElem->setAttribute('lat', htmlentities($wpt->getLat()));
			$wptElem->setAttribute('lon', htmlentities($wpt->getLon()));

			$nameElem = $dom->createElement('name', htmlentities($wpt->getName()));
			$wptElem->appendChild($nameElem);

			$descElem = $dom->createElement('desc', htmlentities($wpt->getDesc()));
			$wptElem->appendChild($descElem);

			$symElem = $dom->createElement('sym', htmlentities($wpt->getSym()));
			$wptElem->appendChild($symElem);

			$root->appendChild($wptElem);
		}

		$dom->appendChild($root);
		$dom->formatOutput = true;
		return $dom->saveXML();
	}
}

?>
