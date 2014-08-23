<?php

/**
 * @file
 * Contains \Drupal\drealty\Access\ListingAddAccessCheck.
 */

namespace Drupal\drealty\Access;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\drealty\ListingTypeInterface;
use Drupal\drealty\Entity\ListingType;

/**
 * Determines access to for listing add pages.
 */
class ListingAddAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * Checks access to the listing add page for the listing type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\drealty\ListingTypeInterface $listing_type
   *   (optional) The listing type. If not specified, access is allowed if there
   *   exists at least one listing type for which the user may create a listing.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, ListingTypeInterface $listing_type = NULL) {
    $access_controller = $this->entityManager->getAccessController('drealty_listing');
    // If checking whether a listing of a particular type may be created.
    if ($listing_type) {
      return $access_controller->createAccess($listing_type->id(), $account) ? static::ALLOW : static::DENY;
    }
    // If checking whether a listing of any type may be created.
    foreach (ListingType::loadMultiple() as $listing_type) {
      if ($access_controller->createAccess($listing_type->id(), $account)) {
        return static::ALLOW;
      }
    }
    return static::DENY;
  }

}
