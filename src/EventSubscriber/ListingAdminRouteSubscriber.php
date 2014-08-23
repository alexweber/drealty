<?php

/**
 * @file
 * Contains \Drupal\drealty\EventSubscriber\ListingAdminRouteSubscriber.
 */

namespace Drupal\drealty\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Sets the _admin_route for specific listing-related routes.
 */
class ListingAdminRouteSubscriber extends RouteSubscriberBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ListingAdminRouteSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Let's treat this setting as a global config.
    if ($this->configFactory->get('node.settings')->get('use_admin_theme')) {
      foreach ($collection->all() as $route) {
        if ($route->hasOption('_drealty_operation_route')) {
          $route->setOption('_admin_route', TRUE);
        }
      }
    }
  }

}
