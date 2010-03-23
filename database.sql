-- Copyright (C) 2010 Thomas Cort <linuxgeek@gmail.com>
--
-- All rights reserved.
--
-- Redistribution and use in source and binary forms, with or without
-- modification, are permitted (subject to the limitations in the
-- disclaimer below) provided that the following conditions are met:
--
--  * Redistributions of source code must retain the above copyright
--    notice, this list of conditions and the following disclaimer.
--
--  * Redistributions in binary form must reproduce the above copyright
--    notice, this list of conditions and the following disclaimer in the
--    documentation and/or other materials provided with the
--    distribution.
--
--  * Neither the name of Thomas Cort nor the names of its
--    contributors may be used to endorse or promote products derived
--    from this software without specific prior written permission.
--
-- NO EXPRESS OR IMPLIED LICENSES TO ANY PARTY'S PATENT RIGHTS ARE
-- GRANTED BY THIS LICENSE.  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT
-- HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
-- WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
-- MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
-- DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
-- LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
-- CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
-- SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
-- BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
-- WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
-- OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
-- IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';
SET CHARACTER SET 'utf8';
SET collation_connection = 'utf8_general_ci';

DROP TABLE IF EXISTS poi;
CREATE TABLE poi (
	id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	lat DECIMAL(15,12) NOT NULL,
	lon DECIMAL(15,12) NOT NULL,
	zoom TINYINT NOT NULL DEFAULT '0',
	name VARCHAR(64) COLLATE utf8_unicode_ci,
	descr TEXT COLLATE utf8_unicode_ci,
	sym VARCHAR(64) COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX wpt on poi (lat,lon,zoom);

DROP TABLE IF EXISTS accesslevels;
CREATE TABLE accesslevels (
	levelid int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name varchar(64) COLLATE utf8_unicode_ci DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO accesslevels (name) VALUES ('admin');

DROP TABLE IF EXISTS users;
CREATE TABLE users (
	userid int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	username varchar(32) NOT NULL DEFAULT '' COLLATE utf8_unicode_ci,
	passwd varchar(40) NOT NULL DEFAULT '' COLLATE utf8_unicode_ci,
	accesslevel int(10) UNSIGNED NOT NULL DEFAULT '0',
	salt char(32) NOT NULL DEFAULT '' COLLATE utf8_unicode_ci,
	UNIQUE KEY username (username)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO users (username,passwd,accesslevel,salt) VALUES ('admin','709ea9ccee88bcdd5e67e8dfc6b469b71a01646c',1,'21232f297a57a5a743894a0e4a801fc3');

DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
	sessionid CHAR(80) NOT NULL PRIMARY KEY COLLATE utf8_unicode_ci,
	starttime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	userid int(10) UNSIGNED NOT NULL,
	ip CHAR(15) NOT NULL DEFAULT '' COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHARACTER SET utf8 COLLATE utf8_general_ci;

