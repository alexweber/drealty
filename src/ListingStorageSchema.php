<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingStorageSchema.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the listing schema handler.
 */
class ListingStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    // Marking the respective fields as NOT NULL makes the indexes more
    // performant.
    $schema['drealty_listing_field_data']['fields']['changed']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['created']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['default_langcode']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['featured']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['status']['not null'] = TRUE;
    $schema['drealty_listing_field_data']['fields']['title']['not null'] = TRUE;
    $schema['drealty_listing_field_revision']['fields']['default_langcode']['not null'] = TRUE;

    // @todo Revisit index definitions in https://drupal.org/node/2015277.
    $schema['drealty_listing_revision']['indexes'] += array(
      'listing__langcode' => array('langcode'),
    );
    $schema['drealty_listing_revision']['foreign keys'] += array(
      'listing__revision_author' => array(
        'table' => 'users',
        'columns' => array('revision_uid' => 'uid'),
      ),
    );

    $schema['drealty_listing_field_data']['indexes'] += array(
      'listing__changed' => array('changed'),
      'listing__created' => array('created'),
      'listing__default_langcode' => array('default_langcode'),
      'listing__langcode' => array('langcode'),
      'listing__frontpage' => array('featured', 'status', 'created'),
      'listing__status_type' => array('status', 'type', 'nid'),
      'listing__title_type' => array('title', array('type', 4)),
    );

    $schema['drealty_listing_field_revision']['indexes'] += array(
      'listing__default_langcode' => array('default_langcode'),
      'listing__langcode' => array('langcode'),
    );

    return $schema;
  }

}
