SimpleMPDWrapper
================

#Introduction

I needed a class to interface with MPD for a project. I looked around and found
a simple one at https://packagist.org/packages/mutantlabs/simplempd. However,
that project was a little more complicated that I needed - it packages an
interface class (`SimpleMPDWrapper`) with a REST service provider. I just needed
`SimpleMPDWrapper`, so I forked it & I'm extending it to provide more
functionality.


#Installing it in your project

Visit the project page at https://packagist.org/packages/monstergfx/php-mpd-client
and install the package via Composer.


Requirements
------------

 - PHP 5.3 and above.


#SimpleMPDWrapper Class usage

See http://www.musicpd.org/doc/protocol/ for the MPD protocol documentation.

You can submit an arbitrary command via the `MPD::send` method.

There are also wrapper methods for several of the commands:

- `MPD::add()`
- `MPD::status()`
- `MPD::clear()`
- `MPD::currentSong()`
- `MPD::move()`


License
-------

 - Licensed under the MIT License. See the LICENSE file for more details.
