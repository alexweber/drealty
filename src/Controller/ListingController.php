<?php

/**
 * @file
 * Contains \Drupal\drealty\Controller\ListingController.
 */

namespace Drupal\drealty\Controller;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\drealty\ListingTypeInterface;
use Drupal\drealty\ListingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Listing routes.
 */
class ListingController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs a ListingController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   */
  public function __construct(DateFormatter $date_formatter) {
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('date.formatter'));
  }


  /**
   * Displays add content links for available content types.
   *
   * Redirects to drealty_listing/add/[type] if only one listing type is available.
   *
   * @return array
   *   A render array for a list of the listing types that can be added; however,
   *   if there is only one listing type defined for the site, the function
   *   redirects to the listing add page for that one listing type and does not return
   *   at all.
   */
  public function addPage() {
    $content = array();

    // Only use listing types the user has access to.
    foreach ($this->entityManager()->getStorage('drealty_listing_type')->loadMultiple() as $type) {
      if ($this->entityManager()->getAccessController('drealty_listing')->createAccess($type->id)) {
        $content[$type->id] = $type;
      }
    }

    // Bypass the drealty_listing/add listing if only one listing type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('drealty.listing_add', array('drealty_listing_type' => $type->id));
    }

    return array(
      '#theme' => 'drealty_listing_add_list',
      '#content' => $content,
    );
  }

  /**
   * Provides the listing submission form.
   *
   * @param \Drupal\drealty\ListingTypeInterface $listing_type
   *   The listing type entity for the listing.
   *
   * @return array
   *   A listing submission form.
   */
  public function add(ListingTypeInterface $listing_type) {
    $account = $this->currentUser();
    $langcode = $this->moduleHandler()->invoke('language', 'get_default_langcode', array('drealty_listing', $listing_type->id));

    $listing = $this->entityManager()->getStorage('drealty_listing')->create(array(
      'uid' => $account->id(),
      'name' => $account->getUsername() ?: '',
      'type' => $listing_type->id,
      'langcode' => $langcode ? $langcode : $this->languageManager()->getCurrentLanguage()->id,
    ));

    $form = $this->entityFormBuilder()->getForm($listing);

    return $form;
  }

  /**
   * Displays a listing revision.
   *
   * @param int $listing_revision
   *   The listing revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($listing_revision) {
    $listing = $this->entityManager()->getStorage('drealty_listing')->loadRevision($listing_revision);
    $listing_view_controller = new ListingViewController($this->entityManager);
    $page = $listing_view_controller->view($listing);
    unset($page['listings'][$listing->id()]['#cache']);
    return $page;
  }

  /**
   * Page title callback for a listing revision.
   *
   * @param int $listing_revision
   *   The listing revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($listing_revision) {
    $listing = $this->entityManager()->getStorage('drealty_listing')->loadRevision($listing_revision);
    return $this->t('Revision of %title from %date', array('%title' => $listing->label(), '%date' => format_date($listing->getRevisionCreationTime())));
  }

  /**
   * Generates an overview table of older revisions of a listing.
   *
   * @param \Drupal\drealty\ListingInterface $listing
   *   A listing object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ListingInterface $listing) {
    $account = $this->currentUser();
    $listing_storage = $this->entityManager()->getStorage('drealty_listing');
    $type = $listing->getType();

    $build = array();
    $build['#title'] = $this->t('Revisions for %title', array('%title' => $listing->label()));
    $header = array($this->t('Revision'), $this->t('Operations'));

    $revert_permission = (($account->hasPermission("revert $type revisions") || $account->hasPermission('revert all drealty listing revisions') || $account->hasPermission('administer drealty listings')) && $listing->access('update'));
    $delete_permission =  (($account->hasPermission("delete $type revisions") || $account->hasPermission('delete all drealty listing revisions') || $account->hasPermission('administer drealty listings')) && $listing->access('delete'));

    $rows = array();

    $vids = $listing_storage->revisionIds($listing);

    foreach (array_reverse($vids) as $vid) {
      if ($revision = $listing_storage->loadRevision($vid)) {
        $row = array();

        $revision_author = $revision->uid->entity;

        if ($vid == $listing->getRevisionId()) {
          $username = array(
            '#theme' => 'username',
            '#account' => $revision_author,
          );
          $row[] = array('data' => $this->t('!date by !username', array('!date' => $this->l($this->dateFormatter->format($revision->revision_timestamp->value, 'short'), 'entity.drealty_listing.canonical', array('drealty_listing' => $listing->id())), '!username' => drupal_render($username)))
            . (($revision->revision_log->value != '') ? '<p class="revision-log">' . Xss::filter($revision->revision_log->value) . '</p>' : ''),
            'class' => array('revision-current'));
          $row[] = array('data' => String::placeholder($this->t('current revision')), 'class' => array('revision-current'));
        }
        else {
          $username = array(
            '#theme' => 'username',
            '#account' => $revision_author,
          );
          $row[] = $this->t('!date by !username', array('!date' => $this->l($this->dateFormatter->format($revision->revision_timestamp->value, 'short'), 'drealty.listing_revision_show', array('drealty_listing' => $listing->id(), 'drealty_listing_revision' => $vid)), '!username' => drupal_render($username)))
            . (($revision->revision_log->value != '') ? '<p class="revision-log">' . Xss::filter($revision->revision_log->value) . '</p>' : '');

          if ($revert_permission) {
            $links['revert'] = array(
              'title' => $this->t('Revert'),
              'route_name' => 'drealty.listing_revision_revert_confirm',
              'route_parameters' => array('drealty_listing' => $listing->id(), 'drealty_listing_revision' => $vid),
            );
          }

          if ($delete_permission) {
            $links['delete'] = array(
              'title' => $this->t('Delete'),
              'route_name' => 'drealty.listing_revision_delete_confirm',
              'route_parameters' => array('drealty_listing' => $listing->id(), 'drealty_listing_revision' => $vid),
            );
          }

          $row[] = array(
            'data' => array(
              '#type' => 'operations',
              '#links' => $links,
            ),
          );
        }

        $rows[] = $row;
      }
    }

    $build['drealty_listing_revisions_table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    );

    return $build;
  }

  /**
   * The _title_callback for the drealty.listing_add route.
   *
   * @param \Drupal\drealty\ListingTypeInterface $listing_type
   *   The current listing.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(ListingTypeInterface $listing_type) {
    return $this->t('Add @name', array('@name' => $listing_type->label()));
  }

}
