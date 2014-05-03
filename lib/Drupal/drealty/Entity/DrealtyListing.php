<?php

/**
 * @file
 * Contains \Drupal\drealty\Entity\DrealtyListing.
 */

namespace Drupal\drealty\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;
use Drupal\drealty\DrealtyListingInterface;
use Drupal\user\UserInterface;

/**
 * Defines the drealty listing entity class.
 *
 * @ContentEntityType(
 *   id = "drealty_listing",
 *   label = @Translation("Drealty Listing"),
 *   bundle_label = @Translation("Drealty Listing type"),
 *   controllers = {
 *     "view_builder" = "Drupal\drealty\DrealtyListingViewBuilder",
 *     "access" = "Drupal\drealty\DrealtyListingAccessController",
 *     "form" = {
 *       "default" = "Drupal\drealty\DrealtyListingFormController",
 *       "delete" = "Drupal\drealty\Form\DrealtyListingDeleteForm",
 *       "edit" = "Drupal\drealty\DrealtyListingFormController"
 *     },
 *     "list_builder" = "Drupal\drealty\DrealtyListingListBuilder",
 *     "translation" = "Drupal\drealty\DrealtyListingTranslationHandler"
 *   },
 *   base_table = "drealty_listing",
 *   data_table = "drealty_listing_field_data",
 *   revision_table = "drealty_listing_revision",
 *   revision_data_table = "drealty_listing_field_revision",
 *   uri_callback = "drealty_listing_uri",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "nid",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   bundle_entity_type = "drealty_listing_type",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "drealty.listing_view",
 *     "delete-form" = "drealty.listing_delete_confirm",
 *     "edit-form" = "drealty.listing_edit",
 *     "version-history" = "drealty.listing_revision_overview",
 *     "admin-form" = "drealty.type_edit"
 *   }
 * )
 */
class DrealtyListing extends ContentEntityBase implements DrealtyListingInterface {

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if ($this->newRevision) {
      // When inserting either a new drealty listing or a new drealty listing
      // revision, $drealty_listing->log must be set because {drealty_listing_field_revision}.log
      // is a text column and therefore cannot have a default value. However, it
      // might not be set at this point (for example, if the user submitting a
      // drealty listing form does not have permission to create revisions), so
      // we ensure that it is at least an empty string in that case.
      //
      // @todo Make the {drealty_listing_field_revision}.log column nullable so
      //   that we can remove this check.
      if (!isset($record->log)) {
        $record->log = '';
      }
    }
    elseif (isset($this->original) && (!isset($record->log) || $record->log === '')) {
      // If we are updating an existing drealty listing without adding a new
      // revision, we need to make sure $entity->log is reset whenever it is
      // empty. Therefore, this code allows us to avoid clobbering an existing
      // log entry with an empty one.
      $record->log = $this->original->log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Reindex the drealty listing when it is updated. The drealty listting is
    // automatically indexed when it is added, simply by being added to the
    // drealty_listing table.
    // @TODO figure out if we want to implement search stuff from node.
//    if ($update) {
//      node_reindex_node_search($this->id());
//    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    // @TODO figure out if we want to implement search stuff from node.
    // Assure that all drealty listing s deleted are removed from the search
    // index.
//    if (\Drupal::moduleHandler()->moduleExists('search')) {
//      foreach ($entities as $entity) {
//        search_reindex($entity->nid->value, 'node_search');
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
      // Load languages the drealty listing exists in.
      $drealty_listing_translations = $this->getTranslationLanguages();
      // Load the language from content negotiation.
      $content_negotiation_langcode = \Drupal::languageManager()->getCurrentLanguage(Language::TYPE_CONTENT)->id;
      // If there is a translation available, use it.
      if (isset($drealty_listing_translations[$content_negotiation_langcode])) {
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
    $fields['nid'] = FieldDefinition::create('integer')
      ->setLabel(t('Drealty Listing ID'))
      ->setDescription(t('The drealty listing ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The drealty listing UUID.'))
      ->setReadOnly(TRUE);

    $fields['vid'] = FieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The drealty listing revision ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['type'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The drealty listing type.'))
      ->setSetting('target_type', 'drealty_listing_type')
      ->setReadOnly(TRUE);

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The drealty listing language code.'))
      ->setRevisionable(TRUE);

    $fields['title'] = FieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of this drealty listing, always treated as non-markup plain text.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ))
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
      ->setDescription(t('The user that is the drealty listing author.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'target_type' => 'user',
        'default_value' => 0,
      ))
      ->setTranslatable(TRUE);

    $fields['status'] = FieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the drealty listing is published.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['created'] = FieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the drealty listing was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['changed'] = FieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the drealty listing was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['revision_timestamp'] = FieldDefinition::create('timestamp')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSettings(array('target_type' => 'user'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['log'] = FieldDefinition::create('string')
      ->setLabel(t('Log'))
      ->setDescription(t('The log entry explaining the changes in this revision.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    $drealty_listing_type = drealty_listing_type_load($bundle);
    $fields = array();
    if (isset($drealty_listing_type->title_label)) {
      $fields['title'] = clone $base_field_definitions['title'];
      $fields['title']->setLabel($drealty_listing_type->title_label);
    }
    return $fields;
  }

}
