<?php

/**
 * @file
 * Contains \DrupalController\DrealtyListingController.
 */

namespace Drupal\drealty\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Drupal\drealty\DrealtyListingTypeInterface;
use Drupal\drealty\DrealtyListingInterface;

/**
 * Returns responses for Drealty Listing routes.
 */
class DrealtyListingController extends ControllerBase {

  /**
   * Displays add drealty listing links for available drealty listing types.
   *
   * Redirects to drealty-listing/add/[type] if only one drealty listing type is
   * available.
   *
   * @return array
   *   A render array for a list of the drealty listing types that can be added;
   *   however, if there is only one drealty listing type defined for the site,
   *   the function redirects to the drealty listing add page for that one
   *   drealty listing type and does not return at all.
   *
   * @see drealty_menu()
   */
  public function addPage() {
    $content = array();

    // Only use drealty listing types the user has access to.
    foreach ($this->entityManager()->getStorage('drealty_listing_type')->loadMultiple() as $type) {
      if ($this->entityManager()->getAccessController('drealty_listing')->createAccess($type->type)) {
        $content[$type->type] = $type;
      }
    }

    // Bypass the drealty-listing/add listing if only one drealty listing type
    // is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('drealty.listing_add', array('drealty_listing_type' => $type->type));
    }

    return array(
      '#theme' => 'drealty_listing_add_list',
      '#content' => $content,
    );
  }

  /**
   * Provides the drealty listing submission form.
   *
   * @param \Drupal\drealty\DrealtyListingTypeInterface $drealty_listing_type
   *   The drealty listing type entity for the drealty listing.
   *
   * @return array
   *   A drealty listing submission form.
   */
  public function add(DrealtyListingTypeInterface $drealty_listing_type) {
    $account = $this->currentUser();
    $langcode = $this->moduleHandler()->invoke('language', 'get_default_langcode', array('drealty_listing', $drealty_listing_type->type));

    $drealty_listing = $this->entityManager()->getStorage('drealty_listing')->create(array(
      'uid' => $account->id(),
      'name' => $account->getUsername() ?: '',
      'type' => $drealty_listing_type->type,
      'langcode' => $langcode ? $langcode : $this->languageManager()->getCurrentLanguage()->id,
    ));

    $form = $this->entityFormBuilder()->getForm($drealty_listing);

    return $form;
  }

  /**
   * Displays a drealty listing revision.
   *
   * @param int $drealty_listing_revision
   *   The drealty listing revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($drealty_listing_revision) {
    $drealty_listing = $this->entityManager()->getStorage('drealty_listing')->loadRevision($drealty_listing_revision);
    $page = $this->buildPage($drealty_listing);
    unset($page['drealty_listings'][$drealty_listing->id()]['#cache']);

    return $page;
  }

  /**
   * Page title callback for a drealty listing revision.
   *
   * @param int $drealty_listing_revision
   *   The drealty listing revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($drealty_listing_revision) {
    $drealty_listing = $this->entityManager()->getStorage('drealty_listing')->loadRevision($drealty_listing_revision);
    return $this->t('Revision of %title from %date', array('%title' => $drealty_listing->label(), '%date' => format_date($drealty_listing->getRevisionCreationTime())));
  }

  /**
   * @todo Remove drealty_listing_revision_overview().
   */
  public function revisionOverview(DrealtyListingInterface $drealty_listing) {
    module_load_include('pages.inc', 'drealty');
    return drealty_listing_revision_overview($drealty_listing);
  }

  /**
   * Displays a drealty listing.
   *
   * @param \Drupal\drealty\DrealtyListingInterface $drealty_listing
   *   The drealty listing we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function page(DrealtyListingInterface $drealty_listing) {
    $build = $this->buildPage($drealty_listing);

    foreach ($drealty_listing->uriRelationships() as $rel) {
      // Set the drealty listing path as the canonical URL to prevent duplicate content.
      $build['#attached']['drupal_add_html_head_link'][] = array(
        array(
          'rel' => $rel,
          'href' => $drealty_listing->url($rel),
        )
      , TRUE);

      if ($rel == 'canonical') {
        // Set the non-aliased canonical path as a default shortlink.
        $build['#attached']['drupal_add_html_head_link'][] = array(
          array(
            'rel' => 'shortlink',
            'href' => $drealty_listing->url($rel, array('alias' => TRUE)),
          )
        , TRUE);
      }
    }

    return $build;
  }

  /**
   * The _title_callback for the drealty.listing_view route.
   *
   * @param DrealtyListingInterface $drealty_listing
   *   The current drealty listing.
   *
   * @return string
   *   The page title.
   */
  public function pageTitle(DrealtyListingInterface $drealty_listing) {
    return String::checkPlain($this->entityManager()->getTranslationFromContext($drealty_listing)->label());
  }

  /**
   * Builds a drealty listing page render array.
   *
   * @param \Drupal\drealty\DrealtyListingInterface $drealty_listing
   *   The drealty listing we are displaying.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  protected function buildPage(DrealtyListingInterface $drealty_listing) {
    return array('drealty_listings' => $this->entityManager()->getViewBuilder('drealty_listing')->view($drealty_listing));
  }

  /**
   * The _title_callback for the drealty.listing_add route.
   *
   * @param \Drupal\drealty\DrealtyListingTypeInterface $drealty_listing_type
   *   The current drealty listing.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(DrealtyListingTypeInterface $drealty_listing_type) {
    return $this->t('Create @name', array('@name' => $drealty_listing_type->name));
  }

}
