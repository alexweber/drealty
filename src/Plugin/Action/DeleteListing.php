<?php

/**
 * @file
 * Contains \Drupal\drealty\Plugin\Action\DeleteListing.
 */

namespace Drupal\drealty\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\user\TempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Redirects to a listing deletion form.
 *
 * @Action(
 *   id = "drealty_listing_delete_action",
 *   label = @Translation("Delete selected listings"),
 *   type = "drealty_listing",
 *   confirm_form_path = "admin/content/drealty/delete"
 * )
 */
class DeleteListing extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The tempstore object.
   *
   * @var \Drupal\user\TempStore
   */
  protected $tempStore;

  /**
   * Constructs a new DeleteListing object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\TempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TempStoreFactory $temp_store_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->tempStore = $temp_store_factory->get('drealty_listing_multiple_delete_confirm');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('user.tempstore'));
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $this->tempStore->set(\Drupal::currentUser()->id(), $entities);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple(array($object));
  }

}
