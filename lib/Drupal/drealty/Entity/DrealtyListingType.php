<?php

/**
 * @file
 * Contains \Drupal\drealty\Entity\DrealtyListingType.
 */

namespace Drupal\drealty\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\drealty\DrealtyListingTypeInterface;

/**
 * Defines the Drealty Listing type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "drealty_listing_type",
 *   label = @Translation("DRealty Listing type"),
 *   controllers = {
 *     "access" = "Drupal\drealty\DrealtyListingTypeAccessController",
 *     "form" = {
 *       "add" = "Drupal\drealty\DrealtyListingTypeFormController",
 *       "edit" = "Drupal\drealty\DrealtyListingTypeFormController",
 *       "delete" = "Drupal\drealty\Form\DrealtyListingTypeDeleteConfirm"
 *     },
 *     "list_builder" = "Drupal\drealty\DrealtyListingTypeListBuilder",
 *   },
 *   admin_permission = "administer drealty listing types",
 *   config_prefix = "type",
 *   bundle_of = "drealty_listing",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "add-form" = "drealty.type_add",
 *     "edit-form" = "drealty.type_edit",
 *     "delete-form" = "drealty.type_delete_confirm"
 *   }
 * )
 */
class DrealtyListingType extends ConfigEntityBase implements DrealtyListingTypeInterface {

  /**
   * The machine name of this drealty listing type.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  public $type;

  /**
   * The human-readable name of the drealty listing type.
   *
   * @var string
   *
   * @todo Rename to $label.
   */
  public $name;

  /**
   * A brief description of this drealty listing type.
   *
   * @var string
   */
  public $description;

  /**
   * Help information shown to the user when creating a Drealty Listing of this
   * type.
   *
   * @var string
   */
  public $help;

  /**
   * Indicates whether the Drealty Listing entity of this type has a title.
   *
   * @var bool
   *
   * @todo Rename to $drealty_listing_has_title.
   */
  public $has_title = TRUE;

  /**
   * The label to use for the title of a Drealty Listing of this type in the
   * user interface.
   *
   * @var string
   *
   * @todo Rename to $drealty_listing_title_label.
   */
  public $title_label = 'Title';

  /**
   * Module-specific settings for this drealty listing type, keyed by module name.
   *
   * @var array
   *
   * @todo Pluginify.
   */
  public $settings = array();

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleSettings($module) {
    if (isset($this->settings[$module]) && is_array($this->settings[$module])) {
      return $this->settings[$module];
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $locked = \Drupal::state()->get('drealty.type.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if (!$update) {
      entity_invoke_bundle_hook('create', 'drealty_listing', $this->id());
    }
    elseif ($this->getOriginalId() != $this->id()) {
      // @TODO refactor this or drop it.
//      $update_count = node_type_update_nodes($this->getOriginalId(), $this->id());
//      if ($update_count) {
//        drupal_set_message(format_plural($update_count,
//          'Changed the content type of 1 post from %old-type to %type.',
//          'Changed the content type of @count posts from %old-type to %type.',
//          array(
//            '%old-type' => $this->getOriginalId(),
//            '%type' => $this->id(),
//          )));
//      }
      entity_invoke_bundle_hook('rename', 'drealty_listing', $this->getOriginalId(), $this->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Clear the drealty listing cache to reflect the removal.
    $storage->resetCache(array_keys($entities));
    foreach ($entities as $entity) {
      entity_invoke_bundle_hook('delete', 'drealty_listing', $entity->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);

    // Ensure default values are set.
    if (!isset($values['settings']['drealty_listing'])) {
      $values['settings']['drealty_listing'] = array();
    }
    $values['settings']['drealty_listing'] = NestedArray::mergeDeep(array(
      'options' => array(
        'status' => TRUE,
        'revision' => FALSE,
      ),
    ), $values['settings']['drealty_listing']);
  }

}
