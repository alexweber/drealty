<?php

/**
 * @file
 * Contains \Drupal\drealty\Access\ListingRevisionAccessCheck.
 */

namespace Drupal\drealty\Access;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\drealty\ListingInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides an access checker for listing revisions.
 */
class ListingRevisionAccessCheck implements AccessInterface {

  /**
   * The listing storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $listingStorage;

  /**
   * The listing access controller.
   *
   * @var \Drupal\Core\Entity\EntityAccessControllerInterface
   */
  protected $listingAccess;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * A static cache of access checks.
   *
   * @var array
   */
  protected $access = array();

  /**
   * Constructs a new ListingRevisionAccessCheck.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityManagerInterface $entity_manager, Connection $connection) {
    $this->listingStorage = $entity_manager->getStorage('drealty_listing');
    $this->listingAccess = $entity_manager->getAccessController('drealty_listing');
    $this->connection = $connection;
  }

  /**
   * Checks routing access for the listing revision.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param int $listing_revision
   *   (optional) The listing revision ID. If not specified, but $listing is, access
   *   is checked for that object's revision.
   * @param \Drupal\drealty\ListingInterface $listing
   *   (optional) A listing object. Used for checking access to a listing's default
   *   revision when $listing_revision is unspecified. Ignored when $listing_revision
   *   is specified. If neither $listing_revision nor $listing are specified, then
   *   access is denied.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(Route $route, AccountInterface $account, $listing_revision = NULL, ListingInterface $listing = NULL) {
    if ($listing_revision) {
      $listing = $this->listingStorage->loadRevision($listing_revision);
    }
    $operation = $route->getRequirement('_access_drealty_listing_revision');
    return ($listing && $this->checkAccess($listing, $account, $operation)) ? static::ALLOW : static::DENY;
  }

  /**
   * Checks listing revision access.
   *
   * @param \Drupal\drealty\ListingInterface $listing
   *   The listing to check.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object representing the user for whom the operation is to be
   *   performed.
   * @param string $op
   *   (optional) The specific operation being checked. Defaults to 'view.'
   * @param string|null $langcode
   *   (optional) Language code for the variant of the listing. Different language
   *   variants might have different permissions associated. If NULL, the
   *   original langcode of the listing is used. Defaults to NULL.
   *
   * @return bool
   *   TRUE if the operation may be performed, FALSE otherwise.
   */
  public function checkAccess(ListingInterface $listing, AccountInterface $account, $op = 'view', $langcode = NULL) {
    $map = array(
      'view' => 'view all revisions',
      'update' => 'revert all revisions',
      'delete' => 'delete all revisions',
    );
    $bundle = $listing->bundle();
    $type_map = array(
      'view' => "view $bundle revisions",
      'update' => "revert $bundle revisions",
      'delete' => "delete $bundle revisions",
    );

    if (!$listing || !isset($map[$op]) || !isset($type_map[$op])) {
      // If there was no listing to check against, or the $op was not one of the
      // supported ones, we return access denied.
      return FALSE;
    }

    // If no language code was provided, default to the listing revision's langcode.
    if (empty($langcode)) {
      $langcode = $listing->language()->id;
    }

    // Statically cache access by revision ID, language code, user account ID,
    // and operation.
    $cid = $listing->getRevisionId() . ':' . $langcode . ':' . $account->id() . ':' . $op;

    if (!isset($this->access[$cid])) {
      // Perform basic permission checks first.
      if (!$account->hasPermission($map[$op]) && !$account->hasPermission($type_map[$op]) && !$account->hasPermission('administer drealty listings')) {
        $this->access[$cid] = FALSE;
        return FALSE;
      }

      // There should be at least two revisions. If the vid of the given listing
      // and the vid of the default revision differ, then we already have two
      // different revisions so there is no need for a separate database check.
      // Also, if you try to revert to or delete the default revision, that's
      // not good.
      if ($listing->isDefaultRevision() && ($this->connection->query('SELECT COUNT(*) FROM {drealty_listing_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $listing->id()))->fetchField() == 1 || $op == 'update' || $op == 'delete')) {
        $this->access[$cid] = FALSE;
      }
      elseif ($account->hasPermission('administer drealty listings')) {
        $this->access[$cid] = TRUE;
      }
      else {
        // First check the access to the default revision and finally, if the
        // listing passed in is not the default revision then access to that, too.
        $this->access[$cid] = $this->listingAccess->access($this->listingStorage->load($listing->id()), $op, $langcode, $account) && ($listing->isDefaultRevision() || $this->listingAccess->access($listing, $op, $langcode, $account));
      }
    }

    return $this->access[$cid];
  }

}
