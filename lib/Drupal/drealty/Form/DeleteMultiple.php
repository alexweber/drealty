<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\DeleteMultiple.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Component\Utility\String;
use Drupal\user\TempStoreFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a drealty listing deletion confirmation form.
 */
class DeleteMultiple extends ConfirmFormBase {

  /**
   * The array of drealty listings to delete.
   *
   * @var array
   */
  protected $drealty_listings = array();

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\TempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The drealty listing storage.
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
    return \Drupal::translation()->formatPlural(count($this->drealty_listings), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
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
  public function buildForm(array $form, array &$form_state) {
    $this->drealty_listings = $this->tempStoreFactory->get('drealty_listing_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->drealty_listings)) {
      return new RedirectResponse(url('admin/content/drealty', array('absolute' => TRUE)));
    }

    $form['drealty_listings'] = array(
      '#theme' => 'item_list',
      '#items' => array_map(function ($drealty_listing) {
        return String::checkPlain($drealty_listing->label());
      }, $this->drealty_listings),
    );
    $form = parent::buildForm($form, $form_state);

    // @todo Convert to getCancelRoute() after http://drupal.org/node/2021161.
    $form['actions']['cancel']['#href'] = 'admin/content/drealty';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    if ($form_state['values']['confirm'] && !empty($this->drealty_listings)) {
      $this->storage->delete($this->drealty_listings);
      $this->tempStoreFactory->get('drealty_listing_multiple_delete_confirm')->delete(\Drupal::currentUser()->id());
      $count = count($this->drealty_listings);
      watchdog('drealty', 'Deleted @count drealty listings.', array('@count' => $count));
      drupal_set_message(\Drupal::translation()->formatPlural($count, 'Deleted 1 drealty listing.', 'Deleted @count drealty listings.'));
      Cache::invalidateTags(array('content' => TRUE)); // @TODO figure out what this means.
    }
    $form_state['redirect_route']['route_name'] = 'drealty.listing_overview';
  }

}
