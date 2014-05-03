<?php

/**
 * @file
 * Contains \Drupal\drealty\DrealtyListingAccessController.
 */

namespace Drupal\drealty;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityControllerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the access controller for the drealty listing entity type.
 */
class DrealtyListingAccessController extends EntityAccessController implements EntityControllerInterface {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, $langcode = Language::LANGCODE_DEFAULT, AccountInterface $account = NULL) {
    // @TODO update with corresponding permissions.
    if (user_access('bypass node access', $account)) {
      return TRUE;
    }
    if (!user_access('access content', $account)) {
      return FALSE;
    }
    return parent::access($entity, $operation, $langcode, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function createAccess($entity_bundle = NULL, AccountInterface $account = NULL, array $context = array()) {
    $account = $this->prepareUser($account);

    // @TODO update with corresponding permissions.
    if (user_access('bypass node access', $account)) {
      return TRUE;
    }
    if (!user_access('access content', $account)) {
      return FALSE;
    }

    return parent::createAccess($entity_bundle, $account, $context);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $drealty_listing, $operation, $langcode, AccountInterface $account) {
    /** @var \Drupal\drealty\DrealtyListingInterface $drealty_listing */
    /** @var \Drupal\drealty\DrealtyListingInterface $translation */
    $translation = $drealty_listing->getTranslation($langcode);
    // Fetch information from the drealty listing object if possible.
    $status = $translation->isPublished();
    $uid = $translation->getOwnerId();

    // Check if authors can view their own unpublished drealty listings.
    // @TODO update permission.
    if ($operation === 'view' && !$status && user_access('view own unpublished content', $account)) {

      if ($account->id() != 0 && $account->id() == $uid) {
        return TRUE;
      }
    }

    // Default behavior is to allow all users to view published drealty listings.
    if ($operation === 'view') {
      return $status;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // @TODO create this.
    $configured_types = node_permissions_get_configured_types();
    if (isset($configured_types[$entity_bundle])) {
      return user_access('create ' . $entity_bundle . ' content', $account);
    }
  }

}
