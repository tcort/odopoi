<?php
function getPOI_header() {
	header("Content-type: text/plain; charset=UTF-8");
	print "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
}

function getPOI_body() {
	print "45.437153\t-75.709007\tSACRE COEUR / LAURIER\tSTO Bus Stop<br><br>(click again to close)\t./img/bus-sto.png\t24,24\t0,0\n";
}

function getPOI_footer() {
	/* nothing */
}

if (strcmp($_REQUEST["action"], "getPOI") == 0) {
	getPOI_header();
	getPOI_body();
	getPOI_footer();
} else {
	header("Content-type: text/plain; charset=UTF-8");
	print "Unsupported Action";
}

?>
