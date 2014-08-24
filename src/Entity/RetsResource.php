<?php

/**
 * @file
 * Contains Drupal\drealty\Entity\RetsResource.
 */

namespace Drupal\drealty\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\drealty\RetsResourceInterface;

/**
 * Defines the RetsResource entity.
 *
 * @ConfigEntityType(
 *   id = "drealty_resource",
 *   label = @Translation("RETS Resource"),
 *   controllers = {
 *     "list_builder" = "Drupal\drealty\Controller\RetsResourceListBuilder",
 *     "form" = {
 *       "add" = "Drupal\drealty\Form\RetsResourceForm",
 *       "edit" = "Drupal\drealty\Form\RetsResourceForm",
 *       "delete" = "Drupal\drealty\Form\RetsResourceDeleteForm"
 *     }
 *   },
 *   config_prefix = "drealty_resource",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "drealty_resource.edit",
 *     "delete-form" = "drealty_resource.delete"
 *   }
 * )
 */
class RetsResource extends ConfigEntityBase implements RetsResourceInterface {

  /**
   * The RetsResource ID.
   *
   * @var string
   */
  public $id;

  /**
   * The RetsResource UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The RetsResource label.
   *
   * @var string
   */
  public $label;


}
