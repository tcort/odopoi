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

require_once('Cache.php');

class FileCache extends Cache {

	var $cache_dir = 'cache';
	var $max_files = 2048;
	var $max_age = 259200; // 3 days in seconds

	protected function keyToFilename($key) {
		(strlen($key) > 10 && preg_match("/^[\._a-zA-Z0-9-]+$/", $key)) or die('Invalid cache key "' . $key . '"');
		return $this->cache_dir . '/' . $key . '.txt';
	}

	public function get($key) {
		$filename = $this->keyToFilename($key);
		if (file_exists($filename)) {
			$mtime = @filemtime($filename);
			if ($mtime == FALSE) {
				return FALSE;
			}
			if (time() - $mtime > $this->max_age) {
				@unlink($filename);
				return FALSE;
			} else {
				return @file_get_contents($filename);
			}
		} else {
			return FALSE;
		}
	}

	public function put($key, $value) {
		$files = scandir($this->cache_dir);
		if (count($files) > $this->max_files) {
			// start by removing stale files
			foreach ($files as $file) {
				$mtime = @filemtime($this->cache_dir . '/' . $file);
				if ($mtime == FALSE) {
					break;
				}
				if ((strlen($file) > 10) && (time() - $mtime > $this->max_age)) {
					@unlink($this->cache_dir . '/' . $file);
				}
			}

			$files = scandir($this->cache_dir);
			if (count($files) > $this->max_files) {
				$delcnt = (int) ($this->max_files * 0.35);
				for ($i = 0; $i < count($files) && $i < $delcnt; $i++) {
					if (strlen($files[$i]) > 10) {
						@unlink($this->cache_dir . '/' . $files[$i]);
					}
				}
			}
		}

		@file_put_contents($this->keyToFilename($key), $value) or die('Cache write fail');
	}
}

?>
