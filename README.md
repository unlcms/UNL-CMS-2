# Installation
  
  * Run:
  ```
  ln -s /Library/WebServer/Documents/wdn wdn
  cp .htaccess.drupal-default .htaccess
  cp sites/default/default.settings.php sites/default/settings.php
  mkdir sites/default/files
  sudo chown _www sites/default/files
  sudo chmod 777 sites/default/settings.php
  sudo chmod 777 sites/default/files
  ```

  * Edit sites/default/settings.php and add:

  $config_directories = array(
    CONFIG_SYNC_DIRECTORY => 'config/sync',
  );

  * Edit .htaccess and change
  ``` 
  # RewriteBase /
  ```
  to
  ``` 
  RewriteBase /workspace/UNL-CMS-2
  ```

  * Run
  ```
  composer update drupal/core --with-dependencies
  ```

  * Do standard Drupal install
    - Navigate to /index.php
    - Choose "Configuration installer" for the "installation profile"
    - On "Configure site" set "Username" to "admin" and set "Email address" to a personal address so it doesn't conflict with your UNL email

  * Run:
```
    sudo chmod 755 sites/default/settings.php
    sudo chmod 755 sites/default/files/
```


# Update core

  * Download latest version from drupal.org

  * Delete /core and /vendor

  * Replace all files except for
    - .htaccess
    - composer.json
    - composer.lock
    - robots.txt
    - /modules
    - /sites
    - /themes

  * Manually update the above files
    - For example, update "drupal/core" in composer.json with the new version

  * Run composer update drupal/core --with-dependencies

  * Run update.php


# Managing configuration

  * Make sure your local dev site is using the latest config by running this from the site root $> drush config-import

  * Make configuration changes on a local dev site and run $> drush config-export

  * Commit changes to config dir

  * On production run $> drush config-import
