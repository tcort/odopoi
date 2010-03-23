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

require_once('DatabaseResult.php');

abstract class Database {
	var $hostname;
	var $database;
	var $username;
	var $password;

	protected function getHostname() {
		return $this->hostname;
	}

	protected function getDatabase() {
		return $this->database;
	}

	protected function getUsername() {
		return $this->username;
	}

	protected function getPassword() {
		return $this->password;
	}

	protected function setHostname($hostname) {
		$this->hostname = $hostname;
	}

	protected function setDatabase($database) {
		$this->database = $database;
	}

	protected function setUsername($username) {
		$this->username = $username;
	}

	protected function setPassword($password) {
		$this->password = $password;
	}


	function __construct($hostname, $database, $username, $password) {
		$this->setHostname($hostname);
		$this->setDatabase($database);
		$this->setUsername($username);
		$this->setPassword($password);
	}

	abstract public function isConnected();
	abstract public function connect();
	abstract public function disconnect();
	abstract public function query($sql);
	abstract public function exec($sql);
	abstract public function escape($str);
	abstract public function getMaxRows($dbresult);
	abstract public function fetchArray($dbresult);

	function __destruct() {
		if ($this->isConnected()) {
			$this->disconnect();
		}
	}
}

?>
