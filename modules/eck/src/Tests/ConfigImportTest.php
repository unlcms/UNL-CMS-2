<?php

/**
 * @file
 * Contains \Drupal\eck\Tests\ConfigImportTest.
 */

namespace Drupal\eck\Tests;

use Drupal\Core\Url;
use Drupal\simpletest\WebTestBase;

/**
 * Class ConfigImportTest
 *
 * @group eck
 *
 * @codeCoverageIgnore because we don't have to test the tests
 */
class ConfigImportTest extends WebTestBase {

  protected $profile = 'standard';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('eck');

  protected function setUp() {
    parent::setUp();

    $user = $this->drupalCreateUser(array(
      'export configuration',
      'synchronize configuration',
      'administer eck entity types',
      'administer eck entities',
      'administer eck entity bundles',
      'bypass eck entity access',
    ));
    $this->drupalLogin($user);

    // Export the current configuration.
    $sync = $this->container->get('config.storage.sync');
    $config = \Drupal::configFactory()->loadMultiple(\Drupal::configFactory()->listAll());
    foreach ($config as $name => $conf) {
      $sync->write($name, $conf->getRawData());
    }
  }

  public function testImport() {
    $entity_type_config_name = 'eck.eck_entity_type.test_entity';
    $entity_bundle_config_name = 'eck.eck_type.test_entity.bundle';
    $storage = $this->container->get('config.storage');
    $sync = $this->container->get('config.storage.sync');

    // Verify the configuration to create does not exist yet.
    $this->assertFalse($storage->exists($entity_type_config_name), t('Entity config not found.'));
    $this->assertFalse($storage->exists($entity_bundle_config_name), t('Bundle config not found.'));

    // Create entity type config entity.
    $entity_type_config = [
      'id' => 'test_entity',
      'label' => 'Test entity',
      'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
      'dependencies' => [],
      'uuid' => '30df59bd-7b03-4cf7-bb35-d42fc49f0651',
      'status' => TRUE,
      'uid' => TRUE,
      'created' => TRUE,
      'changed' => TRUE,
      'title' => TRUE,
    ];
    $sync->write($entity_type_config_name, $entity_type_config);
    // Create entity bundle config entity.
    $entity_bundle_config = [
      'uuid' => '44bb277a-8358-4bc4-b439-577b0cb96820',
      'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
      'status' => TRUE,
      'dependencies' => [],
      'name' => 'Bundle',
      'type' => 'bundle',
      'description' => '',
    ];
    $sync->write($entity_bundle_config_name, $entity_bundle_config);

    // Import the configuration.
    $this->drupalPostForm('admin/config/development/configuration', array(), t('Import all'));

    // Verify the values appeared.
    $config = $this->config($entity_type_config_name);
    $this->assertEqual($config->getRawData(), $entity_type_config, t('Entity type config imported successfully.'));
    // Verify the values appeared.
    $config = $this->config($entity_bundle_config_name);
    $this->assertEqual($config->getRawData(), $entity_bundle_config, t('Entity bundle config imported successfully.'));

    // Verify the entity type has been added.
    $this->drupalGet(Url::fromRoute('eck.entity_type.list'));
    $this->assertRaw('Test entity');

    // Test if a new entity can be created.
    $edit = ['title[0][value]' => $this->randomMachineName()];
    $this->drupalPostForm('/admin/structure/eck/test_entity/add/bundle', $edit, t('Save'));
    $this->assertRaw($edit['title[0][value]']);
  }

}
