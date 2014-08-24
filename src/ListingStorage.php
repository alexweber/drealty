<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingStorage.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;

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

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema = parent::getSchema();

    // @TODO revisit this at a later time and add any approapriate extra indices
    // or metadata.

    // Marking the respective fields as NOT NULL makes the indexes more
    // performant.
    $schema['drealty_listing_field_data']['fields']['changed']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['created']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['default_langcode']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['featured']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['status']['not null'] = TRUE;
    $schema['drealty_listing_field_revision']['fields']['default_langcode']['not null'] = TRUE;

    // @todo Revisit index definitions in https://drupal.org/node/2015277.
    $schema['drealty_listing_revision']['indexes'] += array(
      'drealty_listing__langcode' => array('langcode'),
    );
    $schema['drealty_listing_revision']['foreign keys'] += array(
      'drealty_listing__revision_author' => array(
        'table' => 'users',
        'columns' => array('revision_uid' => 'uid'),
      ),
    );

    $schema['drealty_listing_field_data']['indexes'] += array(
      'drealty_listing__changed' => array('changed'),
      'drealty_listing__created' => array('created'),
      'drealty_listing__default_langcode' => array('default_langcode'),
      'drealty_listing__langcode' => array('langcode'),
      'drealty_listing__frontpage' => array('featured', 'status', 'created'),
      'drealty_listing__status_type' => array('status', 'type', 'id'),
    );

    $schema['drealty_listing_field_revision']['indexes'] += array(
      'drealty_listing__default_langcode' => array('default_langcode'),
      'drealty_listing__langcode' => array('langcode'),
    );

    return $schema;
  }

}
