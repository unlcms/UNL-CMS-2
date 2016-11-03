<?php

/**
 * @file
 * Contains \Drupal\eck\Tests\EntityCreateUpdateTest.
 */

namespace Drupal\eck\Tests;

/**
 * Tests if eck entities are correctly created and updated
 *
 * @group eck
 *
 * @codeCoverageIgnore because we don't have to test the tests
 */
class EntityCreateUpdateTest extends TestBase {

  public function testIfEntityCreationDoesNotResultInMismatchedEntityDefinitions() {
    $this->drupalLogin($this->drupalCreateUser([],[], TRUE));
    $this->createEntityType([], 'TestType');

    $this->drupalGet('admin/reports/status');

    $this->assertNoRaw('Mismatched entity and/or field definitions');
  }

  public function testIfEntityUpdateDoesNotResultInMismatchedEntityDefinitions() {
    $this->testIfEntityCreationDoesNotResultInMismatchedEntityDefinitions();
    $path = 'admin/structure/eck/entity_type/manage/testtype';
    $edit = [
      'created' => FALSE
    ];

    $this->drupalPostForm($path, $edit, t('Update @type', ['@type' => 'TestType']));
    $this->drupalGet('admin/reports/status');

    $this->assertNoRaw('Mismatched entity and/or field definitions');
  }

}
