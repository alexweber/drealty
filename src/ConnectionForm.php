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

    // RETS Version.
    $versions = drealty_rets_versions();
    $form['rets'] = array(
      '#type' => 'select',
      '#title' => t('RETS Version'),
      '#required' => TRUE,
      '#options' => array_combine($versions, $versions),
      '#default_value' => isset($connection->rets) ? $connection->rets : '1.5',
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

    // Password.
    $form['authentication']['password'] = array(
      '#title' => t('Password'),
      '#type' => 'textfield',
      '#description' => t('Login password given to you by your RETS provider'),
      '#required' => TRUE,
      '#default_value' => $connection->password,
    );

    $form['agent'] = array(
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => t('User Agent'),
    );

    // Agent String.
    $form['agent']['ua_string'] = array(
      '#title' => t('User Agent String'),
      '#type' => 'textfield',
      '#description' => t('A User Agent String.'),
      '#required' => TRUE,
      '#default_value' => isset($connection->ua_string) ? $connection->ua_string : 'dRealty/1.0',
    );

    // Agent Password.
    $form['agent']['ua_password'] = array(
      '#title' => t('User Agent Password'),
      '#type' => 'textfield',
      '#description' => t('Leave blank if you don\'t have one.'),
      '#default_value' => $connection->ua_password,
    );

    $form['advanced'] = array(
      '#group' => 'additional_settings',
      '#type' => 'details',
      '#title' => t('Advanced'),
    );

    $form['advanced']['use_interealty_auth'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Interealty Authentication'),
      '#required' => TRUE,
      '#default_value' => isset($connection->use_interealty_auth) ? $connection->use_interealty_auth : FALSE,
    );

    $form['advanced']['force_basic_auth'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force Basic Authentication'),
      '#required' => TRUE,
      '#default_value' => isset($connection->force_basic_auth) ? $connection->force_basic_auth : FALSE,
    );

    $form['advanced']['use_compression'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Compression'),
      '#required' => TRUE,
      '#default_value' => isset($connection->use_compression) ? $connection->use_compression : FALSE,
    );

    $form['advanced']['disable_encoding_fix'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable Encoding Fix'),
      '#required' => TRUE,
      '#default_value' => isset($connection->disable_encoding_fix) ? $connection->disable_encoding_fix : FALSE,
    );

    $form['advanced']['debug_mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable Debug Mode'),
      '#description' => t('Writes phRets debug messages to drealty_debug_log.txt in the private files folder.'),
      '#default_value' => isset($connection->debug_mode) ? $connection->debug_mode : FALSE,
    );

    $form['advanced']['nomap_mode'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable "No mapping" Mode'),
      '#description' => t('This connection will not create or update items and will not be available in field mappings. It may still act on items imported by other connections which have since expired.'),
      '#default_value' => isset($connection->nomap_mode) ? $connection->nomap_mode: FALSE,
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
