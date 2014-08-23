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
 *       "add" = "Drupal\drealty\Form\ConnectionForm",
 *       "edit" = "Drupal\drealty\Form\ConnectionForm",
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
 *     "edit-form" = "drealty.connection_edit",
 *     "delete-form" = "drealty.connection_delete",
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
}
