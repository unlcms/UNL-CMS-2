<?php
/**
 * @file
 * Contains Drupal\Tests\eck\Unit\PermissionsGeneratorTest.
 */

namespace Drupal\Tests\eck\Unit;

use Drupal\eck\PermissionsGenerator;

/**
 * Tests the form element implementation.
 *
 * @group eck
 */
class PermissionsGeneratorTest extends UnitTestBase{

  /** @var \Drupal\eck\PermissionsGenerator $sut */
  private $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->sut = $this->getNewSUT();
  }

  /**
   * @return \Drupal\eck\PermissionsGenerator
   */
  private function getNewSUT() {
    $permissions = new PermissionsGenerator();
    $permissions->setStringTranslation($this->getStringTranslationStub());
    $permissions->setUrlGenerator($this->getMockForAbstractClass('\Drupal\Core\Routing\UrlGeneratorInterface'));

    return $permissions;
  }

  /**
   * @test
   */
  public function generatesNoPermissionsIfNoEntityTypesAreDefined() {
    $this->assertArrayEquals([], $this->sut->entityPermissions());
  }

  /**
   * @test
   */
  public function generatesCorrectPermissionsWithSingleEntity() {
    $this->addEntityToStorage($this->createEckEntityType('entity_type'));
    $permissions = $this->sut->entityPermissions();
    $this->assertCreatePermission($permissions);
    $this->assertGlobalPermissions($permissions);
    $this->assertOwnerPermissions($permissions);
  }

  /**
   * @test
   */
  public function generatesCorrectPermissionsWithSingleEntityWithEnabledAuthorField() {
    $this->addEntityToStorage($this->createEckEntityType('entity_type', ['uid' => TRUE]));
    $permissions = $this->sut->entityPermissions();
    $this->assertCreatePermission($permissions);
    $this->assertGlobalPermissions($permissions);
    $this->assertOwnerPermissions($permissions);
  }

  /**
   * @test
   */
  public function generatesCorrectPermissionsWithMultipleEntities() {
    $this->addEntityToStorage($this->createEckEntityType('entity_type'));
    $this->addEntityToStorage($this->createEckEntityType('another_type', ['uid' => TRUE]));
    $permissions = $this->sut->entityPermissions();
    $this->assertCreatePermission($permissions);
    $this->assertGlobalPermissions($permissions);
    $this->assertOwnerPermissions($permissions);
  }

  protected function assertCreatePermission($permissions) {
    foreach ($this->entities as $id => $entity) {
      $this->assertArrayHasKey("create {$id} entities", $permissions);
    }
  }

  protected function assertGlobalPermissions($permissions) {
    foreach ($this->entities as $id => $entity) {
      $this->assertArrayHasKey("edit any {$id} entities", $permissions);
      $this->assertArrayHasKey("delete any {$id} entities", $permissions);
      $this->assertArrayHasKey("view any {$id} entities", $permissions);
    }
  }

  protected function assertOwnerPermissions($permissions) {
    foreach ($this->entities as $id => $entity) {
      /** @var \Drupal\eck\Entity\EckEntityType $entity */
      if ($entity->hasAuthorField()) {
        $this->assertArrayHasKey("edit own {$id} entities", $permissions);
        $this->assertArrayHasKey("delete own {$id} entities", $permissions);
        $this->assertArrayHasKey("view own {$id} entities", $permissions);
      }
      else {
        $this->assertArrayNotHasKey("edit own {$id} entities", $permissions);
        $this->assertArrayNotHasKey("delete own {$id} entities", $permissions);
        $this->assertArrayNotHasKey("view own {$id} entities", $permissions);
      }
    }
  }

}
