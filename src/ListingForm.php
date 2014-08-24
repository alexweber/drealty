<?php

/**
 * @file
 * Definition of Drupal\drealty\ListingForm.
 */

namespace Drupal\drealty;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Component\Utility\String;

/**
 * Form controller for the listing edit forms.
 */
class ListingForm extends ContentEntityForm {

  /**
   * Default settings for this listing type.
   *
   * @var array
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    /** @var \Drupal\drealty\ListingInterface $listing */
    $listing = $this->entity;
    // Set up default values, if required.
    $type = entity_load('drealty_listing_type', $listing->bundle());
    $this->settings = $type->getModuleSettings('drealty');

    if (!$listing->isNew()) {
      $listing->date = format_date($listing->getCreatedTime(), 'custom', 'Y-m-d H:i:s O');
      // Remove the revision log message from the original listing entity.
      $listing->revision_log = NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\drealty\ListingInterface $listing */
    $listing = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit @type</em> @title', array('@type' => drealty_get_type_label($listing), '@title' => $listing->label()));
    }

    $current_user = \Drupal::currentUser();
    $user_config = \Drupal::config('user.settings');

    // Override the default CSS class name, since the user-defined listing type
    // name in 'TYPE-listing-form' potentially clashes with third-party class
    // names.
    $form['#attributes']['class'][0] = drupal_html_class('listing-' . $listing->getType() . '-form');

    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = array(
      '#type' => 'hidden',
      '#default_value' => $listing->getChangedTime(),
    );

    $language_configuration = \Drupal::moduleHandler()->invoke('language', 'get_default_configuration', array('drealty_listing', $listing->getType()));
    $form['langcode'] = array(
      '#title' => t('Language'),
      '#type' => 'language_select',
      '#default_value' => $listing->getUntranslated()->language()->id,
      '#languages' => LanguageInterface::STATE_ALL,
      '#access' => isset($language_configuration['language_show']) && $language_configuration['language_show'],
    );

    $form['advanced'] = array(
      '#type' => 'vertical_tabs',
      '#attributes' => array('class' => array('entity-meta')),
      '#weight' => 99,
    );

    // Add a revision log field if the "Create new revision" option is checked,
    // or if the current user has the ability to check that option.
    $form['revision_information'] = array(
      '#type' => 'details',
      '#group' => 'advanced',
      '#title' => t('Revision information'),
      // Open by default when "Create new revision" is checked.
      '#open' => $listing->isNewRevision(),
      '#attributes' => array(
        'class' => array('listing-form-revision-information'),
      ),
      '#attached' => array(
        'library' => array('drealty/drealty.listing'),
      ),
      '#weight' => 20,
      '#optional' => TRUE,
    );

    $form['revision'] = array(
      '#type' => 'checkbox',
      '#title' => t('Create new revision'),
      '#default_value' => !empty($this->settings['options']['revision']),
      '#access' => $listing->isNewRevision() || $current_user->hasPermission('administer drealty listings'),
      '#group' => 'revision_information',
    );

    $form['revision_log'] = array(
      '#type' => 'textarea',
      '#title' => t('Revision log message'),
      '#rows' => 4,
      '#default_value' => !empty($listing->revision_log->value) ? $listing->revision_log->value : '',
      '#description' => t('Briefly describe the changes you have made.'),
      '#states' => array(
        'visible' => array(
          ':input[name="revision"]' => array('checked' => TRUE),
        ),
      ),
      '#group' => 'revision_information',
      '#access' => $listing->isNewRevision() || $current_user->hasPermission('administer drealty listings'),
    );

    // Listing author information for administrators.
    $form['author'] = array(
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => array(
        'class' => array('listing-form-author'),
      ),
      '#attached' => array(
        'library' => array('drealty/drealty.listing'),
        'js' => array(
          array(
            'type' => 'setting',
            'data' => array('anonymous' => $user_config->get('anonymous')),
          ),
        ),
      ),
      '#weight' => 90,
      '#optional' => TRUE,
    );

    $form['uid'] = array(
      '#type' => 'textfield',
      '#title' => t('Authored by'),
      '#maxlength' => 60,
      '#autocomplete_route_name' => 'user.autocomplete',
      '#default_value' => $listing->getOwnerId()? $listing->getOwner()->getUsername() : '',
      '#weight' => -1,
      '#description' => t('Leave blank for %anonymous.', array('%anonymous' => $user_config->get('anonymous'))),
      '#group' => 'author',
      '#access' => $current_user->hasPermission('administer drealty listings'),
    );
    $form['created'] = array(
      '#type' => 'textfield',
      '#title' => t('Authored on'),
      '#maxlength' => 25,
      '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($listing->date) ? date_format(date_create($listing->date), 'Y-m-d H:i:s O') : format_date($listing->getCreatedTime(), 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($listing->date) ? date_format(date_create($listing->date), 'O') : format_date($listing->getCreatedTime(), 'custom', 'O'))),
      '#default_value' => !empty($listing->date) ? $listing->date : '',
      '#group' => 'author',
      '#access' => $current_user->hasPermission('administer drealty listings'),
    );

    // Listing options for administrators.
    $form['options'] = array(
      '#type' => 'details',
      '#title' => t('Promotion options'),
      '#group' => 'advanced',
      '#attributes' => array(
        'class' => array('listing-form-options'),
      ),
      '#attached' => array(
        'library' => array('drealty/drealty.listing'),
      ),
      '#weight' => 95,
      '#optional' => TRUE,
    );

    $form['promote'] = array(
      '#type' => 'checkbox',
      '#title' => t('Promoted to front page'),
      '#default_value' => $listing->isPromoted(),
      '#group' => 'options',
      '#access' => $current_user->hasPermission('administer drealty listings'),
    );

    $form['sticky'] = array(
      '#type' => 'checkbox',
      '#title' => t('Sticky at top of lists'),
      '#default_value' => $listing->isSticky(),
      '#group' => 'options',
      '#access' => $current_user->hasPermission('administer drealty listings'),
    );

    return parent::form($form, $form_state, $listing);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $listing = $this->entity;

    // @TODO confirm whether we need to do anything here.
//    $element['submit']['#access'] = TRUE;

    // If saving is an option, privileged users get dedicated form submit
    // buttons to adjust the publishing status while saving in one go.
    // @todo This adjustment makes it close to impossible for contributed
    //   modules to integrate with "the Save operation" of this form. Modules
    //   need a way to plug themselves into 1) the ::submit() step, and
    //   2) the ::save() step, both decoupled from the pressed form button.
    if ($element['submit']['#access'] && \Drupal::currentUser()->hasPermission('administer drealty listings')) {
      // isNew | prev status » default   & publish label             & unpublish label
      // 1     | 1           » publish   & Save and publish          & Save as unpublished
      // 1     | 0           » unpublish & Save and publish          & Save as unpublished
      // 0     | 1           » publish   & Save and keep published   & Save and unpublish
      // 0     | 0           » unpublish & Save and keep unpublished & Save and publish

      // Add a "Publish" button.
      $element['publish'] = $element['submit'];
      $element['publish']['#dropbutton'] = 'save';
      if ($listing->isNew()) {
        $element['publish']['#value'] = t('Save and publish');
      }
      else {
        $element['publish']['#value'] = $listing->isPublished() ? t('Save and keep published') : t('Save and publish');
      }
      $element['publish']['#weight'] = 0;
      array_unshift($element['publish']['#submit'], array($this, 'publish'));

      // Add a "Unpublish" button.
      $element['unpublish'] = $element['submit'];
      $element['unpublish']['#dropbutton'] = 'save';
      if ($listing->isNew()) {
        $element['unpublish']['#value'] = t('Save as unpublished');
      }
      else {
        $element['unpublish']['#value'] = !$listing->isPublished() ? t('Save and keep unpublished') : t('Save and unpublish');
      }
      $element['unpublish']['#weight'] = 10;
      array_unshift($element['unpublish']['#submit'], array($this, 'unpublish'));

      // If already published, the 'publish' button is primary.
      if ($listing->isPublished()) {
        unset($element['unpublish']['#button_type']);
      }
      // Otherwise, the 'unpublish' button is primary and should come first.
      else {
        unset($element['publish']['#button_type']);
        $element['unpublish']['#weight'] = -10;
      }

      // Remove the "Save" button.
      $element['submit']['#access'] = FALSE;
    }

    $element['delete']['#access'] = $listing->access('delete');
    $element['delete']['#weight'] = 100;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, FormStateInterface $form_state) {
    $listing = $this->buildEntity($form, $form_state);

    if ($listing->id() && (drealty_listing_last_changed($listing->id(), $this->getFormLangcode($form_state)) > $listing->getChangedTime())) {
      $form_state->setErrorByName('changed', $this->t('The content on this page has either been modified by another user, or you have already submitted modifications using this form. As a result, your changes cannot be saved.'));
    }

    // Validate the "authored by" field.
    if (!empty($form_state['values']['uid']) && !user_load_by_name($form_state['values']['uid'])) {
      // The use of empty() is mandatory in the context of usernames
      // as the empty string denotes the anonymous user. In case we
      // are dealing with an anonymous user we set the user ID to 0.
      $form_state->setErrorByName('uid', $this->t('The username %name does not exist.', array('%name' => $form_state['values']['uid'])));
    }

    // Validate the "authored on" field.
    // The date element contains the date object.
    $date = $listing->date instanceof DrupalDateTime ? $listing->date : new DrupalDateTime($listing->date);
    if ($date->hasErrors()) {
      $form_state->setErrorByName('date', $this->t('You have to specify a valid date.'));
    }

    // Invoke hook_drealty_listing_validate() for validation needed by modules.
    // Can't use \Drupal::moduleHandler()->invokeAll(), because $form_state must
    // be receivable by reference.
    foreach (\Drupal::moduleHandler()->getImplementations('drealty_listing_validate') as $module) {
      $function = $module . '_drealty_listing_validate';
      $function($listing, $form, $form_state);
    }

    parent::validate($form, $form_state);
  }

  /**
   * Updates the listing object by processing the submitted values.
   *
   * This function can be called by a "Next" button of a wizard to update the
   * form state's entity with the current step's values before proceeding to the
   * next step.
   */
  public function submit(array $form, FormStateInterface $form_state) {
    // Build the listing object from the submitted values.
    $listing = parent::submit($form, $form_state);

    // Save as a new revision if requested to do so.
    if (!empty($form_state['values']['revision']) && $form_state['values']['revision'] != FALSE) {
      $listing->setNewRevision();
      // If a new revision is created, save the current user as revision author.
      $listing->setRevisionCreationTime(REQUEST_TIME);
      $listing->setRevisionAuthorId(\Drupal::currentUser()->id());
    }
    else {
      $listing->setNewRevision(FALSE);
    }

    $listing->validated = TRUE;
    foreach (\Drupal::moduleHandler()->getImplementations('drealty_listing_submit') as $module) {
      $function = $module . '_drealty_listing_submit';
      $function($listing, $form, $form_state);
    }

    return $listing;
  }

  /**
   * Form submission handler for the 'publish' action.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function publish(array $form, FormStateInterface $form_state) {
    $listing = $this->entity;
    $listing->setPublished(TRUE);
    return $listing;
  }

  /**
   * Form submission handler for the 'unpublish' action.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function unpublish(array $form, FormStateInterface $form_state) {
    $listing = $this->entity;
    $listing->setPublished(FALSE);
    return $listing;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\drealty\ListingInterface $entity */
    $entity = parent::buildEntity($form, $form_state);
    // A user might assign the listing author by entering a user name in the listing
    // form, which we then need to translate to a user ID.
    if (!empty($form_state['values']['uid']) && $account = user_load_by_name($form_state['values']['uid'])) {
      $entity->setOwnerId($account->id());
    }
    else {
      $entity->setOwnerId(0);
    }

    if (!empty($form_state['values']['created']) && $form_state['values']['created'] instanceOf DrupalDateTime) {
      $entity->setCreatedTime($form_state['values']['created']->getTimestamp());
    }
    else {
      $entity->setCreatedTime(REQUEST_TIME);
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $listing = $this->entity;
    $insert = $listing->isNew();
    $listing->save();
    $listing_link = l(t('View'), 'drealty_listing/' . $listing->id());
    $context = array('@type' => $listing->getType(), '%title' => $listing->label(), 'link' => $listing_link);
    $t_args = array('@type' => drealty_get_type_label($listing), '%title' => $listing->label());

    if ($insert) {
      $this->logger('content')->notice('@type: added %title.', $context);
      drupal_set_message(t('@type %title has been created.', $t_args));
    }
    else {
      $this->logger('content')->notice('@type: updated %title.', $context);
      drupal_set_message(t('@type %title has been updated.', $t_args));
    }

    if ($listing->id()) {
      $form_state['values']['id'] = $listing->id();
      $form_state['id'] = $listing->id();
      if ($listing->access('view')) {
        $form_state->setRedirect(
          'drealty.drealty_listing_view',
          array('drealty_listing' => $listing->id())
        );
      }
      else {
        $form_state->setRedirect('<front>');
      }
    }
    else {
      // In the unlikely case something went wrong on save, the listing will be
      // rebuilt and listing form redisplayed.
      drupal_set_message(t('The post could not be saved.'), 'error');
      $form_state['rebuild'] = TRUE;
    }
  }

}
