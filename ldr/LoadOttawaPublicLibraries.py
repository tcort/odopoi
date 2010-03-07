#!/usr/bin/python

import xml.etree.ElementTree as ET
for marker in ET.parse('opldata.xml').getroot().getchildren():
    sql = "INSERT INTO poi VALUES ('" + marker.attrib["lat"] + "','" + marker.attrib["lng"] + "','Ottawa Public Library - " + marker.attrib["location"].replace("'","''") + " ','','./img/library.png','20,21','0,0');"
    print sql.encode('utf-8')

