<?php

/**
 * @file
 * Contains \Drupal\eck\Controller\EckEntityListBuilder.
 */

namespace Drupal\eck\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for ECK entity.
 *
 * @ingroup eck
 */
class EckEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['description'] = array(
      '#markup' => $this->t('Entity settings'),
    );
    $build['table'] = parent::render();

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\eck\Entity\EckEntity */
    $row['id'] = $entity->id();
    $row['title'] = \Drupal::l(
      $entity->label(),
      Url::fromRoute(
        'entity.' . $this->entityTypeId . '.canonical',
        array(
          $this->entityTypeId => $entity->id(),
        )
      )
    );

    return array_merge($row, parent::buildRow($entity));
  }

}
