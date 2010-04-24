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
		$root->setAttribute('version', htmlspecialchars($this->version));
		$root->setAttribute('creator', htmlspecialchars($this->creator));

		$metaElem = $dom->createElement('metadata');
		$copyrightElem = $dom->createElement('copyright');
		$copyrightElem->setAttribute('author','OpenStreetMap and Contributors');
		$licenseElem = $dom->createElement('license', htmlspecialchars('http://creativecommons.org/licenses/by-sa/2.0/'));
		$copyrightElem->appendChild($licenseElem);
		$metaElem->appendChild($copyrightElem);
		$root->appendChild($metaElem);

		foreach ($this->wpts as $wpt) {
			$wptElem = $dom->createElement('wpt');
			$wptElem->setAttribute('lat', htmlspecialchars($wpt->getLat()));
			$wptElem->setAttribute('lon', htmlspecialchars($wpt->getLon()));

			$nameElem = $dom->createElement('name', htmlspecialchars($wpt->getName()));
			$wptElem->appendChild($nameElem);

			$descElem = $dom->createElement('desc', htmlspecialchars($wpt->getDesc()));
			$wptElem->appendChild($descElem);

			$symElem = $dom->createElement('sym', htmlspecialchars($wpt->getSym()));
			$wptElem->appendChild($symElem);

			$root->appendChild($wptElem);
		}

		$dom->appendChild($root);
		$dom->formatOutput = true;
		return $dom->saveXML();
	}
}

?>
