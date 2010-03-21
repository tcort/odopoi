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
