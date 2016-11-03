# Installation

Run composer update drupal/core --with-dependencies


# Update core

Download latest version

Delete /core and /vendor

Replace all files except for
  - .htaccess
  - composer.json
  - composer.lock
  - robots.txt
  - /modules
  - /sites
  - /themes

Run composer update drupal/core --with-dependencies

Run update.php
