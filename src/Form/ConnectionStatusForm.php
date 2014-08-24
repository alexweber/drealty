<?php

/**
 * @file
 * Contains \Drupal\drealty\Form\ConnectionDeleteForm.
 */

namespace Drupal\drealty\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form that handles the removal of DRealty Connection entities.
 */
class ConnectionStatusForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $connection = $this->entity;

    var_dump($connection);die;

    return $form;
  }

}
