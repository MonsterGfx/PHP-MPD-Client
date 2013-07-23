<?php

require_once "./vendor/autoload.php";

// instantiate a connection

$mpd = new MPDWrapper\SimpleMPDWrapper('', 'localhost');

Kint::dump($mpd);

Kint::dump($mpd->status());