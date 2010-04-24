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

DROP TABLE IF EXISTS node;
CREATE TABLE node (
	id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
	version MEDIUMINT UNSIGNED NOT NULL,
	timestamp TIMESTAMP NOT NULL,
	lat DECIMAL(15,12) NOT NULL,
	lon DECIMAL(15,12) NOT NULL,
	zoom TINYINT NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS tag;
CREATE TABLE tag (
	node_id BIGINT UNSIGNED NOT NULL,
	k VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci,
	v TEXT NOT NULL COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX node_id_idx ON tag (node_id);
CREATE UNIQUE INDEX tag_key_idx ON tag (node_id,k);

DROP FUNCTION IF EXISTS dist_calc;
CREATE FUNCTION dist_calc (lat_a DECIMAL(15,12), lat_b DECIMAL(15,12), lon_a DECIMAL(15,12), lon_b DECIMAL(15,12))
RETURNS FLOAT DETERMINISTIC
RETURN ((DEGREES(ACOS((SIN(RADIANS(lat_a)) * SIN(RADIANS(lat_b))) + (COS(RADIANS(lat_a)) * COS(RADIANS(lat_b)) * COS(RADIANS(lon_a - lon_b)))))) * 69.09);

