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

class DatabaseResult {
	var $result;
	var $rownum;
	var $arr;
	var $maxrows;
	var $sql;
	var $db;

	function DatabaseResult($db, $result, $sql) {
		$this->db = $db;
		$this->sql = $sql;
		$this->maxrows = $this->db->getMaxRows($result);
		$this->result = $result;
		$this->arr = NULL;
		$this->rownum = 0;
	}

	function hasNext() {
		return $this->rownum < $this->maxrows;
	}

	function next() {
		if ($this->hasNext()) {
			$this->arr = $this->db->fetchArray($this->result);
			$this->rownum++;
			return $this->arr;
		} else {
			return NULL;
		}
	}
}

?>
