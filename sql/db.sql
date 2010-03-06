--
-- The content of this file is licensed under the terms of the following license:
--   Creative Commons Attribution-Share Alike 2.0 Generic License
--
-- The license text is available at the following URL:
--   http://creativecommons.org/licenses/by-sa/2.0/
--

DROP TABLE IF EXISTS poi;
CREATE TABLE poi (
	lat DECIMAL(10,7),
	lon DECIMAL(10,7),
	title VARCHAR(32),
	description TEXT,
	icon VARCHAR(64),
	iconSize VARCHAR(16),
	iconOffset VARCHAR(16)
);

INSERT INTO poi VALUES ('45.43122','-75.70975','6936 / 6838','STO Bus Stop','./img/bus-sto.png','24,24','0,0');
INSERT INTO poi VALUES ('45.43745','-75.7101','6838','STO Bus Stop','./img/bus-sto.png','24,24','0,0');
INSERT INTO poi VALUES ('45.44055','-75.73135','5959 / 5936','STO Bus Stop','./img/bus-sto.png','24,24','0,0');
INSERT INTO poi VALUES ('45.44027','-75.7319','5958 / 5931','STO Bus Stop','./img/bus-sto.png','24,24','0,0');
INSERT INTO poi VALUES ('45.45523','-75.73148','5922','STO Bus Stop','./img/bus-sto.png','24,24','0,0');
INSERT INTO poi VALUES ('45.46168','-75.73453','8221 / 5935','STO Bus Stop','./img/bus-sto.png','24,24','0,0');
