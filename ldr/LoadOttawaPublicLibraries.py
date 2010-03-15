#!/usr/bin/python
# Open Data Ottawa Points of Interest
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

# Data Set URL: http://www.biblioottawalibrary.ca/common/opldata.xml

import xml.etree.ElementTree as ET
print "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';"
print "SET CHARACTER SET 'utf8';"
print "SET collation_connection = 'utf8_general_ci';"
print "DELETE FROM poi_category WHERE id = 'OPL';";
print "DELETE FROM poi WHERE poi_category_id = 'OPL';";
print "INSERT INTO poi_category VALUES ('OPL', './img/library.png', '20,21', '0,0');";
for marker in ET.parse('opldata.xml').getroot().getchildren():
    sql = "INSERT INTO poi VALUES ('OPL','" + marker.attrib["lat"] + "','" + marker.attrib["lng"] + "','0','Ottawa Public Library - " + marker.attrib["location"].replace("'","''") + " ','" + marker.attrib["phone"] + "<br/>" + marker.attrib["addr"] + "<br/>" + "Ottawa, ON " + marker.attrib["postalc"] + "<br/><br/><a href=\"http://www.biblioottawalibrary.ca/explore/branches/" + marker.attrib["url"] + "\">Branch Web Page</a>');"
    print sql.encode('utf-8')

