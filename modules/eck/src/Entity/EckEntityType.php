<?php

/**
 * @file
 * Contains Drupal\eck\Entity\EckEntityType.
 */

namespace Drupal\eck\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\eck\EckEntityTypeInterface;

/**
 * Defines the ECK Entity Type config entities.
 *
 * @ConfigEntityType(
 *   id = "eck_entity_type",
 *   label = @Translation("ECK Entity Type"),
 *   handlers = {
 *     "list_builder" = "Drupal\eck\Controller\EckEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\eck\Form\EntityType\EckEntityTypeAddForm",
 *       "edit" = "Drupal\eck\Form\EntityType\EckEntityTypeEditForm",
 *       "delete" = "Drupal\eck\Form\EntityType\EckEntityTypeDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer eck entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/eck/entity_type/manage/{eck_entity_type}",
 *     "delete-form" = "/admin/structure/eck/entity_type/manage/{eck_entity_type}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "created",
 *     "changed",
 *     "uid",
 *     "title"
 *   }
 * )
 *
 * @ingroup eck
 */
class EckEntityType extends ConfigEntityBase implements EckEntityTypeInterface {

  use LinkGeneratorTrait;

  /**
   * If this entity type has an "Author" base field.
   *
   * @var boolean
   */
  protected $uid;

  /**
   * If this entity type has a "Title" base field.
   *
   * @var boolean
   */
  protected $title;

  /**
   * If this entity type has a "Created" base field.
   *
   * @var boolean
   */
  protected $created;

  /**
   * If this entity type has a "Changed" base field.
   *
   * @var boolean
   */
  protected $changed;

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Clear the router cache to prevent RouteNotFoundException while creating
    // the edit link.
    \Drupal::service('router.builder')->rebuild();
    $edit_link = $this->l(t('Edit'), $this->urlInfo());

    if ($update) {$this->logger($this->id())->notice(
        'Entity type %label has been updated.',
        ['%label' => $this->label(), 'link' => $edit_link]
      );
    }
    else {
      // Clear caches first.
      $this->entityTypeManager()->clearCachedDefinitions();

      // Notify storage to create the database schema.
      $entity_type = $this->entityTypeManager()->getDefinition($this->id());
      $this->entityManager()->onEntityTypeCreate($entity_type);

      $this->logger($this->id())->notice(
        'Entity type %label has been added.',
        ['%label' => $this->label(), 'link' => $edit_link]
      );
    }

    \Drupal::entityDefinitionUpdateManager()->applyUpdates();
  }

  /**
   * Gets the logger for a specific channel.
   *
   * @param string $channel
   *   The name of the channel.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger for this channel.
   */
  protected function logger($channel) {
    return \Drupal::getContainer()->get('logger.factory')->get($channel);
  }

  public function hasAuthorField() {
    return isset($this->uid) && $this->uid;
  }

  public function hasChangedField() {
    return isset($this->changed) && $this->changed;
  }

  public function hasCreatedField() {
    return isset($this->created) && $this->created;
  }

  public function hasTitleField() {
    return isset($this->title) && $this->title;
  }

}
