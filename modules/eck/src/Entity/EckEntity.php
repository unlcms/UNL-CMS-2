<?php

/**
 * @file
 * Contains \Drupal\eck\Entity\EckEntity.
 */

namespace Drupal\eck\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\eck\EckEntityInterface;
use Drupal\user\UserInterface;

/**
 * Defines the ECK entity.
 *
 * @ingroup eck
 */
class EckEntity extends ContentEntityBase implements EckEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array('uid' => \Drupal::currentUser()->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    if ($this->hasField('uid')) {
      return $this->get('uid');
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    if ($this->hasField('uid')) {
      return $this->getOwner()->first()->target_id;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    if ($this->hasField('uid')) {
      $this->set('uid', $uid);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->setOwnerId($account->id());

    return $this;
  }

  /**
   * @return \Drupal\Core\Field\FieldItemListInterface|string
   */
  public function label() {
    if ($this->hasField('title')) {
      $title = $this->get('title')->first();
      if (!empty($title)) {
        return $title->getString();
      }
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $config = \Drupal::config('eck.eck_entity_type.' . $entity_type->id());
    // The primary key field.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The ID of the eck entity.'))
      ->setReadOnly(TRUE)
      ->setSetting('unasigned', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Standard field, universal unique id.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the entity.'))
      ->setReadOnly(TRUE);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The entity type.'))
      ->setSetting('target_type', $entity_type->id() . '_type')
      ->setReadOnly(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language'))
      ->setDescription(t('The language code of the entity.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', ['type' => 'hidden',])
      ->setDisplayOptions('form', [
        'type' => 'language_select',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Title field for the entity.
    if ($config->get('title')) {
      $fields['title'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Title'))
        ->setDescription(t('The title of the entity.'))
        ->setRequired(TRUE)
        ->setTranslatable(TRUE)
        ->setDefaultValue('')
        ->setSetting('max_length', 255)
        ->setDisplayOptions('view', [
            'label' => 'hidden',
            'type' => 'string',
            'weight' => -5,
          ]
        )
        ->setDisplayOptions('form', [
            'type' => 'string_textfield',
            'weight' => -5,
          ]
        )
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);
    }

    // Author field for the entity.
    if ($config->get('uid')) {
      $fields['uid'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Authored by'))
        ->setDescription(t('The username of the entity author.'))
        ->setSetting('target_type', 'user')
        ->setSetting('handler', 'default')
        ->setTranslatable(TRUE)
        ->setDisplayOptions('view', [
          'label' => 'hidden',
          'type' => 'author',
          'weight' => 0,
        ])
        ->setDisplayOptions('form', [
          'type' => 'entity_reference_autocomplete',
          'weight' => 5,
          'settings' => [
            'match_operator' => 'CONTAINS',
            'size' => 60,
            'placeholder' => '',
          ],
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);
    }

    // Created field for the entity
    if ($config->get('created')) {
      $fields['created'] = BaseFieldDefinition::create('created')
        ->setLabel(t('Authored on'))
        ->setDescription(t('The time that the entity was created.'))
        ->setTranslatable(TRUE)
        ->setDisplayOptions('view', [
          'label' => 'hidden',
          'type' => 'timestamp',
          'weight' => 0,
        ])
        ->setDisplayOptions('form', [
          'type' => 'datetime_timestamp',
          'weight' => 10,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);
    }

    // Changed field for the entity
    if ($config->get('changed')) {
      $fields['changed'] = BaseFieldDefinition::create('changed')
        ->setLabel(t('Changed'))
        ->setDescription(t('The time that the entity was last edited.'))
        ->setTranslatable(TRUE)
        ->setDisplayConfigurable('view', TRUE);;
    }

    return $fields;
  }

}
