<?php

/**
 * @file
 * Contains \Drupal\drealty\Access\DrealtyListingRevisionAccessCheck.
 */

namespace Drupal\drealty\Access;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\drealty\DrealtyListingInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Provides an access checker for drealty listing revisions.
 */
class DrealtyListingRevisionAccessCheck implements AccessInterface {

  /**
   * The drealty listing storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $drealty_listingStorage;

  /**
   * The drealty listing access controller.
   *
   * @var \Drupal\Core\Entity\EntityAccessControllerInterface
   */
  protected $drealty_listingAccess;

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
   * Constructs a new DrealtyListingRevisionAccessCheck.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityManagerInterface $entity_manager, Connection $connection) {
    $this->drealty_listingStorage = $entity_manager->getStorage('drealty_listing');
    $this->drealty_listingAccess = $entity_manager->getAccessController('drealty_listing');
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request, AccountInterface $account) {
    // If the route has a {drealty_listing_revision} placeholder, load the
    // drealty listing for that revision. Otherwise, try to use a
    // {drealty_listing} placeholder.
    if ($request->attributes->has('drealty_listing_revision')) {
      $drealty_listing = $this->drealty_listingStorage->loadRevision($request->attributes->get('drealty_listing_revision'));
    }
    elseif ($request->attributes->has('drealty_listing')) {
      $drealty_listing = $request->attributes->get('drealty_listing');
    }
    else {
      return static::DENY;
    }
    return $this->checkAccess($drealty_listing, $account, $route->getRequirement('_access_drealty_listing_revision')) ? static::ALLOW : static::DENY;
  }

  /**
   * Checks drealty listing revision access.
   *
   * @param \Drupal\drealty\DrealtyListingInterface $drealty_listing
   *   The drealty listing to check.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object representing the user for whom the operation is to be
   *   performed.
   * @param string $op
   *   (optional) The specific operation being checked. Defaults to 'view.'
   * @param string|null $langcode
   *   (optional) Language code for the variant of the drealty listing.
   *   Different language variants might have different permissions associated.
   *   If NULL, the original langcode of the drealty listing is used. Defaults
   *   to NULL.
   *
   * @return bool
   *   TRUE if the operation may be performed, FALSE otherwise.
   */
  public function checkAccess(DrealtyListingInterface $drealty_listing, AccountInterface $account, $op = 'view', $langcode = NULL) {
    $map = array(
      'view' => 'view all revisions',
      'update' => 'revert all revisions',
      'delete' => 'delete all revisions',
    );
    $bundle = $drealty_listing->bundle();
    $type_map = array(
      'view' => "view $bundle revisions",
      'update' => "revert $bundle revisions",
      'delete' => "delete $bundle revisions",
    );

    if (!$drealty_listing || !isset($map[$op]) || !isset($type_map[$op])) {
      // If there was no drealty listing to check against, or the $op was not
      // one of the supported ones, we return access denied.
      return FALSE;
    }

    // If no language code was provided, default to the drealty listing
    // revision's langcode.
    if (empty($langcode)) {
      $langcode = $drealty_listing->language()->id;
    }

    // Statically cache access by revision ID, language code, user account ID,
    // and operation.
    $cid = $drealty_listing->getRevisionId() . ':' . $langcode . ':' . $account->id() . ':' . $op;

    if (!isset($this->access[$cid])) {
      // Perform basic permission checks first.
      if (!$account->hasPermission($map[$op]) && !$account->hasPermission($type_map[$op]) && !$account->hasPermission('administer drealty listings')) {
        $this->access[$cid] = FALSE;
        return FALSE;
      }

      // There should be at least two revisions. If the vid of the given drealty
      // listing and the vid of the default revision differ, then we already
      // have two different revisions so there is no need for a separate
      // database check. Also, if you try to revert to or delete the default
      // revision, that's not good.
      if ($drealty_listing->isDefaultRevision() && ($this->connection->query('SELECT COUNT(*) FROM {drealty_listing_field_revision} WHERE nid = :nid AND default_langcode = 1', array(':nid' => $drealty_listing->id()))->fetchField() == 1 || $op == 'update' || $op == 'delete')) {
        $this->access[$cid] = FALSE;
      }
      elseif ($account->hasPermission('administer drealty listings')) {
        $this->access[$cid] = TRUE;
      }
      else {
        // First check the access to the default revision and finally, if the
        // drealty listing passed in is not the default revision then access to
        // that, too.
        $this->access[$cid] = $this->drealty_listingAccess->access($this->drealty_listingStorage->load($drealty_listing->id()), $op, $langcode, $account) && ($drealty_listing->isDefaultRevision() || $this->drealty_listingAccess->access($drealty_listing, $op, $langcode, $account));
      }
    }

    return $this->access[$cid];
  }

}
