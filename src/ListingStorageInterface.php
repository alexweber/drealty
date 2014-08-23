<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingStorageControllerInterface.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines a common interface for drealty listing entity controller classes.
 */
interface ListingStorageInterface extends EntityStorageInterface {

  /**
   * Updates all listings of one type to be of another type.
   *
   * @param string $old_type
   *   The current listing type of the listings.
   * @param string $new_type
   *   The new listing type of the listings.
   *
   * @return int
   *   The number of listings whose listing type field was modified.
   */
  public function updateType($old_type, $new_type);

}
