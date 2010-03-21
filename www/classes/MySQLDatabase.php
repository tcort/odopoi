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

require_once('Database.php');

class MySQLDatabase extends Database {
	var $connection = 0;

	protected function getConnection() {
		return $this->connection;
	}

	protected function setConnection($connection) {
		$this->connection = $connection;
	}

	public function connect() {
		if ($this->isConnected()) {
			return;
		}

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

	public function getMaxRows($dbresult) {
		return mysql_num_rows($dbresult->result);
	}

	public function fetchArray($dbresult) {
		return mysql_fetch_array($dbresult->result);
	}

	public function query($sql) {
		$result = mysql_query($sql, $this->connection) or die(mysql_error());
		return new DatabaseResult($this, $result, $sql);
	}

	public function escape($str) {
		return mysql_real_escape_string($str, $this->getConnection());
	}

	public function exec($sql) {
		$result = mysql_query($sql, $this->connection);
		if (mysql_affected_rows($this->connection) > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}

?>
