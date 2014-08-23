<?php

/**
 * @file
 * Definition of Drupal\drealty\ListingViewBuilder.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for listings.
 */
class ListingViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode, $langcode = NULL) {
    /** @var \Drupal\drealty\ListingInterface[] $entities */
    if (empty($entities)) {
      return;
    }

    // Attach user account.
    user_attach_accounts($build, $entities);

    parent::buildComponents($build, $entities, $displays, $view_mode, $langcode);

    foreach ($entities as $id => $entity) {
      $bundle = $entity->bundle();
      $display = $displays[$bundle];

      $callback = '\Drupal\drealty\ListingViewBuilder::renderLinks';
      $context = array(
        'listing_entity_id' => $entity->id(),
        'view_mode' => $view_mode,
        'langcode' => $langcode,
      );
      $placeholder = drupal_render_cache_generate_placeholder($callback, $context);
      $build[$id]['links'] = array(
        '#post_render_cache' => array(
          $callback => array(
            $context,
          ),
        ),
        '#markup' => $placeholder,
      );


      // Add Language field text element to listing render array.
      if ($display->getComponent('langcode')) {
        $build[$id]['langcode'] = array(
          '#type' => 'item',
          '#title' => t('Language'),
          '#markup' => $entity->language()->name,
          '#prefix' => '<div id="field-language-display">',
          '#suffix' => '</div>'
        );
      }
    }
  }

  /**
   * #post_render_cache callback; replaces the placeholder with listing links.
   *
   * Renders the links on a listing.
   *
   * @param array $element
   *   The renderable array that contains the to be replaced placeholder.
   * @param array $context
   *   An array with the following keys:
   *   - listing_entity_id: a listing entity ID
   *   - view_mode: the view mode in which the listing entity is being viewed
   *   - langcode: in which language the listing entity is being viewed
   *   - in_preview: whether the listing is currently being previewed
   *
   * @return array
   *   A renderable array representing the listing links.
   */
  public static function renderLinks(array $element, array $context) {
    $callback = '\Drupal\drealty\ListingViewBuilder::renderLinks';
    $placeholder = drupal_render_cache_generate_placeholder($callback, $context);

    $links = array(
      '#theme' => 'links__drealty_listing',
      '#pre_render' => array('drupal_pre_render_links'),
      '#attributes' => array('class' => array('links', 'inline')),
    );

    $entity = entity_load('drealty_listing', $context['listing_entity_id'])->getTranslation($context['langcode']);
    $links['drealty_listing'] = self::buildLinks($entity, $context['view_mode']);

    // Allow other modules to alter the listing links.
    $hook_context = array(
      'view_mode' => $context['view_mode'],
      'langcode' => $context['langcode'],
    );
    \Drupal::moduleHandler()->alter('drealty_listing_links', $links, $entity, $hook_context);

    $markup = drupal_render($links);
    $element['#markup'] = str_replace($placeholder, $markup, $element['#markup']);

    return $element;
  }

  /**
   * Build the default links (View details) for a listing.
   *
   * @param \Drupal\drealty\ListingInterface $entity
   *   The listing object.
   * @param string $view_mode
   *   A view mode identifier.
   *
   * @return array
   *   An array that can be processed by drupal_pre_render_links().
   */
  protected static function buildLinks(ListingInterface $entity, $view_mode) {
    $links = array();

    // Always display a read more link on teasers because we have no way
    // to know when a teaser view is different than a full view.
    if ($view_mode == 'teaser') {
      $listing_title_stripped = strip_tags($entity->label());
      $links['listing-view'] = array(
        'title' => t('View details<span class="visually-hidden"> about @title</span>', array(
          '@title' => $listing_title_stripped,
        )),
        'href' => 'drealty_listing/' . $entity->id(),
        'language' => $entity->language(),
        'html' => TRUE,
        'attributes' => array(
          'rel' => 'tag',
          'title' => $listing_title_stripped,
        ),
      );
    }

    return array(
      '#theme' => 'links__drealty__drealty_listing',
      '#links' => $links,
      '#attributes' => array('class' => array('links', 'inline')),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode, $langcode = NULL) {
    /** @var \Drupal\drealty\ListingInterface $entity */
    parent::alterBuild($build, $entity, $display, $view_mode, $langcode);
    if ($entity->id()) {
      $build['#contextual_links']['drealty_listing'] = array(
        'route_parameters' =>array('drealty_listing' => $entity->id()),
        'metadata' => array('changed' => $entity->getChangedTime()),
      );
    }
  }

}
