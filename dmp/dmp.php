<?php
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

mb_language('uni');
mb_internal_encoding('UTF-8');

require_once('../www/config.php');

@mysql_connect($hostname, $username, $password) or die("Unable to connect to database");
@mysql_select_db($database) or die("Unable to select database");

@mysql_set_charset('utf8');

@mysql_query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
@mysql_query("SET CHARACTER SET 'utf8'");
@mysql_query("SET collation_connection = 'utf8_general_ci'");

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<gpx version="1.1" creator="odopoi" xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">
  <metadata>
    <name>OpenDataMap.ca Data Dump</name>
    <desc>All of the points of interest from the OpenDataMap.ca database</desc>
    <author>
      <name>Thomas Cort</name>
      <email id="linuxgeek" domain="gmail.com"/>
      <link href="http://www.tomcort.com/">
        <text>TomCort.com</text>
      </link>
    </author>
    <link href="http://opendatamap.ca/">
      <text>OpenDataMap.ca</text>
    </link>
    <time>2010-03-16T06:56:33Z</time>
  </metadata>
<?php
$sql = "SELECT lat, lon, title, description FROM poi JOIN poi_category ON poi.poi_category_id = poi_category.id;";
$result = mysql_query($sql);

while ($row = mysql_fetch_row($result)) {
?>
  <wpt lat="<?php echo htmlspecialchars($row[0]); ?>" lon="<?php echo htmlspecialchars($row[1]); ?>">
    <name><?php echo htmlspecialchars($row[2]); ?></name>
    <desc><?php echo htmlspecialchars($row[3]); ?></desc>
  </wpt>
<?php
}


@mysql_close();

?>
</gpx>
