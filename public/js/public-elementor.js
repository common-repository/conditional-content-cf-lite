/* globals jQuery: true */
/* globals CFCCElementorSettings: true */
(function ($) {
  'use strict';

  $(function () {
    function loadPageContent(data) {
      var args = $.extend(
        {
          action: 'cf_cc_load_content',
          nonce: CFCCElementorSettings.nonce,
        },
        data
      );
      var postId;
      var key = $('#cf-cc-q').val();

      var matches = $('body').attr('class').match(/elementor-page-([0-9]+)/);

      if (matches && matches.length > 1) {
        postId = parseInt(matches[1], 10);
      }

      if (typeof postId === 'undefined') {
        return;
      }

      args['post_id'] = postId;
      args['referrer'] = document.referrer;

      if (typeof key !== 'undefined') {
        args.key = key;
      }

      $.post(CFCCElementorSettings.ajax_url + window.location.search, args, function (response) {
        if (response.data.elements) {
          for (var elementId in response.data.elements) {
            var $placeholder = $('#cfcc-e-hid-element-' + elementId);
            if ($placeholder.length > 0) {
              $placeholder.replaceWith(function () {
                return response.data.elements[elementId];
              });
            }

          }
        }

        window.elementorFrontend.init();
      });
    }

    if (CFCCElementorSettings.lazy_load) {
      loadPageContent({});
    }
  });
}(jQuery));