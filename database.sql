-- Open Data Ottawa Points of Interest 
-- Copyright (C) 2010 Thomas Cort <linuxgeek@gmail.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Affero General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU Affero General Public License for more details.
--
-- You should have received a copy of the GNU Affero General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

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
