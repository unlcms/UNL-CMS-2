<?php

/**
 * @file
 * Contains \Drupal\eck\Tests\DynamicBaseFieldTest.
 */

namespace Drupal\eck\Tests;

use Drupal\Core\Url;

/**
 * Tests the functioning of eck's dynamic base fields.
 *
 * @group eck
 *
 * @codeCoverageIgnore because we don't have to test the tests
 */
class DynamicBaseFieldTest extends TestBase {

  public function testBaseFieldCRUD() {
    // Create the entity type.
    $type = $this->createEntityType(['uid', 'created', 'changed']);
    $bundle = $this->createEntityBundle($type['id']);

    // Make sure base fields are added.
    $route_args = [
      'eck_entity_type' => $type['id'],
      'eck_entity_bundle' => $bundle['type'],
    ];
    $this->drupalGet(Url::fromRoute('eck.entity.add', $route_args));
    $this->assertField('uid[0][target_id]');
    $this->assertField('created[0][value][date]');

    // Add a field to the entity type.
    $edit = ['title' => TRUE];
    $this->drupalPostForm(Url::fromRoute('entity.eck_entity_type.edit_form', ['eck_entity_type' => $type['id']]), $edit, t('Update @type', array('@type' => $type['label'])));
    $this->assertRaw(t('Entity type %label has been updated.', array('%label' => $type['label'])));

    // Make sure the field was added.
    $this->drupalGet(Url::fromRoute('eck.entity.add', $route_args));
    $this->assertField('title[0][value]');

    // Remove a field from the entity type.
    $edit = ['created' => FALSE];
    $this->drupalPostForm(Url::fromRoute('entity.eck_entity_type.edit_form', ['eck_entity_type' => $type['id']]), $edit, t('Update @type', array('@type' => $type['label'])));
    $this->assertRaw(t('Entity type %label has been updated.', array('%label' => $type['label'])));

    // Make sure the base field was removed.
    $this->drupalGet(Url::fromRoute('eck.entity.add', $route_args));
    $this->assertNoField('created[0][value][date]');

    // Add an entity to make sure there is data in the title field.
    $edit = ['title[0][value]' => $this->randomMachineName()];
    $this->drupalPostForm(Url::fromRoute('eck.entity.add', $route_args), $edit, t('Save'));
    $this->assertRaw($edit['title[0][value]']);

    // We should not be able to remove fields that have data.
    $this->drupalGet(Url::fromRoute('entity.eck_entity_type.edit_form', ['eck_entity_type' => $type['id']]));
    $this->assertFieldByXPath('//input[@type="checkbox"][@disabled]');
  }

  /**
   * Test if entities can be created when base fields are configured.
   */
  public function testEntityEntityCreation() {
    $type = $this->createEntityType();
    $bundle = $this->createEntityBundle($type['id']);

    // Test if a new entity can be created.
    $edit = ['title[0][value]' => $this->randomMachineName()];
    $route_args = [
      'eck_entity_type' => $type['id'],
      'eck_entity_bundle' => $bundle['type'],
    ];
    $this->drupalPostForm(Url::fromRoute('eck.entity.add', $route_args), $edit, t('Save'));
    $this->assertRaw($edit['title[0][value]']);
  }

}
