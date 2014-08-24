<?php

/**
 * @file
 * Contains \Drupal\drealty\ConnectionForm.
 */

namespace Drupal\drealty;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class ConnectionForm,
 *
 * Form class for adding/editing DRealty Connection config entities.
 */
class ConnectionForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $connection = $this->entity;

    // Change page title for the edit operation
    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit connection: @name', array('@name' => $connection->name));
    }

    // The connection name.
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Connection Label'),
      '#maxlength' => 255,
      '#default_value' => $connection->name,
      '#description' => $this->t("The human-readable name of this connection."),
      '#required' => TRUE,
    );

    // The unique machine name of the connection.
    $form['id'] = array(
      '#type' => 'machine_name',
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#default_value' => $connection->id,
      '#disabled' => !$connection->isNew(),
      '#machine_name' => array(
        'source' => array('name'),
        'exists' => 'drealty_connection_load'
      ),
    );

    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs',
      '#attached' => array(
        // @TODO
//        'library' => array('drealty/drealty.connection_admin'),
      ),
    );

    $form['authentication'] = array(
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => t('Authentication'),
    );

    // URL.
    // @TODO add front-end validation with #pattern?
    $form['authentication']['url'] = array(
      '#title' => t('Login URL'),
      '#type' => 'url',
      '#description' => t('Login URL given to you by your RETS provider. i.e. (http://demo.crt.realtors.org:6103/rets/login)'),
      '#required' => TRUE,
      '#default_value' => $connection->url,
    );

    // Username.
    $form['authentication']['username'] = array(
      '#title' => t('Username'),
      '#type' => 'textfield',
      '#description' => t('Login username given to you by your RETS provider'),
      '#required' => TRUE,
      '#default_value' => $connection->username,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $connection = $this->entity;

    $status = $connection->save();

    if ($status) {
      // Setting the success message.
      drupal_set_message($this->t('Connection @label saved successfully.', array(
        '@label' => $connection->name,
      )));
    }
    else {
      drupal_set_message($this->t('There was an error saving the connection: @label.', array(
        '@label' => $connection->name,
      )));
    }

    $url = new Url('drealty.connection_list');
    $form_state['redirect'] = $url->toString();

  }

}
