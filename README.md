# Installation

  * Navigate to index.php and choose "Configuration installer" for the "installation profile"

  * Edit sites/default/settings.php and add:

  $config_directories = array(
    CONFIG_SYNC_DIRECTORY => 'config/sync',
  );

  * Run $> composer update drupal/core --with-dependencies

  * Run &> drush config-import


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

Manually update the above files
  - For example, update "drupal/core" in composer.json with the new version

Run composer update drupal/core --with-dependencies

Run update.php
