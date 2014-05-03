<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\DrealtyListingTypeDeleteConfirm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for drealty listing type deletion.
 */
class DrealtyListingTypeDeleteConfirm extends EntityConfirmFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new DrealtyListingTypeDeleteConfirm object.
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
    return t('Are you sure you want to delete the drealty listing type %type?', array('%type' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'drealty.type_overview',
    );
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
  public function buildForm(array $form, array &$form_state) {
    $num_listings = $this->database->query("SELECT COUNT(*) FROM {drealty_listing} WHERE type = :type", array(':type' => $this->entity->id()))->fetchField();
    if ($num_listings) {
      $caption = '<p>' . format_plural($num_listings, '%type is used by 1 drealty listing on your site. You can not remove this drealty listing type until you have removed all of the %type drealty listings.', '%type is used by @count drealty listings on your site. You may not remove %type until you have removed all of the %type drealty listings.', array('%type' => $this->entity->label())) . '</p>';
      $form['#title'] = $this->getQuestion();
      $form['description'] = array('#markup' => $caption);
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    $t_args = array('%name' => $this->entity->label());
    drupal_set_message(t('The drealty listing type %name has been deleted.', $t_args));
    watchdog('drealty', 'Deleted drealty listing type %name.', $t_args, WATCHDOG_NOTICE);

    $form_state['redirect_route']['route_name'] = 'drealty.type_overview';
  }

}
