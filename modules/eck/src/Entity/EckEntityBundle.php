<?php

/**
 * @file
 * Contains \Drupal\eck\Entity\EckEntityBundle.
 */

namespace Drupal\eck\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\eck\EckEntityBundleInterface;

/**
 * Defines the ECK entity bundle configuration entity.
 *
 * @ConfigEntityType(
 *   id = "eck_entity_bundle",
 *   label = @Translation("Content type"),
 *   handlers = {
 *     "access" = "Drupal\eck\EckEntityAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\eck\Form\EntityBundle\EckEntityBundleForm",
 *       "edit" = "Drupal\eck\Form\EntityBundle\EckEntityBundleForm",
 *       "delete" = "Drupal\eck\Form\EntityBundle\EckEntityBundleDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\eck\Controller\EckEntityBundleListBuilder",
 *   },
 *   admin_permission = "administer eck entity bundles",
 *   entity_keys = {
 *     "id" = "bundle",
 *     "label" = "name"
 *   }
 * )
 *
 * @ingroup eck
 */
class EckEntityBundle extends ConfigEntityBundleBase implements EckEntityBundleInterface {

  /**
   * The machine name of this ECK entity bundle.
   *
   * @var string
   */
  public $type;

  /**
   * The human-readable name of the ECK entity type.
   *
   * @var string
   */
  public $name;

  /**
   * A brief description of this ECK bundle.
   *
   * @var string
   */
  public $description;

  /**
   * Help information shown to the user when creating an Entity of this bundle.
   *
   * @var string
   */
  public $help;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Clear the cache.
    $storage->resetCache(array($entities));
    // Clear all caches because the action links need to be regenerated.
    // @todo figure out how to do this without clearing ALL caches.
    drupal_flush_all_caches();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $this->addDependency('config', 'eck.eck_entity_type.' . $this->getEntityType()->getBundleOf());
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    // Update workflow options.
    // @todo Make it possible to get default values without an entity.
    //   https://www.drupal.org/node/2318187
    \Drupal::entityTypeManager()->getStorage(
      $this->getEntityType()->getBundleOf()
    )->create(['type' => $this->id()]);

    \Drupal::entityManager()->clearCachedFieldDefinitions();
    // Clear all caches because the action links need to be regenerated.
    // @todo figure out how to do this without clearing ALL caches.
    drupal_flush_all_caches();
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $locked = \Drupal::state()->get('eck_entity.type.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function loadMultiple(array $ids = NULL) {
    // Because we use a single class for multiple entity bundles we need to
    // parse all entity types and load the bundles.
    $entity_manager = \Drupal::entityManager();
    $bundles = array();
    foreach (EckEntityType::loadMultiple() as $entity) {
      $bundles = array_merge($bundles, $entity_manager->getStorage($entity->id() . '_type')
        ->loadMultiple($ids));
    }

    return $bundles;
  }

  /**
   * {@inheritdoc}
   */
  public static function load($id) {
    // Because we use a single class for multiple entity bundles we need to
    // parse all entity types and find the id.
    // @todo remove code duplication by using $this->loadMultiple().
    $entity_manager = \Drupal::entityManager();
    $loaded_entity = NULL;
    foreach (EckEntityType::loadMultiple() as $entity) {
      $load = $entity_manager->getStorage($entity->id() . '_type')->load($id);
      $loaded_entity = empty($load) ? $loaded_entity : $load;;
    }

    return $loaded_entity;
  }

}
