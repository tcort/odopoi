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
	/* TODO: add WHERE lat/lon between the parameters passed to the script. validate params */
	$sql = "SELECT * FROM poi;";
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
