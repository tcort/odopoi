--
-- The content of this file is licensed under the terms of the following license:
--   Creative Commons Attribution-Share Alike 2.0 Generic License
--
-- The license text is available at the following URL:
--   http://creativecommons.org/licenses/by-sa/2.0/
--

DROP TABLE IF EXISTS poi;
CREATE TABLE poi (
	lat DECIMAL(15,12),
	lon DECIMAL(15,12),
	title VARCHAR(64),
	description TEXT,
	icon VARCHAR(64),
	iconSize VARCHAR(16),
	iconOffset VARCHAR(16)
);

CREATE INDEX coord on poi (lat,lon);
