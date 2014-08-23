<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ListingDeleteMultiple.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\TempStoreFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing deletion confirmation form.
 */
class ListingDeleteMultiple extends ConfirmFormBase {

  /**
   * The array of listings to delete.
   *
   * @var array
   */
  protected $listings = array();

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\TempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The listing storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $manager;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\TempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
   *   The entity manager.
   */
  public function __construct(TempStoreFactory $temp_store_factory, EntityManagerInterface $manager) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->storage = $manager->getStorage('drealty_listing');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.tempstore'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drealty_listing_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return format_plural(count($this->listings), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('drealty.listing_list');
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->listings = $this->tempStoreFactory->get('drealty_listing_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->listings)) {
      return new RedirectResponse(url('admin/content/drealty', array('absolute' => TRUE)));
    }

    $form['listings'] = array(
      '#theme' => 'item_list',
      '#items' => array_map(function ($listing) {
        return String::checkPlain($listing->label());
      }, $this->listings),
    );
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state['values']['confirm'] && !empty($this->listings)) {
      $this->storage->delete($this->listings);
      $this->tempStoreFactory->get('drealty_listing_multiple_delete_confirm')->delete(\Drupal::currentUser()->id());
      $count = count($this->listings);
      $this->logger('content')->notice('Deleted @count listings.', array('@count' => $count));
      drupal_set_message(format_plural($count, 'Deleted 1 listing.', 'Deleted @count listings.'));
    }
    $form_state->setRedirect('drealty.listing_list');
  }

}
