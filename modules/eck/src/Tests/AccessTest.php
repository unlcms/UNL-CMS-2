<?php

/**
 * @file
 * Contains \Drupal\eck\Tests\AccessTest
 */

namespace Drupal\eck\Tests;

use Drupal\Core\Url;

/**
 * Tests eck's access control.
 *
 * @group eck
 *
 * @codeCoverageIgnore because we don't have to test the tests
 */
class AccessTest extends TestBase {

  /**
   * @var
   */
  protected $entityType;

  /**
   * @var
   */
  protected $bundle;

  public function setUp() {
    parent::setUp();
    $this->entityType = $this->createEntityType();
    $this->bundle = $this->createEntityBundle($this->entityType['id']);
    // Start testing with a logged out user.
    $this->drupalLogout();
  }

  /**
   * Tests if the access to the default routes is properly checked.
   */
  public function testDefaultRoutes() {
    $routes = [
      'administer eck entity types' => [
        'eck.entity_type.list',
        'eck.entity_type.add',
        'entity.eck_entity_type.edit_form',
        'entity.eck_entity_type.delete_form',
      ],
      "create {$this->entityType['id']} entities" => [
        'eck.entity.add_page',
        'eck.entity.add',
      ],
    ];
    $route_args = [
      'eck_entity_type' => $this->entityType['id'],
      'eck_entity_bundle' => $this->bundle['type'],
    ];
    foreach ($routes as $route_names) {
      foreach ($route_names as $route) {
        $this->drupalGet(Url::fromRoute($route, $route_args));
        $this->assertResponse(403, t('Anonymous users cannot access %route', ['%route' => $route]));
      }
    }

    \Drupal::entityTypeManager()->clearCachedDefinitions();
    foreach ($routes as $permission => $route_names) {
      $this->drupalLogin($this->drupalCreateUser([$permission]));
      foreach ($route_names as $route) {
        $this->drupalGet(Url::fromRoute($route, $route_args));
        $this->assertResponse(200, t('Users with the %perm permission can access %route', ['%route' => $route, '%perm' => $permission]));
      }
    }
  }

  /**
   * Tests if the access to dynamic routes is properly checked.
   */
  public function testDynamicRoutes() {
    $routes = [
      "view own {$this->entityType['id']} entities" => [
        "eck.entity.{$this->entityType['id']}.list",
      ],
      "view any {$this->entityType['id']} entities" => [
        "eck.entity.{$this->entityType['id']}.list",
      ],
      'bypass eck entity access' => [
        "eck.entity.{$this->entityType['id']}.list",
      ],
      'administer eck entity bundles' => [
        "eck.entity.{$this->entityType['id']}_type.list",
        "eck.entity.{$this->entityType['id']}_type.add",
        "entity.{$this->entityType['id']}_type.edit_form",
        "entity.{$this->entityType['id']}_type.delete_form",
      ],
    ];
    $route_args = [
      "{$this->entityType['id']}_type" => $this->bundle['type'],
    ];

    foreach ($routes as $route_names) {
      foreach ($route_names as $route) {
        $this->drupalGet(Url::fromRoute($route, $route_args));
        $this->assertResponse(403, t('Anonymous users cannot access %route', ['%route' => $route]));
      }
    }

    \Drupal::entityTypeManager()->clearCachedDefinitions();
    foreach ($routes as $permission => $route_names) {
      $this->drupalLogin($this->drupalCreateUser([$permission]));
      foreach ($route_names as $route) {
        $this->drupalGet(Url::fromRoute($route, $route_args));
        $this->assertResponse(200, t('Users with the %perm permission can access %route', ['%route' => $route, '%perm' => $permission]));
      }
    }
  }

  /**
   * Tests if access handling for created entities is handled correctly.
   */
  public function testEntityAccess() {
    $own_permissions = $any_permissions = ["create {$this->entityType['id']} entities"];
    foreach (['view', 'edit', 'delete'] as $op) {
      $own_permissions[] = "{$op} own {$this->entityType['id']} entities";
      $any_permissions[] = "{$op} any {$this->entityType['id']} entities";
    }
    $ownEntityUser = $this->drupalCreateUser($own_permissions);
    $anyEntityUser = $this->drupalCreateUser($any_permissions);

    $this->drupalLogin($anyEntityUser);
    $edit['title[0][value]'] = $this->randomMachineName();
    $route_args = [
      'eck_entity_type' => $this->entityType['id'],
      'eck_entity_bundle' =>  $this->bundle['type'],
    ];
    $this->drupalPostForm(Url::fromRoute("eck.entity.add", $route_args), $edit, t('Save'));

    $this->drupalLogin($ownEntityUser);
    $edit['title[0][value]'] = $this->randomMachineName();
    $route_args = [
      'eck_entity_type' => $this->entityType['id'],
      'eck_entity_bundle' =>  $this->bundle['type'],
    ];
    $this->drupalPostForm(Url::fromRoute("eck.entity.add", $route_args), $edit, t('Save'));

    // Get the entity that was created by the 'any' user.
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/1");
    $this->assertResponse(403, 'The user cannot see content which is not his own.');
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/1/edit");
    $this->assertResponse(403, 'The user cannot edit content which is not his own.');
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/1/delete");
    $this->assertResponse(403, 'The user cannot delete content which is not his own.');
    // Get the entity that was created by the 'own' user.
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/2");
    $this->assertResponse(200, 'The user can see content which is his own.');
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/2/edit");
    $this->assertResponse(200, 'The user can edit content which is his own.');
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/2/delete");
    $this->assertResponse(200, 'The user can delete content which not his own.');

    $this->drupalLogin($anyEntityUser);
    // Get the entity that was created by the 'any' user.
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/1");
    $this->assertResponse(200, 'The user can see content which is his own.');
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/1/edit");
    $this->assertResponse(200, 'The user can edit content which is his own.');
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/1/delete");
    $this->assertResponse(200, 'The user can delete content which is his own.');
    // Get the entity that was created by the 'own' user.
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/2");
    $this->assertResponse(200, 'The user can see content which is not his own.');
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/2/edit");
    $this->assertResponse(200, 'The user can edit content which is not his own.');
    $this->drupalGet("admin/structure/eck/entity/{$this->entityType['id']}/2/delete");
    $this->assertResponse(200, 'The user can delete content which is not his own.');
  }

}
