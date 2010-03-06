--
-- The content of this file is licensed under the terms of the following license:
--   Creative Commons Attribution-Share Alike 2.0 Generic License
--
-- The license text is available at the following URL:
--   http://creativecommons.org/licenses/by-sa/2.0/
--

DROP TABLE IF EXISTS POI;
CREATE TABLE poi (
	lat DECIMAL(10,7),
	lon DECIMAL(10,7),
	title VARCHAR(32),
	description TEXT,
	icon VARCHAR(64),
	iconSize VARCHAR(16),
	iconOffset VARCHAR(16)
);

INSERT INTO poi VALUES ('45.437153','-75.709007','SACRE COEUR / LAURIER','STO Bus Stop','./img/bus-sto.png','24,24','0,0');
