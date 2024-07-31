;(function($){
    'use strict';

    if(!$) return;

    $.MileniaMPHBRoomTypeHelpers = {
        DOMReady: function() {
            this.listenAjaxRequestsForRerenderTabs();
        },
        outerResourcesReady: function() {

        }
    };

    $.MileniaMPHBRoomTypeHelpers.listenAjaxRequestsForRerenderTabs = function() {
        $(document).ajaxComplete(function() {
            setTimeout(function(){
                var $tabs = $('.milenia-tabs--integrated'),
                    $selects = $('.milenia-custom-select'), $toRemove;

                if($tabs.length) {
                    $tabs.data('tabs').updateContainer();
                }

                if($selects.length && $.fn.MadCustomSelect) {

                    $selects.each(function(index, element){
                        var $sel = $(element),
                            $toRemove = $sel.find('.milenia-selected-option,.milenia-options-list');

                        if($toRemove.length) {
                            $toRemove.remove();
                        }

                        $sel.data('customSelect').build();
                        $sel.data('customSelect').bindEvents();
                    });
                }
            }, 100);
        });
    };

    $(function() {
        $.MileniaMPHBRoomTypeHelpers.DOMReady();
    });

    $(window).on('load', function(event) {
        $.MileniaMPHBRoomTypeHelpers.outerResourcesReady();
    });
})(window.jQuery);
