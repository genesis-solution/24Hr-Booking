;(function($){
    'use strict';

    if(!$) return;

    $(function(){
        if($.fn.ajaxComplete && window.MileniaIsotopeWrapper && window.Milenia) {
            $(document).ajaxComplete(function(event){
                var $body = $('body'),
                    $isotops = $body.find('.milenia-grid--isotope'),
                    $dynamicBg = $body.find('[data-bg-image-src]');

                if($isotops.length) {
                   $isotops.each(function(index, element) {
                       var $el = $(element),
                           IsotopeInstance;

                       if(!$el.data('isotope')) {
                           MileniaIsotopeWrapper.init($el, {
                               itemSelector: '.milenia-grid-item',
                               transitionDuration: window.Milenia.ANIMATIONDURATION
                           });

                           setTimeout(function() {
                               IsotopeInstance = $el.data('isotope');
                               if(IsotopeInstance) IsotopeInstance.layout();
                           }, 100);
                       }
                   });
                }
                if($dynamicBg.length) {
                    setTimeout(function(){
                        window.Milenia.helpers.dynamicBgImage($dynamicBg);
                    }, 50);
                }

                if(window.Milenia.LinkUnderliner) {
                    window.Milenia.LinkUnderliner.init($('body').find('a'));
                }
            });
        }
    });

})(window.jQuery);
