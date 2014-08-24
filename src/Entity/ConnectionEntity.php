<?php

/**
 * @file
 * Contains \Drupal\drealty\Entity\ConnectionEntity.
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
 *     "edit-form" = "entity.drealty_connection.edit_form",
 *     "delete-form" = "entity.drealty_connection.delete_form"
 *   }
 * )
 */
class ConnectionEntity extends ConfigEntityBase implements ConnectionInterface {

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
   * The connection login url.
   *
   * @var string
   */
  protected $url;

  /**
   * The connection login username.
   *
   * @var string
   */
  protected $username;

  /**
   * The connection login password.
   *
   * @var string
   */
  protected $password;

  /**
   * Public getter for login URL.
   *
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Public getter for login username.
   *
   * @return string
   */
  public function getUsername() {
    return $this->username;
  }

  /**
   * Public getter for login password.
   *
   * @return string
   */
  public function getPassword() {
    return $this->password;
  }

}
