<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingAccessController
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\drealty\Entity\ListingType;

class ListingAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, $langcode = LanguageInterface::LANGCODE_DEFAULT, AccountInterface $account = NULL) {
    $account = $this->prepareUser($account);

    if (!$account->hasPermission('view drealty listings')) {
      return FALSE;
    }
    return parent::access($entity, $operation, $langcode, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function createAccess($entity_bundle = NULL, AccountInterface $account = NULL, array $context = array()) {
    $account = $this->prepareUser($account);

    if (!$account->hasPermission('view drealty listings')) {
      return FALSE;
    }

    return parent::createAccess($entity_bundle, $account, $context);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $listing, $operation, $langcode, AccountInterface $account) {
    /** @var \Drupal\drealty\ListingInterface $listing */
    /** @var \Drupal\drealty\ListingInterface  $translation */
    $translation = $listing->getTranslation($langcode);
    // Fetch information from the listing object if possible.
    $status = $translation->isPublished();
    $uid = $translation->getOwnerId();

    // Check if authors can view their own unpublished listings.
    if ($operation === 'view' && !$status && $account->hasPermission('view own unpublished drealty listings')) {

      if ($account->id() != 0 && $account->id() == $uid) {
        return TRUE;
      }
    }

    // The default behavior is to allow all users to view published listings,
    // so reflect that here.
    if ($operation === 'view') {
      return $status;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $configured_types = ListingType::loadMultiple();

    if (isset($configured_types[$entity_bundle])) {
      return $account->hasPermission("create {$entity_bundle} listing");
    }
  }

}
