<?php

require_once "./vendor/autoload.php";

use PHPMPDClient\MPD AS mpd;

// instantiate a connection

mpd::connect('', 'localhost');

Kint::dump(mpd::currentsong());

mpd::disconnect();