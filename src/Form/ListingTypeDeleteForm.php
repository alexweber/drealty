<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ListingTypeDeleteConfirm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for listing type deletion.
 */
class ListingTypeDeleteForm extends EntityConfirmFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new ListingTypeDeleteConfirm object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the listing type %type?', array('%type' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('drealty.listing_type_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $num_listings = $this->database->query("SELECT COUNT(*) FROM {drealty_listing} WHERE type = :type", array(':type' => $this->entity->id()))->fetchField();
    if ($num_listings) {
      $caption = '<p>' . format_plural($num_listings, '%type is used by 1 listing on your site. You can not remove this listing type until you have removed all of the %type listings.', '%type is used by @count listings on your site. You may not remove %type until you have removed all of the %type listings.', array('%type' => $this->entity->label())) . '</p>';
      $form['#title'] = $this->getQuestion();
      $form['description'] = array('#markup' => $caption);
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    $this->entity->delete();
    $t_args = array('%name' => $this->entity->label());
    drupal_set_message(t('The listing type %name has been deleted.', $t_args));
    $this->logger('drealty')->notice('Deleted listing type %name.', $t_args);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
