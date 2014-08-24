/**
 * @file
 * Javascript for the listing type edit form.
 */

(function ($) {

  "use strict";

  Drupal.behaviors.listingType = {
    attach: function (context) {
      var $context = $(context);
      // Provide the vertical tab summaries.
      $context.find('#edit-workflow').drupalSetSummary(function (context) {
        var vals = [];
        $(context).find("input[name^='settings[drealty][options']:checked").parent().each(function () {
          vals.push(Drupal.checkPlain($(this).text()));
        });
        if (!$(context).find('#edit-settings-drealty-options-status').is(':checked')) {
          vals.unshift(Drupal.t('Not published'));
        }
        return vals.join(', ');
      });
      $('#edit-language', context).drupalSetSummary(function (context) {
        var vals = [];

        vals.push($(".form-item-language-configuration-langcode select option:selected", context).text());

        $('input:checked', context).next('label').each(function () {
          vals.push(Drupal.checkPlain($(this).text()));
        });

        return vals.join(', ');
      });
    }
  };

})(jQuery);
