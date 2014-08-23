<?php

/**
 * @file
 *
 */

use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\Element;

/**
 * Prepares variables for list of available listing type templates.
 *
 * Default template: drealty-listing-add-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - content: An array of listing types.
 */
function template_preprocess_drealty_listing_add_list(&$variables) {
  $variables['types'] = array();
  if (!empty($variables['content'])) {
    foreach ($variables['content'] as $type) {
      $variables['types'][$type->type] = array(
        'type' => $type->type,
        'add_link' => l($type->name, 'drealty_listing/add/' . $type->type),
        'description' => Xss::filterAdmin($type->description),
      );
    }
  }
}

/**
 * Prepares variables for listing templates.
 *
 * Default template: drealty_listing.html.twig.
 *
 * Most themes utilize their own copy of drealty_listing.html.twig. The default
 * is located inside "templates/drealty_listing.html.twig". Look in there for
 * the full list of variables.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An array of elements to display in view mode.
 *   - drealty_listing: The listing object.
 *   - view_mode: View mode; e.g., 'full', 'teaser'...
 */
function template_preprocess_drealty_listing(&$variables) {
  $variables['view_mode'] = $variables['elements']['#view_mode'];
  // Provide a distinct $teaser boolean.
  $variables['teaser'] = $variables['view_mode'] == 'teaser';
  $variables['drealty_listing'] = $variables['elements']['#drealty_listing'];
  /** @var \Drupal\drealty\ListingInterface $listing */
  $listing = $variables['drealty_listing'];

  $variables['date'] = format_date($listing->getCreatedTime());
  $username = array(
    '#theme' => 'username',
    '#account' => $listing->getOwner(),
    '#link_options' => array('attributes' => array('rel' => 'author')),
  );
  $variables['author_name'] = drupal_render($username);

  $variables['url'] = $listing->url('canonical', array(
    'language' => $listing->language(),
  ));
  $variables['label'] = $variables['elements']['title'];
  unset($variables['elements']['title']);
  $variables['page'] = $variables['view_mode'] == 'full' && drealty_listing_is_page($listing);

  // Helpful $content variable for templates.
  $variables += array('content' => array());
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  // Add article ARIA role.
  $variables['attributes']['role'] = 'article';

  // Gather listing classes.
  $variables['attributes']['class'][] = 'drealty-listing';
  $variables['attributes']['class'][] = drupal_html_class('listing--type-' . $listing->bundle());
  if ($listing->isPromoted()) {
    $variables['attributes']['class'][] = 'listing--promoted';
  }
  if ($listing->isSticky()) {
    $variables['attributes']['class'][] = 'listing--sticky';
  }
  if (!$listing->isPublished()) {
    $variables['attributes']['class'][] = 'listing--unpublished';
  }
  if ($variables['view_mode']) {
    $variables['attributes']['class'][] = drupal_html_class('listing--view-mode-' . $variables['view_mode']);
  }
}
