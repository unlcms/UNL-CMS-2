<?php

/**
 * @file
 * Contains \Drupal\eck\Tests\TestBase.
 */

namespace Drupal\eck\Tests;

use Drupal\Core\Url;
use Drupal\simpletest\WebTestBase;

/**
 * Base class for eck tests
 *
 * @codeCoverageIgnore because we don't have to test the tests
 */
abstract class TestBase extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['node', 'eck'];

  public function setUp() {
    parent::setUp();
    $user = $this->createUser([
      'administer eck entity types',
      'administer eck entities',
      'administer eck entity bundles',
      'bypass eck entity access',
    ]);
    $this->drupallogin($user);
  }

  /**
   * Returns an array of the configurable base fields.
   * @return array
   */
  protected function getConfigurableBaseFields() {
    return ['created', 'changed', 'uid', 'title'];
  }

  /**
   * Creates an entity type with a given label and/or enabled base fields.
   *
   * @param array $fields
   *   The fields that should be enabled for this entity type.
   * @param string $label
   *   The name of the entity type.
   *
   * @return array
   *   Information about the created entity type.
   *   - id:    the type's machine name
   *   - label: the type's label.
   */
  protected function createEntityType($fields = [], $label = '') {
    $label = empty($label) ? $this->randomMachineName() : $label;
    $fields = empty($fields) ? $this->getConfigurableBaseFields() : $fields;

    $edit = [
      'label' => $label,
      'id' => $id = strtolower($label),
    ];

    foreach ($fields as $field) {
      $edit[$field] = TRUE;
    }

    $this->drupalPostForm(Url::fromRoute('eck.entity_type.add'), $edit, t('Create entity type'));
    $this->assertRaw(t('Entity type %label has been added.', ['%label' => $label]));

    return ['id' => $id, 'label' => $label];
  }

  /**
   * Adds a bundle for a given entity type.
   *
   * @param $entity_type
   *  The entity type to add the bundle for.
   *
   * @return array
   *  The machine name and label of the new bundle.
   */
  protected function createEntityBundle($entity_type, $label = '') {
    if (empty($label)) {
      $label = $this->randomMachineName();
    }
    $bundle = strtolower($label);

    $edit = [
      'name' => $label,
      'type' => $bundle,
    ];
    $this->drupalPostForm("admin/structure/eck/entity/{$entity_type}/types/add", $edit, t('Save bundle'));
    $this->assertRaw(t('The entity bundle %name has been added.', ['%name' => $label]));

    return $edit;
  }

}
