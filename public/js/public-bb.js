/* globals jQuery: true */
/* globals CFCCBBSettings: true */
(function ($) {
  'use strict';

  $(function () {

    //request nodes
    function loadContent() {
      //declare vars
      var params = {
        post_id: CFCCBBSettings.post_id,
        node_ids: getNodes(),
        action: 'cf_cc_bb_content',
        nonce: CFCCBBSettings.nonce,
        referrer: document.referrer
      };

      //fetch and cast to json
      var response = $.post(CFCCBBSettings.ajax_url + window.location.search, params, function (response) {
        if(response.data) {
          setNodes(response.data);
        }
      });
    }

    //get ids of elements to be replaced
    function getNodes() {
      //build a string of node ids separated by a comma
      return $('.cf-cc-bb-node-placeholder').map(function(i, element) {
            return $(element).data('nodeId');
          })
          .get()
          .join(',');
    }

    //replace placeholder elements with retrieved data
    function setNodes(data) {
      $.map(data, function (element, i) {
        //replace placeholder element with retrieved node
        $("div[data-node-id=" + i + "]").replaceWith(element['html']);
        //inject js asset
        var script = document.createElement( 'script' );
        script.type = 'text/javascript';
        script.innerHTML = element['js'];
        $("body").append(script);
      });
    }

    //main function call
    if (CFCCBBSettings.lazy_load) {
      loadContent();
    }

  });
}(jQuery));
