<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingTypeInterface
 */

namespace Drupal\drealty;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface ListingTypeInterface extends ConfigEntityInterface {

  /**
   * Returns the configured property type settings of a given module, if any.
   *
   * @param string $module
   *   The name of the module whose settings to return.
   *
   * @return array
   *   An associative array containing the module's settings for the property type.
   *   Note that this can be empty, and default values do not necessarily exist.
   */
  public function getModuleSettings($module);

  /**
   * Determines whether the property type is locked.
   *
   * @return string|false
   *   The module name that locks the type or FALSE.
   */
  public function isLocked();

}
