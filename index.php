<?php

require_once "./vendor/autoload.php";

// instantiate a connection

$mpd = new PHPMPDClient\MPD('', 'localhost');

Kint::dump($mpd);

Kint::dump($mpd->status());