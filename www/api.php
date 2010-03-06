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

function getPOI_header() {
	header("Content-type: text/plain; charset=UTF-8");
	print "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
}

function getPOI_body() {
	$tllon = $_REQUEST["tllon"];
	$tllat = $_REQUEST["tllat"];
	$brlon = $_REQUEST["brlon"];
	$brlat = $_REQUEST["brlat"];

	if (is_numeric($tllon) && is_numeric($tllat) && is_numeric($brlon) && is_numeric($brlat)) {
		$lon_min = ($tllon < $brlon) ? $tllon : $brlon;
		$lon_max = ($tllon > $brlon) ? $tllon : $brlon;
		$lat_min = ($tllat < $brlat) ? $tllat : $brlat;
		$lat_max = ($tllat > $brlat) ? $tllat : $brlat;

		$sql = "SELECT * FROM poi WHERE lat BETWEEN ('" . $lat_min . "','" . $lat_max . "') AND lon BETWEEN ('" . $lon_min . "','" . $lon_max . "') RAND() LIMIT 100;";
	} else {
		$sql = "SELECT * FROM poi ORDER BY RAND() LIMIT 100;";
	}

	$result = mysql_query($sql);
	$num = mysql_numrows($result);

	for ($i = 0; $i < $num; $i++) {
		print mysql_result($result,$i,"lat");
		print "\t";
		print mysql_result($result,$i,"lon");
		print "\t";
		print mysql_result($result,$i,"title");
		print "\t";
		print mysql_result($result,$i,"description");
		print "\t";
		print mysql_result($result,$i,"icon");
		print "\t";
		print mysql_result($result,$i,"iconSize");
		print "\t";
		print mysql_result($result,$i,"iconOffset");
		print "\n";
	}

}

function getPOI_footer() {
	/* nothing */
}

if (strcmp($_REQUEST["action"], "getPOI") == 0) {
	db_connect();
	getPOI_header();
	getPOI_body();
	getPOI_footer();
	db_disconnect();
} else {
	header("Content-type: text/plain; charset=UTF-8");
	print "Unsupported Action";
}

?>
