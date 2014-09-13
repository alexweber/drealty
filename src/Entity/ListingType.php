<?php

/**
 * @file
 * Contains \Drupal\drealty\Entity\ListingType
 */

namespace Drupal\drealty\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Config\Entity\ThirdPartySettingsTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\drealty\ListingTypeInterface;

/**
 * Defines the Listing type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "drealty_listing_type",
 *   label = @Translation("Listing type"),
 *   handlers = {
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
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "entity.drealty_listing_type.edit_form",
 *     "delete-form" = "entity.drealty_listing_type.delete_form"
 *   }
 * )
 */
class ListingType extends ConfigEntityBundleBase implements ListingTypeInterface {
  use ThirdPartySettingsTrait;

  /**
   * The machine name of this listing type.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the listing type.
   *
   * @var string
   */
  public $label;

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
   * Default value of the 'Create new revision' checkbox of this node type.
   *
   * @var bool
   */
  protected $new_revision = FALSE;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    // @TODO verify this.
    $locked = \Drupal::state()->get('drealty_listing.type.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isNewRevision() {
    return $this->new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision) {
    $this->new_revision = $new_revision;
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

}
