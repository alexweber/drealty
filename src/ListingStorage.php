<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingStorage.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;

/**
 * Defines the controller class for listings.
 *
 * This extends the base storage class, adding required special handling for
 * listing entities.
 */
class ListingStorage extends ContentEntityDatabaseStorage implements ListingStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function updateType($old_type, $new_type) {
    return $this->database->update('drealty_listing')
      ->fields(array('type' => $new_type))
      ->condition('type', $old_type)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema = parent::getSchema();

    // @TODO revisit this at a later time and add any approapriate extra indices
    // or metadata.

    return $schema;
  }

}
