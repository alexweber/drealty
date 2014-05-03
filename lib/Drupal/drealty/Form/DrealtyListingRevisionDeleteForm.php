<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\DrealtyListingRevisionDeleteForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\drealty\DrealtyListingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a drealty listing revision.
 */
class DrealtyListingRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The drealty listing revision.
   *
   * @var \Drupal\drealty\DrealtyListingInterface
   */
  protected $revision;

  /**
   * The drealty listing storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $drealty_listingStorage;

  /**
   * The drealty listing type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $drealty_listingTypeStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new DrealtyListingRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $drealty_listing_storage
   *   The drealty listinh storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $drealty_listing_type_storage
   *   The drealty listing type storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $drealty_listing_storage, EntityStorageInterface $drealty_listing_type_storage, Connection $connection) {
    $this->drealty_listingStorage = $drealty_listing_storage;
    $this->drealty_listingTypeStorage = $drealty_listing_type_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager->getStorage('drealty_listing'),
      $entity_manager->getStorage('drealty_listing_type'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drealty_listing_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', array('%revision-date' => format_date($this->revision->getRevisionCreationTime())));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $drealty_listing_revision = NULL) {
    $this->revision = $this->drealty_listingStorage->loadRevision($drealty_listing_revision);
    $form = parent::buildForm($form, $form_state);

    // @todo Convert to getCancelRoute() after http://drupal.org/node/1863906.
    $form['actions']['cancel']['#href'] = 'drealty-listing/' . $this->revision->id() . '/revisions';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->drealty_listingStorage->deleteRevision($this->revision->getRevisionId());

    watchdog('drealty', '@type: deleted %title revision %revision.', array('@type' => $this->revision->bundle(), '%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()));
    $drealty_listing_type = $this->drealty_listingTypeStorage->load($this->revision->bundle())->label();
    drupal_set_message(t('Revision from %revision-date of @type %title has been deleted.', array('%revision-date' => format_date($this->revision->getRevisionCreationTime()), '@type' => $drealty_listing_type, '%title' => $this->revision->label())));
    $form_state['redirect_route'] = array(
      'route_name' => 'drealty.listing_view',
      'route_parameters' => array(
        'drealty_listing' => $this->revision->id(),
      ),
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {drealty_listing_field_revision} WHERE nid = :nid', array(':nid' => $this->revision->id()))->fetchField() > 1) {
      $form_state['redirect_route']['route_name'] = 'drealty.listing_revision_overview';
    }
  }

}
