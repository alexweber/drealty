<?php

/**
 * @file
 * Contains \Drupal\drealty\EventSubscriber\ListingRouteContext.
 */

namespace Drupal\drealty\EventSubscriber;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\drealty\Entity\Listing;
use Drupal\block\EventSubscriber\BlockConditionContextSubscriberBase;

/**
 * Sets the current listing as a context on listing routes.
 */
class ListingRouteContext extends BlockConditionContextSubscriberBase {

  /**
   * The route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new ListingRouteContext.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  protected function determineBlockContext() {
    if (($route_object = $this->routeMatch->getRouteObject()) && ($route_contexts = $route_object->getOption('parameters')) && isset($route_contexts['drealty_listing'])) {
      $context = new Context(new ContextDefinition($route_contexts['drealty_listing']['type']));
      if ($listing = $this->routeMatch->getParameter('drealty_listing')) {
        $context->setContextValue($listing);
      }
      $this->addContext('drealty_listing', $context);
    }
    elseif ($this->routeMatch->getRouteName() == 'drealty.listing_add') {
      $listing_type = $this->routeMatch->getParameter('drealty_listing_type');
      $context = new Context(new ContextDefinition('entity:drealty_listing'));
      $context->setContextValue(Listing::create(array('type' => $listing_type->id())));
      $this->addContext('drealty_listing', $context);
    }
  }

}
