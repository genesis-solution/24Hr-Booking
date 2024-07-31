<!--================ Room types ================-->
<script>
    (function($){
        'use strict';

        var $container, $carousel, $bgImages, $thumbnailsContainer, $thumbnailsGrid;

        if(!$) return;

        $(function(){
            if(window.DOMDfd)
            {
                window.DOMDfd.done(function(){
                    // Check all necessary modules have been included
                    if(!window.Milenia || !$.fn.jQueryImagesLoaded) return;

                    $container = $('#${unique_id}-grid');
                    $thumbnailsContainer = $('#${unique_id}-thumbs');

                    if(!$container.length) return;

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


                    if($thumbnailsContainer.length && window.Milenia.helpers && window.Milenia.helpers.gridOwl) {
                        $thumbnailsGrid = $thumbnailsContainer.find('.milenia-grid');

                        if($thumbnailsGrid.length) {
                            window.Milenia.helpers.gridOwl.extendConfigFor('#${unique_id}-thumbs', {
                                nav: true,
                                dots: false,
                                margin: 0,
                                mouseDrag: false,
                                loop: false, // !important
                                autoplay: false,
                                responsive: {
                                    0: {
                                        items: 1
                                    },
                                    480: {
                                        items: 2
                                    },
                                    1200: {
                                        items: 3
                                    },
                                    1300: {
                                        items: 4
                                    }
                                },
                                responsiveWithSidebar: {
                                    0: {
                                        items: 1
                                    },
                                    480: {
                                        items: 2
                                    },
                                    1350: {
                                        items: 3
                                    }
                                }
                            });

                            window.Milenia.helpers.gridOwl.add($thumbnailsGrid);
                        }
                    }

                    if(window.Milenia.helpers && window.Milenia.helpers.owlSettings) {
                        $carousel = $container.find('.owl-carousel');

                        if($carousel.length) {
                            $carousel.jQueryImagesLoaded().then(function() {
                                $carousel.owlCarousel(window.Milenia.helpers.owlSettings({
                                    loop: true,
                                    margin: 1
                                }));
                            });
                        }
                    }

                    if(window.Milenia.LinkUnderliner) {
                        window.Milenia.LinkUnderliner.init($container.find('a'));
                    }

                    if(window.Milenia.helpers && window.Milenia.helpers.owlSyncTabbed) {
                        window.Milenia.helpers.owlSyncTabbed.init($('[data-tabbed-sync="${unique_id}-grid"]'));
                    }

                    if(window.MileniaTabbedGrid) {
                        window.MileniaTabbedGrid.init($container, {
                            cssPrefix: 'milenia-',
                            easing: window.Milenia.ANIMATIONEASING,
                            duration: window.Milenia.ANIMATIONDURATION
                        });

                        $container.on('grid.resized.tabbedgrid item.shown.tabbedgrid', function(event, $grid) {
                            if($grid.data('TabsResizeTimeOutId')) clearTimeout($grid.data('TabsResizeTimeOutId'));

                            if(window.Milenia.helpers && window.Milenia.helpers.updateGlobalNiceScroll) {
                                window.Milenia.helpers.updateGlobalNiceScroll();
                            }

                            $grid.data('TabsResizeTimeOutId', setTimeout(function(){
                                var $tabs = $grid.closest('.milenia-tabs'),
                                Tabs;
                                if(!$tabs.length) return;

                                Tabs = $tabs.data('tabs');

                                if(Tabs) Tabs.updateContainer();
                            }, 100));
                        });
                    }

                    if(window.Milenia.helpers && window.Milenia.helpers.dynamicBgImage) {
                        $bgImages = $container.add($thumbnailsGrid).find('[data-bg-image-src]');
                        if($bgImages.length) Milenia.helpers.dynamicBgImage($bgImages);
                    }
                });
            }

        });
    })(window.jQuery);
</script>
${widget_title}
<!--================ Tabbed Carousel ================-->
<div id="${unique_id}" class="milenia-tabbed-carousel ${container_classes}" data-animation="${css_animation}">
    <!--================ Room Types ================-->
    <div class="${element_classes}">
        <div id="${unique_id}-grid" class="${grid_classes}">
            ${items}
        </div>
    </div>
    <!--================ End of Room Types ================-->

    <!--================ Thumbnails ================-->
    <div id="${unique_id}-thumbs" class="milenia-tabbed-carousel-thumbs">
        <div data-tabbed-sync="${unique_id}-grid" class="milenia-grid milenia-grid--cols-4 milenia-grid--shortcode owl-carousel--nav-onhover owl-carousel owl-carousel--nav-edges owl-carousel--nav-small">
            ${thumbnails}
        </div>
    </div>
    <!--================ End of Thumbnails ================-->
</div>
<!--================ End of Tabbed Carousel ================-->
