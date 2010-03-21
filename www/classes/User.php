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

class User {
	var $username;
	var $password;
	var $sessionid;
	var $authenticated;
	var $accesslevel;

	var $accesslevelid;
	var $ip;
	var $userid;

	function User($db, $username, $password, $sid) {
		$this->db = $db;
		$this->username = $username;
		$this->password = $password;
		$this->sessionid = $sid;

		$this->ip = $this->__get_remote_ip();

		// Set 'safe' defaults
		$this->authenticated = FALSE;
		$this->userid        = -1;

		// Purge expired sessions. There is no need to keep the info in there.
		$db->exec("DELETE FROM sessions WHERE starttime < NOW() - INTERVAL 15 minute");
	}

	function login() {

		if ($this->sessionid != "") {
			// see if we already have an existing session
			$this->__existing_session();
		} else {
			$this->sessionid = $this->__gen_session_id();
		}

		if (!$this->authenticated && $this->username != "" && $this->password != "") {

			$s = $this->db->query("SELECT salt FROM users WHERE username='" . $this->db->escape($this->username) . "'");
			$salt = "";
			if (1 == $s->maxrows) {
				$row = $s->next();
				$salt = $row[0];
			}

			if (strlen($salt) == 32) {

				$r = $this->db->query("SELECT users.userid,users.accesslevel FROM users WHERE username='" . $this->db->escape($this->username) . "' and passwd='" . $this->db->escape(sha1($salt.$this->password.$salt)) . "'");

				if (1 == $r->maxrows) {

					$row = $r->next();
					$this->userid = $row[0];
					$this->accesslevelid = $row[1];
					$this->authenticated = TRUE;

					if (is_numeric($this->userid)) {
						$this->db->exec("DELETE FROM sessions WHERE userid = '" . $this->db->escape($this->userid) . "'");
					}

					if (is_string($this->sessionid) && $this->sessionid != "" && is_numeric($this->userid) && $this->ip != "") {
						$this->db->exec("INSERT INTO sessions (sessionid,userid,ip) VALUES ('" . $this->db->escape($this->sessionid) . "','" . $this->db->escape($this->userid) . "','" . $this->db->escape($this->ip) . "')");
					}
				}
			}
		}

		if ($this->authenticated && $this->accesslevelid && is_numeric($this->accesslevelid)) {
			$r = $this->db->query("SELECT name FROM accesslevels WHERE levelid = '" . $this->db->escape($this->accesslevelid) . "'");

			if (1 == $r->maxrows) {
				$row = $r->next();
				$this->accesslevel = $row[0];
			}
		}
	}

	function logout() {
		if (is_string($this->sessionid) && $this->sessionid != "") {
			$this->db->query("DELETE FROM sessions WHERE sessionid = '" . $this->db->escape($this->sessionid) . "'");
		}

		$this->authenticated = FALSE;
		$this->userid        = -1;
		$this->sessionid     = $this->__gen_session_id();
	}

	function __existing_session() {
		if (!$this->ip) {
			return;
		}

		if (!$this->sessionid) {
			$this->sessionid = $this->__gen_session_id();
			return;
		}

		// A valid existing session must have the same IP as us, the same session id
		// and must not have expired. If this is not the case, the user will be
		// given a new session id and must authenticate.
		$r = $this->db->query("SELECT users.username as username, users.userid as userid,users.accesslevel as accesslevel FROM sessions,users WHERE sessions.starttime > NOW() - INTERVAL 15 minute AND sessions.sessionid = '" . $this->db->escape($this->sessionid) . "' AND users.userid = sessions.userid AND sessions.ip = '" . $this->db->escape($this->ip) . "'");

		if (1 == $r->maxrows) {
			$row = $r->next();
			$this->username = $row[0];
			$this->userid = $row[1];
			$this->accesslevelid = $row[2];
			$this->authenticated = TRUE;
			$this->db->exec("UPDATE sessions SET starttime = NOW() WHERE userid = '" . $this->db->escape($this->userid) . "'");
		} else {
			$this->sessionid = $this->__gen_session_id();
		}
	}

	function __get_remote_ip() {

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];
		else
			$ip = "";

		// match any integer number in the range 0-255
		$range = "(25[0-5]|2[0-4]\d|1\d\d|\d\d|\d)";

		// $range matches any of these
		//
		// 	25[0-5]		=>	250 - 255
		// 	2[0-4]\d	=>	200 - 249
		// 	1\d\d		=>	100 - 199
		// 	\d\d		=>	 10 -  99
		// 	\d		=>	  0 -   9

		// match any IP
		$ip_regex = "/$range\\.$range\\.$range\\.$range/";

		// check for a valid IP.
		if (!$ip || !preg_match($ip_regex, $ip)) {
			// the variables above didn't give us an IP address
			return "";
		} else {
			return $ip;
		}
	}

	function __gen_session_id() {
		$x = rand(1000,9999);
		$a = $this->__get_remote_ip();
		$y = rand(100,999);
		$b = date("Ymd");
		$z = rand(10,99);
		$c = microtime(true);

		// This should give a fairly random 80 character session ID ;)
		return sha1($x . $a . $z) . sha1($c . $b . $y);
	}

}

?>
