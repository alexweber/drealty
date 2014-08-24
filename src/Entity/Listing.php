<?php

/**
 * @file
 * Contains \Drupal\drealty\Entity\Listing.
 */

namespace Drupal\drealty\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\drealty\ListingInterface;
use Drupal\user\UserInterface;

/**
 * Defines the listing entity class.
 *
 * @ContentEntityType(
 *   id = "drealty_listing",
 *   label = @Translation("DRealty Listing"),
 *   bundle_label = @Translation("Listing type"),
 *   controllers = {
 *     "storage" = "Drupal\drealty\ListingStorage",
 *     "view_builder" = "Drupal\drealty\ListingViewBuilder",
 *     "access" = "Drupal\drealty\ListingAccessController",
 *     "form" = {
 *       "default" = "Drupal\drealty\ListingForm",
 *       "delete" = "Drupal\drealty\Form\ListingDeleteForm",
 *       "edit" = "Drupal\drealty\ListingForm",
 *       "refresh" = "Drupal\drealty\Form\ListingRefreshForm",
 *     },
 *     "list_builder" = "Drupal\drealty\ListingListBuilder",
 *     "translation" = "Drupal\drealty\ListingTranslationHandler"
 *   },
 *   base_table = "drealty_listing",
 *   data_table = "drealty_listing_field_data",
 *   revision_table = "drealty_listing_revision",
 *   revision_data_table = "drealty_listing_field_revision",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   bundle_entity_type = "drealty_listing_type",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "drealty.listing_view",
 *     "delete-form" = "entity.drealty_listing.delete_form",
 *     "edit-form" = "entity.drealty_listing.edit_form",
 *     "version-history" = "drealty.listing_revision_overview",
 *     "admin-form" = "entity.drealty_listing_type.edit_form"
 *   }
 * )
 */
class Listing extends ContentEntityBase implements ListingInterface {

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If no owner has been set explicitly, make the current user the owner.
    if (!$this->getOwner()) {
      $this->setOwnerId(\Drupal::currentUser()->id());
    }
    // If no revision author has been set explicitly, make the listing owner the
    // revision author.
    if (!$this->getRevisionAuthor()) {
      $this->setRevisionAuthorId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing listing without adding a new revision, we
      // need to make sure $entity->revision_log is reset whenever it is empty.
      // Therefore, this code allows us to avoid clobbering an existing log
      // entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Reindex the listing when it is updated. The listing is automatically
    // indexed when it is added, simply by being added to the drealty_listing
    // table.
    if ($update) {
      // @TODO search integration?
//      drealty_reindex_listing_search($this->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    // @TODO search integration?
    // Assure that all listings deleted are removed from the search index.
//    if (\Drupal::moduleHandler()->moduleExists('search')) {
//      foreach ($entities as $entity) {
//        search_reindex($entity->id->value, 'listing_search');
//      }
//    }
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'view', AccountInterface $account = NULL) {
    if ($operation == 'create') {
      return parent::access($operation, $account);
    }

    return \Drupal::entityManager()
      ->getAccessController($this->entityTypeId)
      ->access($this, $operation, $this->prepareLangcode(), $account);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareLangcode() {
    $langcode = $this->language()->id;
    // If the Language module is enabled, try to use the language from content
    // negotiation.
    if (\Drupal::moduleHandler()->moduleExists('language')) {
      // Load languages the listing exists in.
      $listing_translations = $this->getTranslationLanguages();
      // Load the language from content negotiation.
      $content_negotiation_langcode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->id;
      // If there is a translation available, use it.
      if (isset($listing_translations[$content_negotiation_langcode])) {
        $langcode = $content_negotiation_langcode;
      }
    }
    return $langcode;
  }


  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }


  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isPromoted() {
    return (bool) $this->get('promote')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPromoted($promoted) {
    $this->set('promote', $promoted ? NODE_PROMOTED : NODE_NOT_PROMOTED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isSticky() {
    return (bool) $this->get('sticky')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSticky($sticky) {
    $this->set('sticky', $sticky ? NODE_STICKY : NODE_NOT_STICKY);
    return $this;
  }
  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->set('revision_uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = FieldDefinition::create('integer')
      ->setLabel(t('Listing ID'))
      ->setDescription(t('The listing ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The listing UUID.'))
      ->setReadOnly(TRUE);

    $fields['vid'] = FieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The listing revision ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['type'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The listing type.'))
      ->setSetting('target_type', 'drealty_listing_type')
      ->setReadOnly(TRUE);

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The listing language code.'))
      ->setRevisionable(TRUE);

    $fields['title'] = FieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of this listing, always treated as non-markup plain text.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user that is the listing author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setTranslatable(TRUE);

    $fields['status'] = FieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the listing is published.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['created'] = FieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the listing was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['changed'] = FieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the listing was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['promote'] = FieldDefinition::create('boolean')
      ->setLabel(t('Promote'))
      ->setDescription(t('A boolean indicating whether the listing should be displayed on the front page.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['sticky'] = FieldDefinition::create('boolean')
      ->setLabel(t('Sticky'))
      ->setDescription(t('A boolean indicating whether the listing should be displayed at the top of lists in which it appears.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['revision_timestamp'] = FieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_log'] = FieldDefinition::create('string_long')
      ->setLabel(t('Revision log message'))
      ->setDescription(t('The log entry explaining the changes in this revision.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    $listing_type = ListingType::load($bundle);
    $fields = array();

    // When deleting a listing type the corresponding listing displays are deleted as
    // well. In order to be deleted, they need to be loaded first. Entity
    // displays, however, fetch the field definitions of the respective entity
    // type to fill in their defaults. Therefore this function ends up being
    // called with a non-existing bundle.
    // @todo Fix this in https://drupal.org/node/2248795
    if (!$listing_type) {
      return $fields;
    }

    if (isset($listing_type->title_label)) {
      $fields['title'] = clone $base_field_definitions['title'];
      $fields['title']->setLabel($listing_type->title_label);
    }

    $options = $listing_type->getModuleSettings('drealty')['options'];
    $fields['status'] = clone $base_field_definitions['status'];
    $fields['status']->setDefaultValue(!empty($options['status']) ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    $fields['promote'] = clone $base_field_definitions['promote'];
    $fields['promote']->setDefaultValue(!empty($options['promote']) ? NODE_PROMOTED : NODE_NOT_PROMOTED);
    $fields['sticky'] = clone $base_field_definitions['sticky'];
    $fields['sticky']->setDefaultValue(!empty($options['sticky']) ? NODE_STICKY : NODE_NOT_STICKY);

    return $fields;
  }

}
