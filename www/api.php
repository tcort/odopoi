<?php
/*
 * The content of this file is licensed under the terms of the following license:
 *   Creative Commons Attribution-Share Alike 2.0 Generic License
 *
 * The license text is available at the following URL:
 *   http://creativecommons.org/licenses/by-sa/2.0/
 */

require_once('config.php');

function db_connect() {
	global $hostname, $database, $username, $password;

	@mysql_connect($hostname, $username, $password) or die("Unable to connect to database");
	@mysql_select_db($database) or die("Unable to select database");
}

function db_disconnect() {
	@mysql_close();
}

function getPOI() {
	header("Content-type: text/xml; charset=UTF-8");
	echo '<?xml version="1.0" ?>';

	$tllon = $_REQUEST["tllon"];
	$tllat = $_REQUEST["tllat"];
	$brlon = $_REQUEST["brlon"];
	$brlat = $_REQUEST["brlat"];

	/* TODO: adjust number of results and icons based on zoom factor */
	if (is_numeric($tllon) && is_numeric($tllat) && is_numeric($brlon) && is_numeric($brlat)) {
		$lon_min = ($tllon < $brlon) ? $tllon : $brlon;
		$lon_max = ($tllon > $brlon) ? $tllon : $brlon;
		$lat_min = ($tllat < $brlat) ? $tllat : $brlat;
		$lat_max = ($tllat > $brlat) ? $tllat : $brlat;

		$sql = "SELECT * FROM poi WHERE lat BETWEEN '" . $lat_min . "' AND '" . $lat_max . "' AND lon BETWEEN '" . $lon_min . "' AND '" . $lon_max . "' ORDER BY RAND() LIMIT 100;";
	} else {
		$sql = "SELECT * FROM poi ORDER BY RAND() LIMIT 100;";
	}

	$result = mysql_query($sql);

?>
<!DOCTYPE root [
<!ELEMENT cell ( #PCDATA ) >
<!ELEMENT data ( row+ ) >
<!ELEMENT root ( data ) >
<!ELEMENT row ( cell+ ) >
]>
<root>
	<data>
<?php
	while ($row = mysql_fetch_row($result)) {
?>
		<row>
			<cell><?php echo htmlspecialchars(htmlspecialchars($row[0])); ?></cell>
			<cell><?php echo htmlspecialchars(htmlspecialchars($row[1])); ?></cell>
			<cell><?php echo htmlspecialchars(htmlspecialchars($row[2])); ?></cell>
			<cell><?php echo htmlspecialchars(htmlspecialchars($row[3])); ?></cell>
			<cell><?php echo htmlspecialchars(htmlspecialchars($row[4])); ?></cell>
			<cell><?php echo htmlspecialchars(htmlspecialchars($row[5])); ?></cell>
			<cell><?php echo htmlspecialchars(htmlspecialchars($row[6])); ?></cell>
		</row>
<?php
	}

?>
	</data>
</root>
<?php
}


if (strcmp($_REQUEST["action"], "getPOI") == 0) {
	db_connect();
	getPOI();
	db_disconnect();
} else {
	header("Content-type: text/plain; charset=UTF-8");
	print "Unsupported Action";
}

?>
