<?php

namespace Drupal\eck;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeBundleInfo;

class EckEntityTypeBundleInfo extends EntityTypeBundleInfo{

  /**
   * {@inheritdoc}
   */
  public function getAllBundleInfo() {
    if (empty($this->bundleInfo)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      if ($cache = $this->cacheGet("eck_entity_bundle_info:$langcode")) {
        $this->bundleInfo = $cache->data;
      }
      else {
        $this->bundleInfo = $this->moduleHandler->invokeAll('entity_bundle_info');
        foreach ($this->entityTypeManager->getDefinitions() as $type => $entity_type) {
          if ($bundle_entity_type = $entity_type->getBundleEntityType()) {
            foreach ($this->entityTypeManager->getStorage($bundle_entity_type)->loadMultiple() as $entity) {
              $this->bundleInfo[$type][$entity->id()]['label'] = $entity->label();
            }
          }
        }
        $this->moduleHandler->alter('entity_bundle_info', $this->bundleInfo);
        $this->cacheSet("eck_entity_bundle_info:$langcode", $this->bundleInfo, Cache::PERMANENT, ['entity_types', 'entity_bundles']);
      }
    }

    return $this->bundleInfo;
  }

  /**
   * @param string $entity_type
   * @return bool
   */
  public function entityTypeHasBundles($entity_type) {
    return !empty($this->getBundleInfo($entity_type));
  }

  /**
   * @param string $entity_type
   * @return string[]
   */
  public function getEntityTypeBundleMachineNames($entity_type) {
    return array_keys($this->getBundleInfo($entity_type));
  }

  /**
   * @param string $entity_type
   * @return int
   */
  public function entityTypeBundleCount($entity_type) {
    return count($this->getBundleInfo($entity_type));
  }


}
