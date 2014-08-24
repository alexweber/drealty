<?php

/**
 * @file
 * API Documentation.
 */

use Drupal\drealty\ListingInterface;
use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;

/**
 * @file
 * Hooks specific to the DRealty module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Perform listing validation before a listing is created or updated.
 *
 * This hook is invoked from ListingForm::validate(), after a user has
 * finished editing the listing and is previewing or submitting it. It is invoked
 * at the end of all the standard validation steps.
 *
 * To indicate a validation error, use form_set_error().
 *
 * Note: Changes made to the $listing object within your hook implementation will
 * have no effect.  The preferred method to change a listing's content is to use
 * hook_drealty_listing_presave() instead. If it is really necessary to change
 * the listing at the validate stage, you can use FormState::setValueForElement().
 *
 * @param \Drupal\drealty\ListingInterface $listing
 *   The listing being validated.
 * @param $form
 *   The form being used to edit the listing.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form.
 *
 * @ingroup entity_crud
 */
function hook_drealty_listing_validate(ListingInterface $listing, $form, FormStateInterface $form_state) {
  if (isset($listing->end) && isset($listing->start)) {
    if ($listing->start > $listing->end) {
      $form_state->setErrorByName('time', t('An event may not end before it starts.'));
    }
  }
}

/**
 * Act on a listing after validated form values have been copied to it.
 *
 * This hook is invoked when a listing form is submitted with either the "Save" or
 * "Preview" button, after form values have been copied to the form state's listing
 * object, but before the listing is saved or previewed. It is a chance for modules
 * to adjust the listing's properties from what they are simply after a copy from
 * $form_state['values']. This hook is intended for adjusting non-field-related
 * properties.
 *
 * @param \Drupal\drealty\ListingInterface $listing
 *   The listing entity being updated in response to a form submission.
 * @param $form
 *   The form being used to edit the listing.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form.
 *
 * @ingroup entity_crud
 */
function hook_drealty_listing_submit(ListingInterface $listing, $form, FormStateInterface $form_state) {
  // Decompose the selected menu parent option into 'menu_name' and 'parent', if
  // the form used the default parent selection widget.
  if (!empty($form_state['values']['menu']['parent'])) {
    list($listing->menu['menu_name'], $listing->menu['parent']) = explode(':', $form_state['values']['menu']['parent']);
  }
}

/**
 * Alter the links of a listing.
 *
 * @param array &$links
 *   A renderable array representing the listing links.
 * @param \Drupal\drealty\ListingInterface $entity
 *   The listing being rendered.
 * @param array &$context
 *   Various aspects of the context in which the listing links are going to be
 *   displayed, with the following keys:
 *   - 'view_mode': the view mode in which the listing is being viewed
 *   - 'langcode': the language in which the listing is being viewed
 *
 * @see \Drupal\drealty\ListingViewBuilder::renderLinks()
 * @see \Drupal\drealty\ListingViewBuilder::buildLinks()
 */
function hook_drealty_listing_links_alter(array &$links, ListingInterface $entity, array &$context) {
  $links['mymodule'] = array(
    '#theme' => 'links__drealty_listing__mymodule',
    '#attributes' => array('class' => array('links', 'inline')),
    '#links' => array(
      'drealty-listing-report' => array(
        'title' => t('Report'),
        'href' => "drealty_listing/{$entity->id()}/report",
        'html' => TRUE,
        'query' => array('token' => \Drupal::csrfToken()->get("drealty_listing/{$entity->id()}/report")),
      ),
    ),
  );
}

/**
 * @} End of "addtogroup hooks".
 */
