<?php

/**
 * @file
 * Contains \Drupal\eck\Routing\EckRoutes.
 */

namespace Drupal\eck\Routing;

use Drupal\eck\Entity\EckEntityType;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines dynamic routes.
 *
 * @ingroup eck
 */
class EckRoutes {

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $route_collection = new RouteCollection();
    // Get all entity types.
    $eck_types = EckEntityType::loadMultiple();

    foreach ($eck_types as $eck_type) {
      // Route for list.
      $route_list = new Route(
        'admin/structure/eck/entity/' . $eck_type->id,
        array(
          '_entity_list' => $eck_type->id,
          '_title' => '%type content',
          '_title_arguments' => ['%type' => ucfirst($eck_type->label())],
        ),
        array(
          '_permission' => "view own {$eck_type->id} entities+view any {$eck_type->id} entities+bypass eck entity access",
        )
      );
      // Add the route.
      $route_collection->add('eck.entity.' . $eck_type->id . '.list', $route_list);

      // Route for type list.
      $route_type_list = new Route(
        'admin/structure/eck/entity/' . $eck_type->id . '/types',
        array(
          '_controller' => '\Drupal\Core\Entity\Controller\EntityListController::listing',
          'entity_type' => $eck_type->id . '_type',
          '_title' => '%type bundles',
          '_title_arguments' => ['%type' => ucfirst($eck_type->label())],
        ),
        array(
          '_permission' => 'administer eck entity bundles',
        )
      );
      // Add the route.
      $route_collection->add('eck.entity.' . $eck_type->id . '_type.list', $route_type_list);

      // Route for type add.
      $route_type_add = new Route(
        'admin/structure/eck/entity/' . $eck_type->id . '/types/add',
        array(
          '_entity_form' => $eck_type->id . '_type.add',
          '_title' => 'Add %type bundle',
          '_title_arguments' => ['%type' => $eck_type->label()],
        ),
        array(
          '_permission' => 'administer eck entity bundles',
        )
      );
      // Add the route.
      $route_collection->add('eck.entity.' . $eck_type->id . '_type.add', $route_type_add);

      // Route for type edit.
      $route_type_edit = new Route(
        'admin/structure/eck/entity/' . $eck_type->id . '/types/manage/{' . $eck_type->id . '_type}',
        array(
          '_entity_form' => $eck_type->id . '_type.edit',
          '_title' => 'Edit %type bundle',
          '_title_arguments' => ['%type' => $eck_type->label()],
        ),
        array(
          '_permission' => 'administer eck entity bundles',
        )
      );
      // Add the route.
      $route_collection->add('entity.' . $eck_type->id . '_type.edit_form', $route_type_edit);

      // Route for type delete.
      $route_type_delete = new Route(
        'admin/structure/eck/entity/' . $eck_type->id . '/types/manage/{' . $eck_type->id . '_type}/delete',
        array(
          '_entity_form' => $eck_type->id . '_type.delete',
          '_title' => 'Delete %type bundle',
          '_title_arguments' => ['%type' => $eck_type->label()],
        ),
        array(
          '_permission' => 'administer eck entity bundles',
        )
      );
      // Add the route.
      $route_collection->add('entity.' . $eck_type->id . '_type.delete_form', $route_type_delete);
    }

    return $route_collection;
  }

}
