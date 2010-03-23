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
