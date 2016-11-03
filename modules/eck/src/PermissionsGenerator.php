<?php

/**
 * @file
 * Contains \Drupal\eck\Permission\PermissionsGenerator.
 */

namespace Drupal\eck;

use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\eck\Entity\EckEntityType;

/**
 * Defines dynamic permissions.
 *
 * @ingroup eck
 */
class PermissionsGenerator {
  use StringTranslationTrait;
  use UrlGeneratorTrait;

  /**
   * Returns an array of entity type permissions.
   *
   * @return array
   *   The permissions.
   */
  public function entityPermissions() {
    $perms = array();
    // Generate entity permissions for all entity types.
    foreach (EckEntityType::loadMultiple() as $eck_type) {
      $perms = array_merge($perms, $this->buildPermissions($eck_type));
    }

    return $perms;
  }

  /**
   * Builds a standard list of entity permissions for a given type.
   *
   * @param EckEntityType $eck_type
   *   The entity type.
   *
   * @return array
   *   An array of permissions.
   */
  private function buildPermissions(EckEntityType $eck_type) {
    return array_merge($this->getCreatePermission($eck_type), $this->getEditPermissions($eck_type));
  }

  private function getCreatePermission(EckEntityType $entity_type) {
    return [
      "create {$entity_type->id()} entities" => [
        'title' => $this->t('Create new %type_name entities', ['%type_name' => $entity_type->label()]),
      ],
    ];
  }

  private function getEditPermissions(EckEntityType $entity_type) {
    $permissions = [];
    foreach (['edit', 'delete', 'view'] as $op) {
      $permissions = array_merge($permissions, $this->getEditPermission($entity_type, $op, 'any'));
      if ($entity_type->hasAuthorField()) {
        $permissions = array_merge($permissions, $this->getEditPermission($entity_type, $op, 'own'));
      }
    }
    return $permissions;
  }

  private function getEditPermission(EckEntityType $entity_type, $op, $ownership) {
    $ucfirst_op = ucfirst($op);
    return [
      "{$op} {$ownership} {$entity_type->id()} entities" => [
        'title' => $this->t("{$ucfirst_op} {$ownership} %type_name entities", ['%type_name' => $entity_type->label()]),
      ]
    ];
  }

}
