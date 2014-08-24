<?php

/**
 * @file
 * Contains \Drupal\drealty\Entity\Connection.
 */

namespace Drupal\drealty\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\drealty\ConnectionInterface;

/**
 * Defines a DRealty Connection configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "drealty_connection",
 *   label = @Translation("DRealty Connection"),
 *   fieldable = FALSE,
 *   controllers = {
 *     "list_builder" = "Drupal\drealty\ConnectionListBuilder",
 *     "view_builder" = "Drupal\drealty\ConnectionViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\drealty\ConnectionForm",
 *       "edit" = "Drupal\drealty\ConnectionForm",
 *       "delete" = "Drupal\drealty\Form\ConnectionDeleteForm"
 *     }
 *   },
 *   config_prefix = "drealty_connection",
 *   admin_permission = "administer drealty connections",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name"
 *   },
 *   links = {
 *     "view" = "drealty.drealty_connection_view",
 *     "edit-form" = "entity.drealty_connection.edit_form",
 *     "delete-form" = "entity.drealty_connection.delete_form"
 *   }
 * )
 */
class Connection extends ConfigEntityBase implements ConnectionInterface {

  /**
   * The connection ID.
   *
   * @var string
   */
  public $id;

  /**
   * The connection name.
   *
   * @var string
   */
  public $name;

  /**
   * The connection RETS version.
   *
   * @var string
   */
  public $rets;

  /**
   * The connection login url.
   *
   * @var string
   */
  public $url;

  /**
   * The connection login username.
   *
   * @var string
   */
  public $username;

  /**
   * The connection login password.
   *
   * @var string
   */
  public $password;

  /**
   * The connection user agent string.
   *
   * @var string
   */
  public $ua_string;

  /**
   * The connection user agent password.
   *
   * @var string
   */
  public $ua_password;

  /**
   * Use interealty authentication.
   *
   * @var boolean
   */
  public $use_interealty_auth;

  /**
   * Force basic authentication.
   *
   * @var boolean
   */
  public $force_basic_auth;

  /**
   * Use compression.
   *
   * @var boolean
   */
  public $use_compression;

  /**
   * Disable encoding fix.
   *
   * @var boolean
   */
  public $disable_encoding_fix;

  /**
   * Debug mode.
   *
   * @var boolean
   */
  public $debug_mode;

  /**
   * No map mode.
   *
   * @var boolean
   */
  public $nomap_mode;

}
