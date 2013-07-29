<?php

require_once "./vendor/autoload.php";

use PHPMPDClient\MPD AS mpd;

// instantiate a connection

class test {
	public $val1 = 7;
	public $val8 = 8;
	public $val9 = 9;
	public $vala = 'a';
}
$sample = array(
	1, 2, 3,
	array(4, 5, 6),
	new test(),
	array( 'b', 'c', array('d','e','f'))
);

Kint::dump(mpd::condense($sample));