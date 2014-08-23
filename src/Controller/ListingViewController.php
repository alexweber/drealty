<?php

/**
 * @file
 * Contains \Drupal\drealty\Controller\ListingViewController.
 */

namespace Drupal\drealty\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Controller\EntityViewController;

/**
 * Defines a controller to render a single listing.
 */
class ListingViewController extends EntityViewController {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $listing, $view_mode = 'full', $langcode = NULL) {
    $build = array('listings' => parent::view($listing));

    $build['#title'] = $build['listings']['#title'];
    unset($build['listings']['#title']);

    foreach ($listing->uriRelationships() as $rel) {
      // Set the listing path as the canonical URL to prevent duplicate content.
      $build['#attached']['drupal_add_html_head_link'][] = array(
        array(
          'rel' => $rel,
          'href' => $listing->url($rel),
        ),
        TRUE,
      );

      if ($rel == 'canonical') {
        // Set the non-aliased canonical path as a default shortlink.
        $build['#attached']['drupal_add_html_head_link'][] = array(
          array(
            'rel' => 'shortlink',
            'href' => $listing->url($rel, array('alias' => TRUE)),
          ),
          TRUE,
        );
      }
    }

    return $build;
  }

  /**
   * The _title_callback for the page that renders a single listing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $listing
   *   The current listing.
   *
   * @return string
   *   The page title.
   */
  public function title(EntityInterface $listing) {
    return String::checkPlain($this->entityManager->getTranslationFromContext($listing)->label());
  }

}
