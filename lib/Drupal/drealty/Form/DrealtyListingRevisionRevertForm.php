<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\DrealtyListingRevisionDeleteForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\drealty\DrealtyListingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a drealty listing revision.
 */
class DrealtyListingRevisionRevertForm extends ConfirmFormBase {

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
   * Constructs a new DrealtyListingRevisionRevertForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $drealty_listing_storage
   *   The drealty listing storage.
   */
  public function __construct(EntityStorageInterface $drealty_listing_storage) {
    $this->drealty_listingStorage = $drealty_listing_storage;
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
  public function getCancelRoute() {
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
    $this->revision->setNewRevision();
    // Make this the new default revision for the drealty listing.
    $this->revision->isDefaultRevision(TRUE);

    // The revision timestamp will be updated when the revision is saved. Keep the
    // original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision->log = t('Copy of the revision from %date.', array('%date' => format_date($original_revision_timestamp)));

    $this->revision->save();

    watchdog('drealty', '@type: reverted %title revision %revision.', array('@type' => $this->revision->bundle(), '%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()));
    drupal_set_message(t('@type %title has been reverted back to the revision from %revision-date.', array('@type' => drealty_listing_get_type_label($this->revision), '%title' => $this->revision->label(), '%revision-date' => format_date($original_revision_timestamp))));
    $form_state['redirect_route'] = array(
      'route_name' => 'drealty.listing_revision_overview',
      'route_parameters' => array(
        'drealty_listing' => $this->revision->id(),
      ),
    );
  }

}
