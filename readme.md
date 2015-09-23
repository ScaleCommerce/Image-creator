ScaleCommerce Image Generator
=============================

This script generates Images for handling the OXID structure on a separate Server.
It is helpful for keeping the appserver and your development environment small.

Requirements
------------
You need the marmalade module for OXID eShop to make sure your pictures are not stored on your appservers.

In composer i set the requirements to PHP 5.6 although it should work with PHP 5.3+. But i will not support that.

Installation
------------
* Run ```composer install```

* change the root path in config.php to where your images are stored.

* Change your .htaccess file or nginx config so if an image is not found the script is called.


Known issues
------------
At the moment, there is no protection for generating hundreds and thousands of files.
You should take care of that by monitoring your server.

Image quality is not resprected yet.