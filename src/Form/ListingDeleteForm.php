<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ListingDeleteForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a listing.
 */
class ListingDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a ListingDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   */
  public function __construct(EntityManagerInterface $entity_manager, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_manager);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete %title?', array('%title' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->urlInfo();
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $this->logger('drealty')->notice('Listing type deleted: %title.', array('%title' => $this->entity->label()));
    $listing_type_storage = $this->entityManager->getStorage('drealty_listing_type');
    $listing_type = $listing_type_storage->load($this->entity->bundle())->label();
    drupal_set_message(t('@type %title has been deleted.', array('@type' => $listing_type, '%title' => $this->entity->label())));
    $form_state->setRedirect('<front>');
  }

}
