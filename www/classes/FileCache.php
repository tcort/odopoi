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

require_once('Cache.php');

class FileCache extends Cache {

	var $cache_dir = 'cache';
	var $max_files = 512;
	var $max_age = 259200; // 3 days in seconds

	protected function keyToFilename($key) {
		preg_match("/^[\._a-zA-Z0-9-]+$/", $key) or die('Invalid cache key "' . $key . '"');
		return $this->cache_dir . '/' . $key . '.txt';
	}

	public function get($key) {
		$filename = $this->keyToFilename($key);
		if (file_exists($filename)) {
			if ((filectime($filename) + $max_age) < time()) {
				unlink($filename) or die('Cannot delete stale cache file');
				return FALSE;
			} else {
				return @file_get_contents($filename);
			}
		} else {
			return FALSE;
		}
	}

	public function put($key, $value) {
		@file_put_contents($this->keyToFilename($key), $value) or die('Cache write fail');
	}
}

?>
