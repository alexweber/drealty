<?php

/**
 * @file
 * Contains \Drupal\drealty\Plugin\Type\selection\ListingSelection.
 */

namespace Drupal\drealty\Plugin\entity_reference\selection;

use Drupal\entity_reference\Plugin\entity_reference\selection\SelectionBase;

/**
 * Provides specific access control for the drealty listing entity type.
 *
 * @EntityReferenceSelection(
 *   id = "drealty_listing",
 *   label = @Translation("Drealty listing selection"),
 *   entity_types = {"drealty_listing"},
 *   group = "drealty",
 *   weight = 1
 * )
 */
class ListingSelection extends SelectionBase {

  /**
   * {@inheritdoc}
   */
  public function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);

    if (!\Drupal::currentUser()->hasPermission('administer drealty listings')) {
      $query->condition('status', NODE_PUBLISHED);
    }
    return $query;
  }
}
