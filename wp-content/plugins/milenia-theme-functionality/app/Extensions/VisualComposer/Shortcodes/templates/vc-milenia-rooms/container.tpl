<!--================ Room types ================-->
<script>
    (function($){
        'use strict';

        var $container, $slideshow, $bgImages, $carousel;

        if(!$) return;

        $(function(){
            if(window.DOMDfd) {
                window.DOMDfd.done(function(){
                    // Check all necessary modules have been included
                    if(!window.Milenia || !$.fn.jQueryImagesLoaded) return;

                    $container = $('#${unique_id}');

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

                    if(!$container.length) return;

                    if(window.Milenia.helpers && window.Milenia.helpers.owlSettings) {
                        $slideshow = $container.find('.milenia-simple-slideshow');

                        if($slideshow.length) {
                            $slideshow.jQueryImagesLoaded().then(function() {
                                $slideshow.owlCarousel(window.Milenia.helpers.owlSettings({
                                    loop: true,
                                    margin: 1
                                }));
                            });
                        }
                    }

                    if(window.Milenia.helpers && window.Milenia.helpers.gridOwl) {
                        $carousel = $container.find('.milenia-grid--shortcode.owl-carousel');
                        if($carousel.length) {
                            window.Milenia.helpers.gridOwl.add($carousel);
                        }
                    }

                    if(window.Milenia.helpers && window.Milenia.helpers.dynamicBgImage) {
                        $bgImages = $container.find('[data-bg-image-src]');
                        if($bgImages.length) Milenia.helpers.dynamicBgImage($bgImages);
                    }

                    if(window.Milenia.LinkUnderliner) {
                        window.Milenia.LinkUnderliner.init($container.find('a'));
                    }
                });
            }


        });
    })(window.jQuery);
</script>
${widget_title}
<div id="${unique_id}" class="${container_classes}" data-animation="${css_animation}">
    <div class="${grid_classes}">
        ${items}
    </div>
</div>
<!--================ End of Room types ================-->
