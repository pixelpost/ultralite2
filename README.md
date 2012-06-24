Ultralite2
==========

The second generation core framework for Pixelpost.

*Note: This is an alpha release not intended for production use.*

Requirements
------------

* Apache 2 or higher
	* mod_rewrite

* PHP 5.3.0 or higher
	* MBString
	* SQLite3
	* GD2

Setup & Configuration
---------------------

## Classic method

1. Copy the `app` folder in your web directory.
2. Launch the install script `http://www.example.com/app/setup/`.
3. Enjoy!

## Phar method

1. Copy the `pixelpost.phar.php` file in your web directory.
2. Launch the install script `http://www.example.com/pixelpost.phar.php`.
3. Enjoy!

Create a Phar archive
---------------------

Run the command line `php -d phar.readonly=0 phar/build.php`.

This will create a `pixelpost.phar.php` file in your current directory.

> You can rename that file but keep in mind that the `.phar` extension is mandatory. `.php` extension can be omitted but it permit to be executed by web server which don't understand `.phar` extension.

License & Copyright
-------------------

(c) 2011-2012 Alban Leroux, Licensed under the [Creative Commons BY-SA 3.0](http://creativecommons.org/licenses/by-sa/3.0/).
