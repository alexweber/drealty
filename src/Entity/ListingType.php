<?php

/**
 * @file
 * Contains \Drupal\drealty\Entity\ListingType
 */

namespace Drupal\drealty\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\drealty\ListingTypeInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Defines the Listing type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "drealty_listing_type",
 *   label = @Translation("Listing type"),
 *   controllers = {
 *     "access" = "Drupal\drealty\ListingTypeAccessController",
 *     "form" = {
 *       "add" = "Drupal\drealty\Form\ListingTypeForm",
 *       "edit" = "Drupal\drealty\Form\ListingTypeForm",
 *       "delete" = "Drupal\drealty\Form\ListingTypeDeleteForm"
 *     },
 *     "list_builder" = "Drupal\drealty\ListingTypeListBuilder",
 *   },
 *   admin_permission = "administer drealty listing types",
 *   config_prefix = "type",
 *   bundle_of = "drealty_listing",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "name"
 *   },
 *   links = {
 *     "add-form" = "drealty.listing_type_add",
 *     "edit-form" = "entity.drealty_listing_type.edit_form",
 *     "delete-form" = "entity.drealty_listing_type.delete_form"
 *   }
 * )
 */
class ListingType extends ConfigEntityBundleBase implements ListingTypeInterface {

  /**
   * The machine name of this listing type.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  public $type;

  /**
   * The human-readable name of the listing type.
   *
   * @var string
   *
   * @todo Rename to $label.
   */
  public $name;

  /**
   * A brief description of this listing type.
   *
   * @var string
   */
  public $description;

  /**
   * Help information shown to the user when creating a listing of this type.
   *
   * @var string
   */
  public $help;

  /**
   * Module-specific settings for this listing type, keyed by module name.
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
  public function isLocked() {
    // @TODO verify this.
    $locked = \Drupal::state()->get('drealty.listing_type.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
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
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // If updating...
    if ($update) {
      // Check for changed type and react accordingly.
      if ($this->getOriginalId() != $this->id()) {
        $update_count = drealty_listing_type_update_listings($this->getOriginalId(), $this->id());
        if ($update_count) {

          drupal_set_message(format_plural($update_count,
            'Changed the type of 1 listing from %old-type to %type.',
            'Changed the type of @count listings from %old-type to %type.',
            array(
              '%old-type' => $this->getOriginalId(),
              '%type' => $this->id(),
            )));
        }
      }

      // Clear the cached field definitions as some settings affect the field
      // definitions.
      $this->entityManager()->clearCachedFieldDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Clear the listing type cache to reflect the removal.
    $storage->resetCache(array_keys($entities));
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);

    // Ensure default values are set.
    if (!isset($values['settings']['drealty'])) {
      $values['settings']['drealty'] = array();
    }
    $values['settings']['drealty'] = NestedArray::mergeDeep(array(
      'options' => array(
        'status' => TRUE,
        'promote' => TRUE,
        'sticky' => FALSE,
        'revision' => FALSE,
      ),
    ), $values['settings']['drealty']);
  }

}
