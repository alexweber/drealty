<?php

/**
 * @file
 * Definition of Drupal\drealty\DrealtyListingViewBuilder.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for drealty listings.
 */
class DrealtyListingViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildContent(array $entities, array $displays, $view_mode, $langcode = NULL) {
    $return = array();
    if (empty($entities)) {
      return $return;
    }

    // Attach user account.
    user_attach_accounts($entities);

    parent::buildContent($entities, $displays, $view_mode, $langcode);

    foreach ($entities as $entity) {
      $bundle = $entity->bundle();
      $display = $displays[$bundle];

      $entity->content['links'] = array(
        '#type' => 'render_cache_placeholder',
        '#callback' => '\Drupal\drealty\DrealtyListingViewBuilder::renderLinks',
        '#context' => array(
          'drealty_listing_entity_id' => $entity->id(),
          'view_mode' => $view_mode,
          'langcode' => $langcode,
        ),
      );

      // Add Language field text element to drealty listing render array.
      if ($display->getComponent('langcode')) {
        $entity->content['langcode'] = array(
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
   * #post_render_cache callback; replaces the placeholder with drealty listing
   * links.
   *
   * Renders the links on a drealty listing.
   *
   * @param array $context
   *   An array with the following keys:
   *   - drealty_listing_entity_id: a drealty listing entity ID
   *   - view_mode: the view mode in which the drealty listing entity is being viewed
   *   - langcode: in which language the drealty listing entity is being viewed
   *
   * @return array
   *   A renderable array representing the drealty listing links.
   */
  public static function renderLinks(array $context) {
    $links = array(
      '#theme' => 'links__drealty_listing',
      '#pre_render' => array('drupal_pre_render_links'),
      '#attributes' => array('class' => array('links', 'inline')),
    );

    return $links;
  }

  /**
   * Build the default links (Read more) for a drealty listing.
   *
   * @param \Drupal\drealty\DrealtyListingInterface $entity
   *   The drealty listing object.
   * @param string $view_mode
   *   A view mode identifier.
   *
   * @return array
   *   An array that can be processed by drupal_pre_render_links().
   */
  protected static function buildLinks(DrealtyListingInterface $entity, $view_mode) {
    $links = array();

    // Always display a read more link on teasers because we have no way
    // to know when a teaser view is different than a full view.
    if ($view_mode == 'teaser') {
      $drealty_listing_title_stripped = strip_tags($entity->label());
      $links['drealty-listing-readmore'] = array(
        'title' => t('Read more<span class="visually-hidden"> about @title</span>', array(
          '@title' => $drealty_listing_title_stripped,
        )),
        'href' => 'drealty-listing/' . $entity->id(),
        'language' => $entity->language(),
        'html' => TRUE,
        'attributes' => array(
          'rel' => 'tag',
          'title' => $drealty_listing_title_stripped,
        ),
      );
    }

    return array(
      '#theme' => 'links__drealty_listing__drealty',
      '#links' => $links,
      '#attributes' => array('class' => array('links', 'inline')),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode, $langcode = NULL) {
    /** @var \Drupal\drealty\DrealtyListingInterface $entity */
    parent::alterBuild($build, $entity, $display, $view_mode, $langcode);
    if ($entity->id()) {
      $build['#contextual_links']['drealty_listing'] = array(
        'route_parameters' =>array('drealty_listing' => $entity->id()),
        'metadata' => array('changed' => $entity->getChangedTime()),
      );
    }
  }

}
