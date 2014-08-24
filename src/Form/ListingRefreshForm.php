<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ListingRefreshForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for refreshing a listing's RETS data.
 */
class ListingRefreshForm extends ContentEntityConfirmFormBase {

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a ListingRefreshForm object.
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
    return t('Are you sure you want to refresh RETS data for %title?', array('%title' => $this->entity->label()));
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
    return t('Refresh');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    // @TODO
//    $this->entity->refresh();
    $this->logger('content')->notice('@type: refreshed RETS data for: %title.', array('@type' => $this->entity->bundle(), '%title' => $this->entity->label()));
    $listing_type_storage = $this->entityManager->getStorage('drealty_listing_type');
    $listing_type = $listing_type_storage->load($this->entity->bundle())->label();
    drupal_set_message(t('@type %title has been refreshed.', array('@type' => $listing_type, '%title' => $this->entity->label())));

    if ($this->entity->access('view')) {
      $form_state->setRedirect(
        'entity.drealty_listing.canonical',
        array('drealty_listing' => $this->entity->id())
      );
    }
    else {
      $form_state->setRedirect('<front>');
    }
  }

}
