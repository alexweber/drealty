<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingStorage.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines the controller class for listings.
 *
 * This extends the base storage class, adding required special handling for
 * listing entities.
 */
class ListingStorage extends SqlContentEntityStorage implements ListingStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ListingInterface $listing) {
    return $this->database->query(
      'SELECT vid FROM {drealty_listing_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $listing->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {drealty_listing_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

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
  public function clearRevisionsLanguage($language) {
    return $this->database->update('drealty_listing_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->id)
      ->execute();
  }

}
