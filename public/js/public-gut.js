/* globals jQuery: true */
/* globals CFCCGBSettings: true */
(function ($) {
  'use strict';

  $(document).ready(function () {

    var $elementSelector = $('.cc-has-condition');

    //get ids of added conditions
    function getCondIds() {
      //build a string of ids separated by a comma
      var $elements = $elementSelector.map(function(i, element) {
            return $(element).data('condition');
          })
          .get();
      $elements = $.uniqueSort($elements).join(',');
      return $elements;
    }

    //display element
    function displayBlock($element) {
      $element[0].style.display = null;
    }

    if (CFCCGBSettings.lazy_load && getCondIds()) {
      //get condition ids that should be displayed
      var params = {
        cond_ids: getCondIds(),
        action: 'cf_cc_gb_content',
        nonce: CFCCGBSettings.nonce,
        referrer: document.referrer
      };

      //fetch elements and replace with content as needed
      $.post(CFCCGBSettings.ajax_url + window.location.search, params, function (response)
      {
        if(response.data) {
          $elementSelector.each(function () {
            var $this = $(this);
            var condition = $this.data('condition').toString();
            if(-1 !== $.inArray(condition, response.data)) {
              displayBlock($this);
            }
          });
        }
      });
    }


    // add page visit
    $.post(CFCCGBSettings.ajax_url, {
      action: 'cf_cc_add_page_visit',
      nonce: CFCCGBSettings.nonce,
      page_url: CFCCGBSettings.page_url
    }, function () {});
  });
}(jQuery));
