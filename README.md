# UNLcms 2
Drupal 8 installation at the University of Nebraska–Lincoln

# Installation

1. Install Composer (https://getcomposer.org/download/) and Drush version 8+ (http://docs.drush.org/en/master/install/)

  -  Production uses http://www.imagemagick.org/script/download.php. You can optionally skip installing this and switch to the core GD2 toolkit at _admin/config/media/image-toolkit_.

2. Clone this project and run from this project's root:
  ```
  cp .htaccess.drupal-default .htaccess
  cp sites/default/default.settings.php sites/default/settings.php
  mkdir sites/default/files
  sudo chown _www sites/default/files
  sudo chmod 777 sites/default/settings.php
  sudo chmod 777 sites/default/files
  ```
  
3. Edit sites/default/settings.php and add:
  ```
  $config_directories = array(
    CONFIG_SYNC_DIRECTORY => 'config/sync',
  );
  $settings['install_profile'] = 'config_installer';
  ```

4. If installing in a directory such as http://localhost/workspace/UNL-CMS-2 edit .htaccess and change
  ``` 
  # RewriteBase /
  ```
  to
  ``` 
  RewriteBase /workspace/UNL-CMS-2
  ```

5. Run
  ```
  composer install
  ```

6. Do standard Drupal install:
  * Navigate to /index.php in the browser
  * Choose "Configuration installer" for the "installation profile"
  * On "Configure site" set "Username" to "admin" and set "Email address" to a personal address so it doesn't conflict with your UNL email

7. Run:
  ```
  sudo chmod 755 sites/default/settings.php
  sudo chmod 755 sites/default/files/
  ```

8. Install the UNLedu Framework (https://github.com/unl/wdntemplates) separately and create a symlink to its 'wdn' directory.
  * For example, if you installed the wdntemplates project in /Library/WebServer/Documents then you would run this from your UNL-CMS-2 porject root:

  ```
  ln -s /Library/WebServer/Documents/wdntemplates/wdn wdn
  ```

9. Run:
  ```
  drush user-create jdoe2
  drush user-add-role "administrator" jdoe2
  ```
  The reason we create an admin user first, then create a second account is that the first user in a Drupal installation has special permisisons. We want to operate without that complexity. See https://www.drupal.org/node/540008

10. Enter the domain of your dev machine at admin/config/system/group_subdomain

11. That's it for installation. Instructions below are for additional site maintenance and updating tasks.

# Update core

  * Run `composer update drupal/core --with-dependencies`
  * Replace all files (such as index.php, update.php ) except for
    - .htaccess
    - composer.json
    - composer.lock
    - robots.txt
    - /sites
  * Manually update the above files with the latest changes
  * Run `drush updatedb`

# Adding a module

  * Example for adding the IMCE module: `composer require drupal/imce`
  * Enable the module in the UI and configure
  * Export the configuration and commit using "Managing configuration" below
  
# Updating a module

  * Run command to update a module `composer update "vendor/module:version"`. For example: `composer update "unlcms/unl_cas"` as this will only update the specific module and nothing else.
  * Make edits to the configuration in the Drupal UI if needed
  * Follow "Managing configuration" below
  * Once the module update is pushed to production, run update.php there

# Managing configuration

  * Requires Drush 8+: http://docs.drush.org/en/master/install/
  * `git pull` so you have the latest /config files
  * Make sure your local dev site is using the latest config by running this from the site root `drush config-import`
  * Make configuration changes on a local dev site and run `drush config-export`
  * Commit changes to /config dir
  * Do a pull request
  * On production run `drush config-import`

# Local devlopment

  * Disable Drupal 8 caching during development: https://www.drupal.org/node/2598914
  * Enable Twig debugging and disable caching: https://www.drupal.org/node/1903374

# Useful drush commands

  * Cache rebuild: `drush cr`
