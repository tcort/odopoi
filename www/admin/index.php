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

require_once('../config.php');
require_once('../classes/MySQLDatabase.php');
require_once('../classes/User.php');

if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
	header('WWW-Authenticate: Basic realm="OpenDataMap.ca"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'Authentication Failed.';
	exit;
}

$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : "";
$db = new MySQLDatabase($hostname, $database, $username, $password);
$db->connect();
$user = new User($db, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $sid);
$user->login();

$db->disconnect();

if ($user->authenticated) {
	echo "Logged in";
} else {
	header('WWW-Authenticate: Basic realm="OpenDataMap.ca"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'Authentication Failed.';
	exit;
}

?>
