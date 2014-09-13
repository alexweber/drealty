<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingTypeInterface
 */

namespace Drupal\drealty;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;

/**
 * Provides an interface defining a listing type entity.
 */
interface ListingTypeInterface extends ConfigEntityInterface, ThirdPartySettingsInterface {

  /**
   * Determines whether the listing type is locked.
   *
   * @return string|false
   *   The module name that locks the type or FALSE.
   */
  public function isLocked();

  /**
   * Returns whether a new revision should be created by default.
   *
   * @return bool
   *   TRUE if a new revision should be created by default.
   */
  public function isNewRevision();

  /**
   * Set whether a new revision should be created by default.
   *
   * @param bool
   *   TRUE if a new revision should be created by default.
   */
  public function setNewRevision($new_revision);

}
