<!-- - - - - - - - - - - - - - Instafeed - - - - - - - - - - - - - - - - -->
<script>
    (function($){
        'use strict';
        if(!$) return;
        var container, templates;

        $(function(){
            container = document.querySelectorAll('#${unique_id}-grid');
            if(!container || !window.InstafeedWrapper) return;

            if(window.appear) {
                appear({
                    elements: function(){
                        return document.querySelectorAll('#${unique_id}');
                    },
                    appear: function(element) {
                        var $el = $(element);
                        $el.addClass('milenia-visible').addClass($el.data('animation'));
                    },
                    reappear: false,
                    bounds: -200
                });
            }

            templates = {
                'gallery': '<div class="milenia-grid-item">\
                                <figure class="milenia-gallery-item milenia-gallery-item--with-thumb" style="background-image: url({{image}});" data-bg-image-src="{{image}}">\
                                    <a data-fancybox-gallery data-caption="{{caption}}" href="{{image}}" title="{{caption}}" class="milenia-gallery-item-link milenia-ln--independent">\
                                        <img src="{{image}}" alt="{{caption}}" class="milenia-d-none">\
                                    </a>\
                                    <figcaption class="milenia-gallery-item-caption">{{caption}}</figure>\
                                </figure>\
                            </div>',
                'simple-feed': '<div class="milenia-grid-item">\
                                    <div class="milenia-square-image" style="background-image: url({{image}});" data-bg-image-src="{{image}}">\
                                        <a class="milenia-ln--independent" target="_blank" rel="instagram" href="{{link}}" title="{{caption}}"></a>\
                                    </div>\
                                </div>',
                'snake': '<div class="milenia-grid-item">\
                                    <div class="milenia-square-image" style="background-image: url({{image}});" data-bg-image-src="{{image}}">\
                                        <a class="milenia-ln--independent" rel="instagram" target="_blank" href="{{link}}" title="{{caption}}"></a>\
                                    </div>\
                                </div>'
            };

            InstafeedWrapper.setUsersSecureOptions({'${user}': {userId: ${user_id},accessToken: '${user_access_token}',clientId: '${user_client_id}'}});

            InstafeedWrapper.init(container, {
                resolution: 'standard_resolution',
                template: templates['${feed_type}'],
                after: function() {
                    var $container = $('#' + this.options.target),
                        $fancyboxItems = $container.find('[data-fancybox-gallery]');

                    if($fancyboxItems.length && $.fn.fancybox) {
                        $fancyboxItems.attr('data-fancybox', this.options.target).fancybox({
                            animationEffect: "fade"
                        });
                    }

                    if(window.Milenia && window.Milenia.helper && window.Milenia.helpers.updateGlobalNiceScroll) {
                        window.Milenia.helpers.updateGlobalNiceScroll();
                    }

                    if(window.Milenia.LinkUnderliner) {
                        window.Milenia.LinkUnderliner.init($container.find('a'));
                    }
                }
            });

        });
    })(window.jQuery);
</script>
${widget_title}
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    <div id="${unique_id}-grid" data-get="user"
         data-user="${user}"
         data-limit="${items_per_page}"
         data-load-more-control="${load_more_id}"
         class="milenia-grid milenia-instafeed-gallery ${grid_classes}"></div>
</div>
${load_more}
<!-- - - - - - - - - - - - - - End of Instafeed - - - - - - - - - - - - - - - - -->
