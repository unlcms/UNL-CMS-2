<?php

/**
 * @file
 * Contains \Drupal\eck\Controller\EckEntityTypeListBuilder.
 */

namespace Drupal\eck\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Url;
use Drupal\eck\EckEntityTypeBundleInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of ECK entities.
 *
 * @ingroup eck
 */
class EckEntityTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * @var \Drupal\eck\EckEntityTypeBundleInfo $eckBundleInfo
   */
  protected $eckBundleInfo;

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory $queryFactory
   */
  protected $queryFactory;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('eck.entity_type.bundle.info'),
      $container->get('entity.query')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EckEntityTypeBundleInfo $bundle_info, QueryFactory $query_factory) {
    parent::__construct($entity_type, $storage);
    $this->eckBundleInfo = $bundle_info;
    $this->queryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Entity Type');
    $header['machine_name'] = $this->t('Machine Name');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['machine_name'] = $entity->id();

    if (!$this->eckBundleInfo->entityTypeHasBundles($entity->id())) {
      $row['operations']['data']['#links']['add_bundle'] = [
        'title' => $this->t('Add bundle'),
        'url' => new Url('eck.entity.' . $entity->id() . '_type.add'),
      ];
    }
    else {
      // Add link to list operation.
      $row['operations']['data']['#links']['add_content'] = [
        'title' => $this->t('Add content'),
        'url' => new Url('eck.entity.add_page', ['eck_entity_type' => $entity->id()]),
      ];
      // Directly link to the add entity page if there is only one bundle.
      if ($this->eckBundleInfo->entityTypeBundleCount($entity->id()) === 1) {
        $bundle_machine_names = $this->eckBundleInfo->getEntityTypeBundleMachineNames($entity->id());
        $arguments = [
          'eck_entity_type' => $entity->id(),
          'eck_entity_bundle' => reset($bundle_machine_names),
        ];
        $row['operations']['data']['#links']['add_content']['url'] = new Url('eck.entity.add', $arguments);
      }

      $contentExists = (bool) $this->queryFactory->get($entity->id())
        ->range(0, 1)
        ->execute();
      if ($contentExists) {
        // Add link to list operation.
        $row['operations']['data']['#links']['content_list'] = [
          'title' => $this->t('Content list'),
          'url' => new Url('eck.entity.' . $entity->id() . '.list'),
        ];
      }
    }

    $row['operations']['data']['#links']['bundle_list'] = [
      'title' => $this->t('Bundle list'),
      'url' => new Url('eck.entity.' . $entity->id() . '_type.list'),
    ];

    return array_merge_recursive($row, parent::buildRow($entity));
  }

}
