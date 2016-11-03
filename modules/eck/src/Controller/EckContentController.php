<?php

/**
 * @file
 * Contains \Drupal\eck\Controller\EckContentController.
 */

namespace Drupal\eck\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\eck\EckEntityTypeInterface;
use Drupal\eck\Entity\EckEntityBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a content controller for entities.
 *
 * @ingroup eck
 */
class EckContentController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The render service.
   *
   * @var RendererInterface
   */
  protected $renderer;

  /**
   * Constructs an EckContentController object.
   *
   * @param RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Displays add content link for available entity types.
   *
   * @param EckEntityTypeInterface $eck_entity_type
   *   The request parameters.
   *
   * @return array
   *   The output as a renderable array.
   */
  public function addPage(EckEntityTypeInterface $eck_entity_type) {
    $content = array();
    $entity_type_bundle = $eck_entity_type->id() . '_type';
    // Only use types the user has access to.
    foreach ($this->entityManager()->getStorage($entity_type_bundle)->loadMultiple() as $bundle) {
      if ($this->entityManager()->getAccessControlHandler($eck_entity_type->id())->createAccess($bundle->type)) {
        $content[$bundle->type] = $bundle;
      }
    }

    return array(
      '#theme' => 'eck_content_add_list',
      '#content' => $content,
      '#entity_type' => array(
        'id' => $eck_entity_type->id(),
        'label' => $this->entityManager()->getStorage($entity_type_bundle)->getEntityType()->getLabel(),
      ),
    );
  }

  /**
   * Provides the entity submission form.
   *
   * @param EckEntityTypeInterface $eck_entity_type
   *   The entity type.
   * @param string $eck_entity_bundle
   *   The entity type bundle.
   *
   * @return array
   *   The entity submission form.
   */
  public function add(EckEntityTypeInterface $eck_entity_type, $eck_entity_bundle) {
    $entity_type = $this->entityManager()->getStorage($eck_entity_type->id());
    // Create an entity.
    $entity = $entity_type->create(
      array(
        'type' => $eck_entity_bundle,
      )
    );
    // Get the form and return it.
    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Title callback for add page.
   *
   * @param EckEntityTypeInterface $eck_entity_type
   *   The entity type.
   *
   * @return string
   *   The title.
   */
  public function addPageTitle(EckEntityTypeInterface $eck_entity_type) {

    return t('Add %label content', array('%label' => $eck_entity_type->label()));
  }

  /**
   * Title callback for add page.
   *
   * @param string $eck_entity_bundle
   *   The entity type.
   *
   * @return string
   *   The title.
   */
  public function addContentPageTitle($eck_entity_bundle) {
    $eck_entity_bundle = EckEntityBundle::load($eck_entity_bundle);
    return t('Add %label content', array('%label' => $eck_entity_bundle->get('name')));
  }

}
