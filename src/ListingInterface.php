<?php

/**
 * @file
 * Contains \Drupal\drealty\ListingInterface.
 */

namespace Drupal\drealty;

use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a listing entity.
 */
interface ListingInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Returns the listing type.
   *
   * @return string
   *   The listing type.
   */
  public function getType();

  /**
   * Returns the listing title.
   *
   * @return string
   *   Title of the listing.
   */
  public function getTitle();

  /**
   * Sets the listing title.
   *
   * @param string $title
   *   The listing title.
   *
   * @return \Drupal\drealty\ListingInterface
   *   The called listing entity.
   */
  public function setTitle($title);

  /**
   * Returns the listing creation timestamp.
   *
   * @return int
   *   Creation timestamp of the listing.
   */
  public function getCreatedTime();

  /**
   * Sets the listing creation timestamp.
   *
   * @param int $timestamp
   *   The listing creation timestamp.
   *
   * @return \Drupal\drealty\ListingInterface
   *   The called listing entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the listing featured status.
   *
   * @return bool
   *   TRUE if the listing is featured.
   */
  public function isFeatured();

  /**
   * Sets the listing featured status.
   *
   * @param bool $featured
   *   TRUE to set this listing to featured, FALSE to set it to not featured.
   *
   * @return \Drupal\drealty\ListingInterface
   *   The called listing entity.
   */
  public function setFeatured($featured);

  /**
   * Returns the listing published status indicator.
   *
   * Unpublished listings are only visible to their authors and to administrators.
   *
   * @return bool
   *   TRUE if the listing is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a listing.
   *
   * @param bool $published
   *   TRUE to set this listing to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\drealty\ListingInterface
   *   The called listing entity.
   */
  public function setPublished($published);

  /**
   * Returns the listing revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the listing revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\drealty\ListingInterface
   *   The called listing entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Returns the listing revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionAuthor();

  /**
   * Sets the listing revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\drealty\ListingInterface
   *   The called listing entity.
   */
  public function setRevisionAuthorId($uid);

  /**
   * Prepares the langcode for a listing.
   *
   * @return string
   *   The langcode for this listing.
   */
  public function prepareLangcode();

}
