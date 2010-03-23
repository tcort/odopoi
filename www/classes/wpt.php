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
