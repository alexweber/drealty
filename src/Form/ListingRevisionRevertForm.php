<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ListingRevisionDeleteForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\drealty\ListingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a listing revision.
 */
class ListingRevisionRevertForm extends ConfirmFormBase {

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
   * Constructs a new ListingRevisionRevertForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $listing_storage
   *   The listing storage.
   */
  public function __construct(EntityStorageInterface $listing_storage) {
    $this->listingStorage = $listing_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('drealty_listing')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drealty_listing_revision_revert_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to revert to the revision from %revision-date?', array('%revision-date' => format_date($this->revision->getRevisionCreationTime())));
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
    return t('Revert');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
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
    $this->revision->setNewRevision();
    // Make this the new default revision for the listing.
    $this->revision->isDefaultRevision(TRUE);

    // The revision timestamp will be updated when the revision is saved. Keep the
    // original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision->revision_log = t('Copy of the revision from %date.', array('%date' => format_date($original_revision_timestamp)));

    $this->revision->save();

    $this->logger('drealty')->notice('@type: reverted %title revision %revision.', array('@type' => $this->revision->bundle(), '%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()));
    drupal_set_message(t('@type %title has been reverted back to the revision from %revision-date.', array('@type' => $this->revision->bundle(), '%title' => $this->revision->label(), '%revision-date' => format_date($original_revision_timestamp))));
    $form_state->setRedirect(
      'drealty.listing_revision_overview',
      array('drealty_listing' => $this->revision->id())
    );
  }

}
