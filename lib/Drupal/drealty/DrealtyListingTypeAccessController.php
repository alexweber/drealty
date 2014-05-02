<?php

/**
 * @file
 * Contains \Drupal\drealty\DrealtyListingTypeAccessController.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the drealty listing type entity.
 *
 * @see \Drupal\drealty\Entity\DrealtyListingType.
 */
class DrealtyListingTypeAccessController extends EntityAccessController {

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
