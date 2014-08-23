<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ListingRevisionDeleteForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\drealty\ListingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a listing revision.
 */
class ListingRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The listing revision.
   *
   * @var \Drupal\drealty\ListingInterface
   */
  protected $revision;

  /**
   * The listing storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $listingStorage;

  /**
   * The listing type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $listingTypeStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new ListingRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $listing_storage
   *   The listing storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $listing_type_storage
   *   The listing type storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $listing_storage, EntityStorageInterface $listing_type_storage, Connection $connection) {
    $this->listingStorage = $listing_storage;
    $this->listingTypeStorage = $listing_type_storage;
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
  public function getCancelUrl() {
    return new Url('drealty.listing_revision_overview', array('drealty_listing' => $this->revision->id()));
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
  public function buildForm(array $form, FormStateInterface $form_state, $listing_revision = NULL) {
    $this->revision = $this->listingStorage->loadRevision($listing_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->listingStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('@type: deleted %title revision %revision.', array('@type' => $this->revision->bundle(), '%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()));
    $listing_type = $this->listingTypeStorage->load($this->revision->bundle())->label();
    drupal_set_message(t('Revision from %revision-date of @type %title has been deleted.', array('%revision-date' => format_date($this->revision->getRevisionCreationTime()), '@type' => $listing_type, '%title' => $this->revision->label())));
    $form_state->setRedirect(
      'drealty.drealty_listing_view',
      array('drealty_listing' => $this->revision->id())
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {drealty_listing_field_revision} WHERE id = :id', array(':id' => $this->revision->id()))->fetchField() > 1) {
      $form_state->setRedirect(
        'drealty.listing_revision_overview',
        array('drealty_listing' => $this->revision->id())
      );
    }
  }

}
