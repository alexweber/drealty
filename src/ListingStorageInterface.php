<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingStorageControllerInterface.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines a common interface for drealty listing entity controller classes.
 */
interface ListingStorageInterface extends EntityStorageInterface {

  /**
   * Returns a list of revision IDs for a specific listing.
   *
   * @param \Drupal\drealty\ListingInterface
   *   The listing entity.
   *
   * @return int[]
   *   Listing revision IDs (in ascending order).
   */
  public function revisionIds(ListingInterface $listing);

  /**
   * Returns a list of revision IDs having a given user as listing author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Listing revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

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

  /**
   * Unsets the language for all listings with the given language.
   *
   * @param $language
   *  The language object.
   */
  public function clearRevisionsLanguage($language);

}
