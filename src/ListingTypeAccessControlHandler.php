<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingTypeAccessController
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the listing type entity.
 *
 * @see \Drupal\drealty\Entity\ListingType.
 */
class ListingTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation == 'delete' && $entity->isLocked()) {
      return FALSE;
    }
    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
