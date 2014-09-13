<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingPermissions.
 */

namespace Drupal\drealty;

use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\drealty\Entity\ListingType;

/**
 * Defines a class containing permission callbacks.
 */
class ListingPermissions {

  use StringTranslationTrait;
  use UrlGeneratorTrait;

  /**
   * Returns an array of content permissions.
   *
   * @return array
   */
  public function listingPermissions() {
    return array(
      'access drealty listing overview' => array(
        'title' => t('Access the Listing overview page'),
        'description' => t('Get an overview of <a href="!url">all listings</a>.', array('!url' => \Drupal::url('drealty.listing_list'))),
      ),
    );
  }

  /**
   * Returns an array of node type permissions.
   *
   * @return array
   */
  public function listingTypePermissions() {
    $perms = array();
    // Generate node permissions for all node types.
    foreach (ListingType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Builds a standard list of listing permissions for a given type.
   *
   * @param \Drupal\drealty\Entity\ListingType $type
   *   The machine name of the listing type.
   *
   * @return array
   *   An array of permission names and descriptions.
   */
  protected function buildPermissions(ListingType $type) {
    $type_id = $type->id();
    $type_params = array('%type_name' => $type->label());

    return array(
      "create {$type_id} drealty_listing" => array(
        'title' => t('%type_name: Create new listings', $type_params),
      ),
      "edit own {$type_id} drealty_listing" => array(
        'title' => t('%type_name: Edit own listings', $type_params),
      ),
      "edit any {$type_id} drealty_listing" => array(
        'title' => t('%type_name: Edit any listing', $type_params),
      ),
      "delete own {$type_id} drealty_listing" => array(
        'title' => t('%type_name: Delete own listings', $type_params),
      ),
      "delete any {$type_id} drealty_listing" => array(
        'title' => t('%type_name: Delete any listing', $type_params),
      ),
      "view {$type_id} drealty_listing revisions" => array(
        'title' => t('%type_name: View listing revisions', $type_params),
      ),
      "revert {$type_id} drealty_listing revisions" => array(
        'title' => t('%type_name: Revert listing revisions', $type_params),
        'description' => t('Role requires permission <em>view revisions</em> and <em>edit rights</em> for listings in question, or <em>administer drealty listings</em>.'),
      ),
      "delete {$type_id} drealty_listing revisions" => array(
        'title' => t('%type_name: Delete listing revisions', $type_params),
        'description' => t('Role requires permission to <em>view revisions</em> and <em>delete rights</em> for listings in question, or <em>administer drealty listings</em>.'),
      ),
    );
  }

}
