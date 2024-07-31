var Milenia = (function($){
    'use strict';

    var App = {},
        DOMDfd = $.Deferred(),
        $body = $('body'),
        $doc = $(document);

    window.DOMDfd = DOMDfd;

    App.modules = {};
    App.helpers = {};
    App._localCache = {};

    App.ISTOUCH = Modernizr.touchevents;
    App.ANIMATIONDURATION = 500;
    App.ANIMATIONEASING = 'easeOutQuart';
    App.ANIMATIONSUPPORTED = Modernizr.cssanimations;
    App.ANIMATIONEND = "webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend";
    App.RTL = getComputedStyle(document.body).direction === 'rtl';
    App.ISLEGACYBROWSER = !Modernizr.flexbox;
    App.ISFIREFOX = window.navigator.userAgent.indexOf('Firefox') != -1;

    App.afterDOMReady = function() {
        var self = this;

        this.helpers.WPRowCSS();
        this.helpers.sharer();

        // Show message to update legacy browser
        if(this.ISLEGACYBROWSER) {
            if(this.helpers.showCriticalFullScreenMessage) {
                this.helpers.showCriticalFullScreenMessage({
                    before: '<i class="icon icon-sad"></i>',
                    content: 'Your browser does not support some technologies this site use. Please update your browser or visit the site using more modern browser.'
                });
            }

            // Anyway preloader must be used
            if(window.MileniaOptions && window.MileniaOptions.preloader == '1' && this.modules.preloader) this.modules.preloader();

            return;
        }

        if(window.MileniaOptions && window.MileniaOptions.moment_locale && window.moment) {
            moment.locale(window.MileniaOptions.moment_locale);
        }

        if(this.ISFIREFOX) window.onunload = function(){};

        if(this.modules.backToTop) {
			this.modules.backToTop({
				easing: 'easeOutQuint',
				speed: 550,
				cssPrefix: 'milenia-'
			});
        }

        if(this.modules.weatherIndicator) {
			this.modules.weatherIndicator();
        }

        if(this.helpers.toggledFields) this.helpers.toggledFields();

        if(window.MileniaEventsCalendar) window.MileniaEventsCalendar.init($('.milenia-events-calendar'), {
            isTouch: self.ISTOUCH,
            cssPrefix: 'milenia-',
            breakpoint: 768
        });

        if(window.MileniaAlertBox) {
            var $nothingFoundAlertBox = $('#milenia-nothing-found-alert-box');

            if($nothingFoundAlertBox.length) {
                window.MileniaAlertBox.init($nothingFoundAlertBox, {
                    duration: self.ANIMATIONDURATION,
                    cssPrefix: 'milenia-',
                    easing: self.ANIMATIONEASING,
                    type: 'info'
                });
            }
        }

        if(window.MileniaSidebarHidden) {
            new window.MileniaSidebarHidden({
                cssPrefix: 'milenia-'
            });
        }

        if(window.MileniaStickyHeaderSection) {
            var $stickySections = $('[class*="milenia-header-section--sticky"]:not([class*="milenia-header-section--sticky-hidden"])');

            if($stickySections.length) {
                new window.MileniaStickyHeaderSection($stickySections, {
                    animationEasing: self.ANIMATIONEASING,
                    animationDuration: self.ANIMATIONDURATION
                });
            }
        }

        if(this.modules.dropdown) this.modules.dropdown.init();

        if(this.modules.fancyboxAlbum) this.modules.fancyboxAlbum.init();

        if(this.modules.hiddenFilters) this.modules.hiddenFilters();

        if(this.modules.fieldCounter) this.modules.fieldCounter();

        if(this.modules.fieldDatepicker) this.modules.fieldDatepicker.init();

        if(this.modules.WPGallery) this.modules.WPGallery.init($('.gallery'));

        if(this.helpers.bookingFormV2V4) this.helpers.bookingFormV2V4();
        if(this.helpers.bookingFormV3) this.helpers.bookingFormV3();
        if(this.helpers.calendarWidget) this.helpers.calendarWidget();
        if(this.helpers.MPHBCheckboxNRadio) this.helpers.MPHBCheckboxNRadio();

        var $datepickers = $('.milenia-datepicker'),
            borderBottomDatepickerContainers = '.milenia-booking-form-wrapper--v2';

        if($datepickers.length && $.fn.datepicker) {
            $datepickers.datepicker({
                showOtherMonths: true,
                selectOtherMonths: true,
                isRTL: self.RTL,
                dayNamesMin: ["S", "M", "T", "W", "T", "F", "S"],
                prevText: self.RTL ? '<i class="icon icon-chevron-right"></i>' : '<i class="icon icon-chevron-left"></i>',
                nextText: self.RTL ? '<i class="icon icon-chevron-left"></i>' : '<i class="icon icon-chevron-right"></i>',
                beforeShow: function(input, instance) {
                    var $input = $(input);

                    if($input.closest(borderBottomDatepickerContainers).length) {
                        instance.dpDiv.addClass('ui-datepicker--bordered-container');
                    }
                    else {
                        instance.dpDiv.removeClass('ui-datepicker--bordered-container');
                    }
                }
            });
        }

        var $selects = $('.milenia-custom-select2');

        if($selects.length && $.fn.select2) {
            $selects.select2({
                width: '100%',
                theme: 'milenia',
                dir: self.RTL ? 'rtl' : 'ltr'
            });
        }

        /* ------------------------------------------------
			Range Slider
		------------------------------------------------ */

            var $rangeSliders = $('.milenia-range-slider');

            if($.fn.slider && $rangeSliders.length) {
                $rangeSliders.slider({
                    range: true,
                    min: 0,
                    max: 999,
                    values: [99, 999],
                    slide: function(event, ui) {
                        var $range = $(ui.handle).closest('.milenia-range-slider'),
                            $input = $range.siblings('.milenia-range-slider-input');

                        if($range.length && $input.length) {
                            $input.attr('value', '$' + $range.slider('values', 0) + '-' + '$' + $range.slider('values', 1))
                                .val('$' + $range.slider('values', 0) + '-' + '$' + $range.slider('values', 1));
                        }
                    }
                });
            }

        /* ------------------------------------------------
			End of Range Slider
		------------------------------------------------ */

        /* ------------------------------------------------
			Custom Select
		------------------------------------------------ */

            var $selects = $('.milenia-custom-select'),
                $wpSelects = $('.milenia-widget select, .mphb_sc_checkout-wrapper select, .mphb-booking-form select, .mphb-rooms-quantity');

            if ( $selects.length || $wpSelects.length ) {
	            if($wpSelects.length) {
		            $wpSelects.wrap('<div class="milenia-custom-select"></div>');
	            }

		         $.MadCustomSelect();
            }

            // if($wpSelects.length) {
            //     $wpSelects.wrap('<div class="milenia-custom-select"></div>');
            //
	           //  $.MadCustomSelect();

                // $wpSelects.closest('.milenia-custom-select').MadCustomSelect({
                //     cssPrefix: 'milenia-'
                // });
            // }

		/* ------------------------------------------------
			End of Custom Select
		------------------------------------------------ */



        /* ------------------------------------------------
            Contact Form
        ------------------------------------------------ */

            var contactForm = $('.milenia-contact-form');

            if(contactForm.length && window.MileniaContactForm) {
                MileniaContactForm.init(contactForm);
            }

        /* ------------------------------------------------
            End of Contact Form
        ------------------------------------------------ */

        /* ----------------------------------------
             Fancybox
         ---------------------------------------- */

            if($.fancybox && $.fancybox.defaults) {
                var $contentImageLinks = $('.milenia-entity-body a:not(.apo-project-cover-link)[href$=".jpg"], .milenia-entity-body a:not(.apo-project-cover-link)[href$=".jpeg"], .milenia-entity-body a:not(.apo-project-cover-link)[href$=".png"], .apo-page-content a:not(.apo-project-cover-link)[href$=".jpg"], .apo-page-content a:not(.apo-project-cover-link)[href$=".jpeg"], .apo-page-content a:not(.apo-project-cover-link)[href$=".png"]');

                $.extend($.fancybox.defaults, {
                    transitionEffect: "slide",
                    transitionDuration: self.ANIMATIONDURATION,
                    animationDuration: self.ANIMATIONDURATION
                });

                if($contentImageLinks.length) {
    				$contentImageLinks.fancybox({
    					buttons : ['close']
    				});
    			}
            }

        /* ----------------------------------------
             End of Fancybox
         ---------------------------------------- */

        if(this.modules.arcticModals) this.modules.arcticModals.init();

        /* ------------------------------------------------
			Navigation
		------------------------------------------------ */

            var $nav = $('.milenia-navigation'),
                $verticalNav = $('.milenia-navigation-vertical');

            if ( $nav.length ) {
                $nav.MonkeysanNav({
                  cssPrefix: 'milenia-',
	                mobileBreakpoint: MileniaOptions.mobile_breakpoint
                });

                $nav.on('submenumobileopened.jquery.nav', function() {
                    self.LinkUnderliner.toUnderline($(this).find('a'));
                });
            }

            if ( $verticalNav.length ) {
                $verticalNav.MonkeysanNav({
                  cssPrefix: 'milenia-',
                  mobileBreakpoint: 10000
                });
                $verticalNav.on('submenumobileopened.jquery.nav', function() {
                    self.LinkUnderliner.toUnderline($(this).find('a'));
                });
            }

        /* ------------------------------------------------
				End of Navigation
		------------------------------------------------ */

        /* ------------------------------------------------
				Custom Scrollbar
		------------------------------------------------ */

            var $customScrollbar = $('.milenia-sidebar-hidden .milenia-sidebar-hidden-content .milenia-navigation-container'),
                $customScrollbarVerticalNav = $customScrollbar.children('.milenia-navigation-vertical'),
                $customScrollbar2 = $('.milenia-sidebar-hidden--v2');
            if($customScrollbar.length && $.fn.niceScroll) {
                $customScrollbar.niceScroll({
                    scrollspeed: 60,
                    mousescrollstep: 40,
                    cursorwidth: 2,
                    cursorborder: 0,
                    rtlmode: self.RTL,
                    railalign: self.RTL ? 'left': 'right',
                    cursorborderradius: 0,
                    cursorcolor: "#1c1c1c",
                    autohidemode: false,
                    horizrailenabled: false
                });

                if($customScrollbarVerticalNav.length) {
                    $customScrollbarVerticalNav.on('navigationopening.jquery.nav submenumobileopening.jquery.nav submenumobileclosing.jquery.nav', function() {
                        var scroller = $customScrollbar.getNiceScroll(0);
                        $customScrollbar.getNiceScroll().resize();
                        setTimeout(function(){
                            scroller.doScrollTop(scroller.getScrollTop() + 10, 100);
                            $customScrollbar.getNiceScroll().resize();
                        }, 200);
                    });
                }
            }

            if($customScrollbar2.length && $.fn.niceScroll) {

                $customScrollbar2.niceScroll({
                    scrollspeed: 60,
                    mousescrollstep: 40,
                    cursorwidth: 2,
                    cursorborder: 0,
                    rtlmode: self.RTL,
                    railalign: self.RTL ? 'left': 'right',
                    cursorborderradius: 0,
                    cursorcolor: "#1c1c1c",
                    autohidemode: false,
                    horizrailenabled: false
                });
            }

            setTimeout(function() {
                $customScrollbar2.getNiceScroll().resize();
            }, self.ANIMATIONDURATION);

        /* ------------------------------------------------
				End of Custom Scrollbar
		------------------------------------------------ */



          /* ----------------------------------------
                  Alert Boxes
           ---------------------------------------- */

               $doc.on('pushed.milenia.alert closed.milenia.alert', function(event) {
                   self.helpers.updateGlobalNiceScroll();
               });

           /* ----------------------------------------
                  End of Alert Boxes
            ---------------------------------------- */

            /* ----------------------------------------
                    Tooltips
             ---------------------------------------- */

                if( $('[data-tooltip]').length && $.fn.MonkeysanTooltip ) {
                    $('[data-tooltip]').MonkeysanTooltip({
                        animationIn: 'fadeInDown',
                        animationOut: 'fadeOutUp',
                        tooltipPosition: 'top',
                        jQueryAnimationEasing: self.ANIMATIONEASING,
                        jQueryAnimationDuration: self.ANIMATIONDURATION,
                        skin: 'milenia'
                    });
                }

             /* ----------------------------------------
                    End of Tooltips
              ---------------------------------------- */

             /* ----------------------------------------
                    IsotopeWrapper
              ---------------------------------------- */

                    var $isotope = $('.milenia-grid--isotope:not(.milenia-grid--isotope-lazy):not(.milenia-grid--shortcode)'),
                        $isotopeLazy = $('.milenia-grid--isotope.milenia-grid--isotope-lazy:not(.milenia-grid--shortcode)');

                    if($isotope.length && window.MileniaIsotopeWrapper) {
                        $isotope.each(function(index, container) {
                            var $container = $(container),
                                $stretchedSection = $container.closest('.milenia-section--stretched-content, .milenia-section--stretched-content-no-px');

                            if($stretchedSection.length) {
                                $stretchedSection.on('stretched.milenia.Section', function() {
                                    if($container.data('IsotopeWrapper')) return;

                                    $container.jQueryImagesLoaded().then(function(){
                                        MileniaIsotopeWrapper.init($container, {
                                            itemSelector: '.milenia-grid-item',
                                            transitionDuration: self.ANIMATIONDURATION
                                        });
                                    });
                                });
                            }
                            else {
                                $container.jQueryImagesLoaded().then(function(){
                                    MileniaIsotopeWrapper.init($container, {
                                        itemSelector: '.milenia-grid-item',
                                        transitionDuration: self.ANIMATIONDURATION
                                    });
                                });
                            }
                        });
                    }

                    if($isotopeLazy.length && window.MileniaIsotopeWrapper) {
                        $isotopeLazy.each(function(index, container){
                            var $container = $(container),
                                $stretchedSection = $container.closest('.milenia-section--stretched-content, .milenia-section--stretched-content-no-px');

                            if($stretchedSection.length) {
                                $stretchedSection.on('stretched.milenia.Section', function() {
                                    if($container.data('IsotopeWrapper')) return;

                                    setTimeout(function(){
                                        MileniaIsotopeWrapper.init($container, {
                                            itemSelector: '.milenia-grid-item',
                                            transitionDuration: self.ANIMATIONDURATION
                                        });
                                    }, 500);
                                });
                            }
                            else {
                                setTimeout(function(){
                                    MileniaIsotopeWrapper.init($container, {
                                        itemSelector: '.milenia-grid-item',
                                        transitionDuration: self.ANIMATIONDURATION
                                    });
                                }, 500);
                            }
                        });
                    }

              /* ----------------------------------------
                    End of IsotopeWrapper
              ---------------------------------------- */

              /* ----------------------------------------
                    Dynamic background image
               ---------------------------------------- */

                    var $backgrounds = $('[data-bg-image-src]:not([class*="milenia-colorizer--scheme-"]):not(.milenia-colorizer-functionality)');

                    if($backgrounds.length && this.helpers.dynamicBgImage) {
                        this.helpers.dynamicBgImage($backgrounds);
                    }

               /* ----------------------------------------
                    End of Dynamic background image
                ---------------------------------------- */

              /* ----------------------------------------
                    Owl Carousel
               ---------------------------------------- */

                    // owl carousel adaptive
                    if($('.owl-carousel').length) this.helpers.owlAdaptive();

                    var $simpleSlideshow = $('.milenia-simple-slideshow:not(.milenia-simple-slideshow--shortcode)');

                    if($simpleSlideshow.length && $.fn.owlCarousel) {
                        $simpleSlideshow.jQueryImagesLoaded().then(function(){
                            $simpleSlideshow.each(function(index, carousel){
                                var $carousel = $(carousel),
                                    $stretchedSection = $carousel.closest('.milenia-section--stretched-content, .milenia-section--stretched-content-no-px');

                                if($stretchedSection.length) {
                                    $stretchedSection.each(function(scindex, scelement){
                                        $(scelement).on('stretched.milenia.Section', function() {


                                                $carousel.owlCarousel(self.helpers.owlSettings({
                                                    margin: 1,
                                                    loop: true,
                                                    autoplay: $carousel.hasClass('milenia-simple-slideshow--autoplay')
                                                }));



                                        });
                                    });
                                }
                                else {

                                    $carousel.owlCarousel(self.helpers.owlSettings({
                                        margin: 1,
                                        loop: true,
                                        autoplay: $carousel.hasClass('milenia-simple-slideshow--autoplay')
                                    }));
                                }
                            });
                        });
                    }

                    // Initialization owl carousels placed in the stretched sections
                    $('[class*="milenia-section--stretched-content"]').on('stretched.milenia.Section', function(event, $section) {
                        var $gridOwlCarousels = $section.find('.milenia-grid.owl-carousel:not(.milenia-grid--shortcode)'),
                            $simpleThumbs = $section.find('.milenia-simple-slideshow-thumbs.owl-carousel');

                        if($gridOwlCarousels.length) self.helpers.gridOwl.add($gridOwlCarousels);

                        if($simpleThumbs.length) {
                            $simpleThumbs.owlCarousel(self.helpers.owlSettings({
                                responsive: {
                                    0: {
                                        items: 2
                                    },
                                    380: {
                                        items: 3
                                    },
                                    992: {
                                        items: 4
                                    },
                                    1200: {
                                        items: 6
                                    }
                                },
                                margin: 10,
                                loop: false
                            }));
                        }
                    });

                    // Initialization owl carousels placed in the normal sections
                    var $simpleThumbs = $('.milenia-simple-slideshow-thumbs.owl-carousel').filter(function(index, element){
                        return !$(element).closest('[class*="milenia-section--stretched-content"]').length;
                    });

                    if($simpleThumbs.length) {
                        $simpleThumbs.owlCarousel(self.helpers.owlSettings({
                            responsive: {
                                0: {
                                    items: 2
                                },
                                380: {
                                    items: 3
                                },
                                992: {
                                    items: 4
                                },
                                1200: {
                                    items: 6
                                }
                            },
                            margin: 10,
                            loop: false
                        }));
                    }

                    this.helpers.gridOwl.add($('.milenia-grid.owl-carousel:not(.milenia-grid--shortcode)').filter(function(index, element){
                        return !$(element).closest('[class*="milenia-section--stretched-content"]').length;
                    }));

                    this.helpers.owlSync.init();


               /* ----------------------------------------
                    End of Owl Carousel
                ---------------------------------------- */

               /* ----------------------------------------
                    Rating
                ---------------------------------------- */

                    var $ratingFields = $('.milenia-rating-field'),
                        $ratings;

                    if($ratingFields.length) {
                        $ratings = $ratingFields.find('.milenia-rating');

                        if($ratings.length) {
                            $ratings.on('built.milenia.Rating', function(event, $rating) {
                                var $tabs = $rating.closest('.milenia-tabs'),
                                    Tabs;

                                if($tabs.length) {
                                    Tabs = $tabs.data('tabs');

                                    if(Tabs) Tabs.updateContainer();
                                }
                            });
                        }
                    }


                    if(this.helpers.rating) this.helpers.rating($('.milenia-rating:not(.milenia-rating--independent)'), {
                        topLevelElements: null,
                        bottomLevelElements: '<i class="icon icon-star"></i>'
                    });

                    if(this.helpers.rating) this.helpers.rating($('.milenia-rating--independent'), {
                        topLevelElements: '<i class="icon icon-star"></i>',
                        bottomLevelElements: '<i class="icon icon-star"></i>'
                    });

                    if(this.helpers.ratingField) this.helpers.ratingField($('.milenia-rating-field'));

               /* ----------------------------------------
                    End of Rating
                ---------------------------------------- */

                if(this.helpers.touchHoverEmulator) this.helpers.touchHoverEmulator($('.milenia-entities--style-17'), '.milenia-entity-link', '.milenia-entity');

        /* ----------------------------------------
               Self Hosted Video
         ---------------------------------------- */

           var $selfHostedVideos = $('.milenia-selfhosted-video');

           if($selfHostedVideos.length) {
               $selfHostedVideos.on('click.MileniaSelfHostedVideo', function(event) {
                   var $this = $(this),
                       $state = $this.find('.mejs__overlay-play');

                   if($state.length) {
                       setTimeout(function() {
                           $this[!$state.is(':visible') ? 'addClass' : 'removeClass']('milenia-selfhosted-video--playing');
                       },0);
                   }
               });
           }

        /* ----------------------------------------
               End of Self Hosted Video
         ---------------------------------------- */

         $body.on('spaceadded.milenia.stickysection spaceremoved.milenia.stickysection', function(){
             self.helpers.updateGlobalNiceScroll();
         });

         $doc.on('container.updated.mokeysan.tabs', function(event, $container) {
             self.helpers.updateGlobalNiceScroll();
         });

         if($('.milenia-entity-content iframe[src*="youtube.com"]')) this.helpers.wrapYoutube( $('.milenia-entity-content iframe[src*="youtube.com"]') );

         DOMDfd.resolve();


          if ($('body').is('.tribe-mobile')) {
            $(document.getElementById('tribe-events')).unbind('click')
          }

    };

    App.afterOuterResourcesLoaded = function() {

        var self = this;

        /* ------------------------------------------------
				Revolution slider
		------------------------------------------------ */

            var $revSlider1 = $('#rev-slider-1'),
                $revSlider2 = $('#rev-slider-2'),
                $revSlider3 = $('#rev-slider-3'),
                $revSlider4 = $('#rev-slider-4'),
                revApi1,
                revApi2,
                revApi3;

            if($revSlider1.length && $.fn.revolution) {
                revApi1 = $revSlider1.show().revolution({
                    fullScreenOffsetContainer: ($(window).width() > 767 && $(window).height() > 600) ? '#milenia-header:not(.milenia-header--transparent-single), #wpadminbar' : '',
                    sliderLayout: 'fullscreen',
                    dottedOverlay: 'milenia',
                    disableProgressBar: "on",
                    spinner: 'spinner3',
                    gridwidth:[1400, 1024, 813, 580],
                    responsiveLevels: [1400, 1024, 813, 580],
                    parallax: {
                        type: 'mouse+scroll',
                        origo: 'slidercenter',
                        speed: 400,
                        levels: [5,10,15,20,25,30,35,40,45,46,47,48,49,50,51,55],
                        disable_onmobile: 'on'
                    },
                    navigation: {
                        keyboardNavigation: 'on',
                        keyboard_direction: 'horizontal',
                        onHoverStop: 'false',
                        arrows: {
                            enable: false,
                        },
                        bullets: {
                            enable: true,
                            style: 'milenia',
                            hide_onleave: false,
                            h_align: 'right',
                            v_align: 'center',
                            direction: 'vertical',
                            h_offset: 60,
                            v_offset: 0,
                            space: 12,
                            hide_under: 1200
                        }
                    }
                });

                revApi1.on('revolution.slide.onchange', function() {
                    self.helpers.updateGlobalNiceScroll();
                });
            }

            if($revSlider2.length && $.fn.revolution) {
                revApi2 = $revSlider2.show().revolution({
                    fullScreenOffsetContainer: ($(window).width() > 767 && $(window).height() > 600) ? '#milenia-header:not(.milenia-header--transparent-single), #wpadminbar' : '',
                    sliderLayout: 'fullwidth',
                    dottedOverlay: 'milenia',
                    disableProgressBar: "on",
                    spinner: 'spinner3',
                    responsiveLevels: [1400, 1024, 813, 580],
                    gridwidth:[1400, 1024, 813, 580],
                    gridheight:[800, 600, 500, 400],
                    visibilityLevels:[1400, 1024, 813, 580],
                    parallax: {
                        type: 'mouse+scroll',
                        origo: 'slidercenter',
                        speed: 400,
                        levels: [5,10,15,20,25,30,35,40,45,46,47,48,49,50,51,55],
                        disable_onmobile: 'on'
                    },
                    navigation: {
                        keyboardNavigation: 'on',
                        keyboard_direction: 'horizontal',
                        onHoverStop: 'false',
                        arrows: {
                            enable: false,
                        },
                        bullets: {
                            enable: true,
                            style: 'milenia',
                            hide_onleave: false,
                            h_align: 'right',
                            v_align: 'center',
                            direction: 'vertical',
                            h_offset: 60,
                            v_offset: 0,
                            space: 12,
                            hide_under: 1200
                        }
                    }
                });

                revApi2.on('revolution.slide.onchange', function() {
                    self.helpers.updateGlobalNiceScroll();
                });
            }

            if($revSlider3.length && $.fn.revolution) {
                revApi3 = $revSlider3.show().revolution({
                    fullScreenOffsetContainer: ($(window).width() > 767 && $(window).height() > 600) ? '#milenia-header:not(.milenia-header--transparent-single), #wpadminbar' : '',
                    sliderLayout: 'fullwidth',
                    disableProgressBar: "on",
                    spinner: 'spinner3',
                    responsiveLevels: [1400, 1024, 813, 580],
                    gridwidth:[1400, 1024, 813, 580],
                    gridheight:[640, 600, 500, 400],
                    visibilityLevels:[1400, 1024, 813, 580],
                    parallax: {
                        type: 'mouse+scroll',
                        origo: 'slidercenter',
                        speed: 400,
                        levels: [5,10,15,20,25,30,35,40,45,46,47,48,49,50,51,55],
                        disable_onmobile: 'on'
                    },
                    navigation: {
                        keyboardNavigation: 'on',
                        keyboard_direction: 'horizontal',
                        onHoverStop: 'false',
                        arrows: {
                            enable: false,
                        },
                        bullets: {
                            enable: false
                        }
                    }
                });

                revApi3.on('revolution.slide.onchange', function() {
                    self.helpers.updateGlobalNiceScroll();
                });
            }

            if($revSlider4.length && $.fn.revolutionInit) {
                window.roomRevApi = $revSlider4.show().revolutionInit({
                    fullScreenOffsetContainer: ($(window).width() > 767 && $(window).height() > 600) ? '#milenia-header:not(.milenia-header--transparent-single), #wpadminbar' : '',
                    sliderLayout: 'fullscreen',
                    disableProgressBar: "on",
                    spinner: 'spinner3',
                    gridwidth:[1400, 1024, 813, 580],
                    responsiveLevels: [1400, 1024, 813, 580],
                    navigation: {
                        arrows: {
                            enable: false,
                        },
                        bullets: {
                            enable: false
                        }
                    }
                });

                window.roomRevApi.on('revolution.slide.onchange', function() {
                    self.helpers.updateGlobalNiceScroll();
                });
            }

            if(this.helpers.revArrowsOutside) this.helpers.revArrowsOutside();

        /* ------------------------------------------------
				End of Revolution slider
		------------------------------------------------ */

        // Stop initializing any modules in case legacy browser is using
        if(this.ISLEGACYBROWSER) return;

        setTimeout(function(){ if(self.LinkUnderliner) self.LinkUnderliner.init($('a, .milenia-btn--link'), {
            except: '.custom-logo-link',
            exceptClass: 'milenia-ln--independent'
        }); }, 100);

        var $sections = $('.milenia-section');

        if(this.helpers.Colorizer) this.helpers.Colorizer.init($('[class*="milenia-colorizer--scheme-"], .milenia-colorizer-functionality'));

        if(this.helpers.footerWidgetsSettings && window.MileniaFooterWidgetsSettings) this.helpers.footerWidgetsSettings(window.MileniaFooterWidgetsSettings);

        if(this.modules.Section && $sections.length) {
            this.modules.Section.init($sections);
        }

        if(this.helpers.Breadcrumb) this.helpers.Breadcrumb.init($('.milenia-header--transparent + .milenia-breadcrumb[data-bg-image-src]'));

        /* ----------------------------------------
                Tabs & Tour Sections
         ---------------------------------------- */

            var $tabs = $('.milenia-tabs--integrated');

            if($tabs.length && $.fn.MonkeysanTabs) {
                $tabs.MonkeysanTabs({
					speed: self.ANIMATIONDURATION,
                    easing: self.ANIMATIONEASING,
					cssPrefix: 'milenia-',
                    afterOpen: function() {
                        self.helpers.updateGlobalNiceScroll();
                    },
                    afterClose: function() {
                        self.helpers.updateGlobalNiceScroll();
                    },
                    afterInit: function() {
                        var $bookButton = this.tabsContainer.find('.mphb-book-button'),
                            Tabs = this;

                        openMonkeysanTabByHash.call(Tabs, window.location.hash + '-room', true);

                        if($bookButton.length) {
                            $bookButton.on('click.MileniaTabs', function(e){
                                openMonkeysanTabByHash.call(Tabs, $bookButton.attr('href').substr($bookButton.attr('href').indexOf('#')) + '-room');
                                e.preventDefault();
                            });
                        }
                    }
				});


            }

         /* ----------------------------------------
                End of Tabs & Tour Sections
          ---------------------------------------- */

        /* ----------------------------------------
            SameHeight
        ---------------------------------------- */

            if($.fn.MonkeysanSameHeight) {

                var $sameheightContainers = $('.milenia-entities--style-6 .milenia-grid:not([data-isotope-layout="masonry"]):not(.milenia-grid--cols-1), .milenia-entities--style-7 .milenia-grid:not([data-isotope-layout="masonry"]):not(.milenia-grid--cols-1), .milenia-entities--style-8 .milenia-grid:not([data-isotope-layout="masonry"]):not(.milenia-grid--cols-1), .milenia-entities--style-12 .milenia-grid:not([data-isotope-layout="masonry"]):not(.milenia-grid--cols-1)').not('.milenia-shortcode-container'),
                    $pricingTables = $('.milenia-flexbox .milenia-pricing-tables .milenia-grid:not([data-isotope-layout="masonry"])');

                if( $sameheightContainers.length ) {

                    $sameheightContainers.each(function(index, container){
                        var $container = $(container),
                            $items = $container.find('.milenia-grid-item');

                        if($items.length) {
                            $container.MonkeysanSameHeight({
                                target: $container.closest('.milenia-entities--style-7').length ? '.milenia-entity' : '.milenia-entity-content .milenia-aligner-inner',
                                isIsotope: $container.find('.milenia-grid--isotope').length || $container.hasClass('milenia-grid--isotope'),
                                columns: $items.length ? Math.floor( $container.outerWidth() / $items.first().outerWidth() ) : null
                            });
                        }
                    });
                }

                if( $pricingTables.length ) {

                    $pricingTables.each(function(index, container){
                        var $container = $(container),
                            $items = $container.find('.milenia-grid-item');

                        if($items.length) {
                            $container.MonkeysanSameHeight({
                                target: '.milenia-pricing-table',
                                isIsotope: $container.find('.milenia-grid--isotope').length,
                                columns: $items.length ? Math.ceil( $container.outerWidth() / $items.first().outerWidth() ) : null
                            });
                        }
                    });
                }
            }

        /* ----------------------------------------
            End of SameHeight
        ---------------------------------------- */

            if(self.helpers.fullScreenArea) self.helpers.fullScreenArea.init({
                except: $('#milenia-header:not(.milenia-header--transparent)').add($('#milenia-footer')).add($('.milenia-fullscreen-area-except'))
            });

          if(window.MileniaOptions && window.MileniaOptions.preloader == '1' && this.modules.preloader) this.modules.preloader();

          var $parallaxSections = $('.milenia-colorizer--parallax .milenia-colorizer-bg-image');

          if($parallaxSections.length) {
              $parallaxSections.parallax("50%",.4);
          }

          // Refresh the owl carousels after full width sections stretch
          if(this.helpers.VCONStretch) {
              this.helpers.VCONStretch.push(function($container){
                  var $owlCarousels = $container.find('.owl-carousel');
                  if($owlCarousels.length) $owlCarousels.trigger('refresh.owl.carousel');
              });

              this.helpers.VCONStretch.init();
          }

          if(self.helpers.PageStretcher) {
              var $stretcherTarget = $('.milenia-stretcher-target');

              self.helpers.PageStretcher.init({
                  target: $stretcherTarget.length ? '.milenia-stretcher-target' : '.milenia-content',
                  both: $stretcherTarget.length,
                  ignore: '#wpadminbar'
              });
          }
    };

    App.helpers.VCONStretch = {};
    App.helpers.VCONStretch.callbacks = [];

    App.helpers.VCONStretch.push = function(callback) {
        if(this.callbacks.indexOf(callback) == -1) this.callbacks.push(callback);
    };

    App.helpers.VCONStretch.init = function() {
        var $fullWidthSections = $('[data-vc-full-width]'),
            self = this;

        if($fullWidthSections.length) {
            $fullWidthSections.each(function(index, section){
                var $section = $(section);

                self.callbacks.forEach(function(callback){
                    callback.call(section, $section);
                });
            });
        }
    };


    App.LinkUnderliner = {
        _$collection: $(),
        _config: {
            except: null,
            exceptClass: null
        },
		init: function($collection, config) {
			var self = this,
				$currentFilteredCollection;

			if(!$.isjQuery($collection) || !$collection.length) return;

            this.config = $.extend(true, {}, this._config, $.isPlainObject(config) ? config : {});

			if(!this._bindedEvents) this._bindEvents();

			$currentFilteredCollection = $();

			$collection.each(function(index, element){
				var $element = $(element);

				if(self._$collection.filter($element).length) return;

                if($element.is(self.config.except)) {
                    $element.addClass(self.config.exceptClass);
                    return;
                }

				self._$collection = self._$collection.add($element);
				$currentFilteredCollection = $currentFilteredCollection.add($element);
			});

			return this.toUnderline($currentFilteredCollection);
		},
		isRTL: function() {
			return getComputedStyle(document.body).direction === 'rtl';
		},
		_bindEvents: function() {
			var self = this;

			$(window).on('resize.MileniaLinksUnderline', function() {
				if(self.resizeTimeOutId) clearTimeout(self.resizeTimeOutId);

				self.resizeTimeOutId = setTimeout(function(){
					self.toUnderline(self._$collection);
				}, 100);
			});
		},
		toUnderline: function($collection) {
			var self = this;

			if(!$.isjQuery($collection) || !$collection.length) return;

			return $collection.each(function(index, element){
				var $element = $(element),
					transitionDuration = getComputedStyle($element.get(0)).transitionDuration,
					transitionDurationMS = parseFloat(transitionDuration, 10) * 1000;

				if(transitionDurationMS) {
					setTimeout(function(){
						self.setUnderlineToElement($element);
					}, transitionDurationMS);
				}
				else {
					self.setUnderlineToElement($element);
				}
			});
		},
		setUnderlineToElement: function($element) {
			var backgroundPosition = $element.css('background-position').split(' '),
				resultLineHeight;

			$element.css('white-space', 'nowrap');
			resultLineHeight = $element.outerHeight() - 1;
			$element.css('white-space', '');

			if(this.isRTL() && backgroundPosition[0]) backgroundPosition[0] = '100%';

			if(backgroundPosition[1]) backgroundPosition[1] = resultLineHeight + 'px';

			$element.css('background-position', backgroundPosition.join(' '));
		}
    };

    /* ----------------------------------------
            Weather indicator
     ---------------------------------------- */

        App.modules.weatherIndicator = function() {
            $('body').on('click.MileniaWeatherIndicator', '.milenia-weather-indicator-btn', function(event) {
                var $this = $(this),
                    $container = $this.closest('.milenia-weather-indicator');

                if($this.closest('.milenia-weather-indicator-celsius').length) {
                    $container.addClass('milenia-weather-indicator--fahrenheit-state');
                }
                else if($this.closest('.milenia-weather-indicator-fahrenheit').length) {
                    $container.removeClass('milenia-weather-indicator--fahrenheit-state');
                }
            });
        };

    /* ----------------------------------------
            End of Weather indicator
     ---------------------------------------- */

    /* ----------------------------------------
            Back to top
     ---------------------------------------- */

        App.modules.backToTop = function(config) {

             var backToTop = {

                 init: function(config){

                     var self = this;

                     if(config) this.config = $.extend(this.config, config);

                     this.btn = $('<button></button>', {
                         class: self.config.cssPrefix+'back-to-top animated stealthy',
                         html: '<span class="icon icon-chevron-up"></span>'
                     });

                     this.bindEvents();

                     $body.append(this.btn);

                 },

                 config: {
                     breakpoint: 700,
                     showClass: 'zoomIn',
                     hideClass: 'zoomOut',
                     easing: 'linear',
                     speed: 500,
                     cssPrefix: ''
                 },

                 bindEvents: function(){

                     var page = $('html, body'),
                         self = this;

                     this.btn.on('click', function(e){

                         $body.getNiceScroll().stop();

                         page.stop().animate({

                             scrollTop: 0

                         }, {
                             easing: self.config.easing,
                             duration: self.config.speed
                         });

                     });

                     this.btn.on(App.ANIMATIONEND, function(e){

                         e.preventDefault();

                         var $this = $(this);

                         if($this.hasClass(self.config.hideClass)){

                             $this
                                 .addClass('stealthy')
                                 .removeClass(self.config.hideClass + " " + self.config.cssPrefix + "inview");

                         }

                     });

                     $(window).on('scroll.backtotop', { self: this}, this.toggleBtn);

                 },

                 toggleBtn: function(e){

                     var $this = $(this),
                         self = e.data.self;

                     if($this.scrollTop() > self.config.breakpoint && !self.btn.hasClass(self.config.cssPrefix + 'inview')){

                         self.btn
                                 .addClass(self.config.cssPrefix + 'inview')
                                 .removeClass('stealthy');

                         if(App.ANIMATIONSUPPORTED){
                             self.btn.addClass(self.config.showClass);
                         }

                     }
                     else if($this.scrollTop() < self.config.breakpoint && self.btn.hasClass(self.config.cssPrefix + 'inview')){

                         self.btn.removeClass(self.config.cssPrefix + 'inview');

                         if(!App.ANIMATIONSUPPORTED){
                             self.btn.addClass('stealthy');
                         }
                         else{
                             self.btn.removeClass(self.config.showClass)
                                     .addClass(self.config.hideClass);
                         }

                     }

                 }

             };

             backToTop.init(config);

             return this;

         };

    /* ----------------------------------------
            End of Back to top
     ---------------------------------------- */

    /* ----------------------------------------
            Preloader
     ---------------------------------------- */

        App.modules.preloader = function() {
            var $preloader = $('.milenia-preloader'),
                leftPos = parseInt($preloader.css('margin-left'), 10),
                topPos = parseInt($preloader.css('margin-top'), 10),
                $w = $(window),
                $nav = $('.milenia-navigation, .milenia-navigation-vertical');

            if($nav.length) {
                $nav.off('click.MileniaPreloader').on('click.MileniaPreloader', 'a', function(event){

                    var $this = $(this),
                        $circle = $('<div></div>', {
                            style: 'left: '+ event.clientX +'px; top: '+ event.clientY +'px;',
                            class: 'milenia-preloader-circle'
                        });

                    if($body.hasClass('milenia-body--moving-to-another-page')) {
                        if (!$('.milenia-preloader-circle').length) {
                            $circle.appendTo($body);
                        }
                        $('.milenia-preloader-circle').addClass('milenia-preloader-circle--appearing');

                        setTimeout(function(){
                            $('.milenia-preloader-circle').removeClass('milenia-preloader-circle--appearing');
                        }, 700);
                    }
                });
            }

            if(!$preloader.length) return;

            $body.off('mousemove.MileniaPreloader').on('mousemove.MileniaPreloader', function(event) {
                $preloader.css({
                    'margin-left': leftPos - ($w.width() / 2 - event.pageX),
                    'margin-top': topPos - ($w.height() / 2 - (event.pageY - $w.scrollTop())),
                });
            }).jQueryImagesLoaded().then(function(){
                var $niceScrollRails = $('.nicescroll-rails');

                $preloader.addClass('milenia-preloader--disappearing');
                setTimeout(function() {
                    $preloader.remove();
                    $body.off('mousemove.MileniaPreloader');
                    App.helpers.updateGlobalNiceScroll();
                    if($niceScrollRails.length) $niceScrollRails.css('visibility', 'visible');
                }, 700);
                // can be removed in production (demo only):
                if(window.location.hash == '#milenia-footer') {
                    $('html, body').stop().animate({
                        scrollTop: $doc.height()
                    }, {
                        duration: self.ANIMATIONDURATION,
                        easing: self.ANIMATIONEASING
                    });
                }
            });
        };

    /* ----------------------------------------
            End of Preloader
     ---------------------------------------- */

    /* ----------------------------------------
            Field Counter
     ---------------------------------------- */

        App.modules.fieldCounter = function() {
            $body.on('click.MileniaFieldCounter', '.milenia-field-counter-control', function(e) {
                var $this = $(this),
                    $field = $this.siblings('.milenia-field-counter-target'),
                    $value = $this.siblings('.milenia-field-counter-value'),
                    val = +$field.val(),
                    max = $this.parent('[data-counter-max]').length && $this.parent('[data-counter-max]').data('counter-max');

                if($this.hasClass('milenia-field-counter-control--decrease') && val != 0) {
                    val--;
                }
                else if($this.hasClass('milenia-field-counter-control--increase') && val < max) {
                    val++;
                }

                $field.val(val);
                $value.text(val);

                e.preventDefault();
            });
        };

    /* ----------------------------------------
            End of Field Counter
     ---------------------------------------- */

    /* ----------------------------------------
            WPGallery
     ---------------------------------------- */

        App.modules.WPGallery = {};
        App.modules.WPGallery._cache = [];

        App.modules.WPGallery.init = function($collection) {
            var self = this;

            if(!$.isjQuery($collection, true)) return $collection;

            return $collection.each(function(index, gallery){
                var $gallery = $(gallery);

                if(self.isInitialized($gallery)) return;

                self.initializeSingle($gallery);
            });
        };

        App.modules.WPGallery.isInitialized = function($gallery) {
            return !$.isjQuery($gallery, true) || $gallery.data('milenia-wp-gallery-initialized');
        };

        App.modules.WPGallery.initializeSingle = function($gallery) {
            var $items,
                id;

            if(!$.isjQuery($gallery, true)) return $gallery;

            $items = $gallery.find('a[href$=".jpg"], a[href$=".png"], a[href$=".jpeg"], a[href$=".gif"]');

            if($items.length) {
                id = App.helpers.getRandomId('gallery');

                $items.data('fancybox', id).attr('data-fancybox', id);

                $gallery.data('milenia-wp-gallery-initialized', true);
            }

            return $gallery;
        };

    /* ----------------------------------------
            End of WPGallery
     ---------------------------------------- */


    /* ----------------------------------------
        Field Datepicker
     ---------------------------------------- */

        App.modules.fieldDatepicker = {
            $forms: $('.milenia-booking-form'),
            init: function($forms) {
                var self = this;
                $forms = $.isjQuery($forms) ? $forms : this.$forms;

                $forms.each(function(index, form) {
                    var $form = $(form);

                    self.fillHiddenInputs($form)
                        .renderDates($form)
                        .bindEvents($form);
                });

                $('body').bind('click.MileniaFieldDatepicker', '.mphb-selectable-date', function(event){
                    $forms.each(function(index, form) {
                        var $form = $(form);
                        self.renderDates($form);
                    });
                });
            },

            renderDates: function($form) {
                if(!$.isjQuery($form)) return this;

                var self = this,
                    $checkInInput = $form.find('input[name="mphb_check_in_date"]'),
                    $checkOutInput = $form.find('input[name="mphb_check_out_date"]');

                if($checkInInput.length) this.renderDateForField($checkInInput, $form);
                if($checkOutInput.length) this.renderDateForField($checkOutInput, $form);

                return this;
            },

            bindEvents: function($form) {
                var self = this;
            },

            renderDateForField: function($field, $form) {
                var $column = $field.closest('.form-col'),
                    $markupField = $column.find('.milenia-field-datepicker'),
                    $day = $column.find('.milenia-field-datepicker-day'),
                    $monthYear = $column.find('.milenia-field-datepicker-month-year'),
                    $dayName = $column.find('.milenia-field-datepicker-dayname'),
                    $srcField = $field.filter('input[name="mphb_check_in_date"]').length ? $form.find('input[name="mphb_check_in_date"][type="hidden"]') : $form.find('input[name="mphb_check_out_date"][type="hidden"]'),
                    momentInstance;

                if(!$srcField.length || !$markupField.length) return;

                momentInstance = moment($srcField.get(0).getAttribute('value'));

                if($markupField.hasClass('milenia-field-datepicker--style-1')) {
                    if($day.length) {
                        $day.text(momentInstance.date());
                    }

                    if($monthYear.length) {
                        $monthYear.text(momentInstance.format('MMMM, YYYY'));
                    }

                    if($dayName.length) {
                        $dayName.text(momentInstance.format('dddd'));
                    }
                }
                else if($markupField.hasClass('milenia-field-datepicker--style-2') || $markupField.hasClass('milenia-field-datepicker--style-3') || $markupField.hasClass('milenia-field-datepicker--style-4')) {
                    $markupField.text(momentInstance.format('dddd Do MMMM, YYYY'));
                }

                return this;
            },

            _convertValue: function(value) {
                return value.split('/').reverse().join('-');
            },

            fillHiddenInputs: function($form) {
                var $checkInInput = $form.find('input[name="mphb_check_in_date"]:not([type="hidden"])'),
                    $checkOutInput = $form.find('input[name="mphb_check_out_date"]:not([type="hidden"])'),
                    $checkInInputHidden = $form.find('input[name="mphb_check_in_date"][type="hidden"]'),
                    $checkOutInputHidden = $form.find('input[name="mphb_check_out_date"][type="hidden"]'),
                    checkInValue,
                    checkOutValue;

                if($checkInInput.length && $checkInInputHidden.length) {
                    checkInValue = moment(this._convertValue($checkInInput.get(0).getAttribute('value')));

                    $checkInInputHidden.get(0).value = checkInValue.format('YYYY-MM-DD');
                    $checkInInputHidden.get(0).setAttribute('value', checkInValue.format('YYYY-MM-DD'));
                }

                if($checkOutInput.length && $checkOutInputHidden.length) {
                    checkOutValue = moment(this._convertValue($checkOutInput.get(0).getAttribute('value')));

                    $checkOutInputHidden.get(0).value = checkOutValue.format('YYYY-MM-DD');
                    $checkOutInputHidden.get(0).setAttribute('value', checkOutValue.format('YYYY-MM-DD'));
                }

                return this;
            }
        };

    /* ----------------------------------------
            End of Field Datepicker
     ---------------------------------------- */

    /* ----------------------------------------
            Hidden Filters
     ---------------------------------------- */

        App.modules.hiddenFilters = function() {
            $body.on('click.MileniaHiddenFilters', '.milenia-hidden-filters-show', function(e) {
                var $this = $(this),
                    $shownElement = $this.closest('.milenia-hidden-filters-shown'),
                    $hiddenElement,
                    $hiddenElementActionButton;

                if($shownElement.length) {
                    $hiddenElement = $shownElement.siblings('.milenia-hidden-filters-hidden');

                    $shownElement.removeClass('milenia-hidden-filters--visible').attr('aria-hidden', 'true');
                    $this.attr('aria-expanded', 'false');

                    if($hiddenElement.length) {
                        $hiddenElement.addClass('milenia-hidden-filters--visible').attr('aria-hidden', 'false');

                        $hiddenElementActionButton = $hiddenElement.find('.milenia-hidden-filters-hide');

                        if($hiddenElementActionButton.length) $hiddenElementActionButton.attr('aria-expanded', 'true');
                    }
                }
                e.preventDefault();
            }).on('click.MileniaHiddenFilters', '.milenia-hidden-filters-hide', function(e) {
                var $this = $(this),
                    $hiddenElement = $this.closest('.milenia-hidden-filters-hidden'),
                    $shownElement,
                    $shownElementActionButton;

                if($hiddenElement.length) {
                    $shownElement = $hiddenElement.siblings('.milenia-hidden-filters-shown');

                    $hiddenElement.removeClass('milenia-hidden-filters--visible').attr('aria-hidden', 'true');
                    $this.attr('aria-expanded', 'false');

                    if($shownElement.length) {
                        $shownElement.addClass('milenia-hidden-filters--visible').attr('aria-hidden', 'false');

                        $shownElementActionButton = $shownElement.find('.milenia-hidden-filters-show');

                        if($shownElementActionButton.length) $shownElementActionButton.attr('aria-expanded', 'true');
                    }
                }

                e.preventDefault();
            });
        };

    /* ----------------------------------------
            End of Hidden Filters
     ---------------------------------------- */

    /* ----------------------------------------
            Fancybox Album
     ---------------------------------------- */

        App.modules.fancyboxAlbum = {};

        App.modules.fancyboxAlbum.init = function() {
            $body.off('click.MileniaFancyboxAlbum').on('click.MileniaFancyboxAlbum', '[data-fancybox-album-src]', function(event) {
                var $this = $(this),
                    srcs;

                if($.fn.fancybox) {
                    srcs = $this.data('fancybox-album-src');
                    if(srcs) $.fancybox.open(srcs);
                }

                event.preventDefault();
            });
        };

    /* ----------------------------------------
            End of Fancybox Album
     ---------------------------------------- */

    /* ----------------------------------------
            Dropdown
     ---------------------------------------- */

        App.modules.dropdown = {};

        App.modules.dropdown.config = {
            uncloseable: '.milenia-dropdown, .select2-container--milenia',
            cssPrefix: 'milenia-',
            availableError: 30,
            rtl: App.RTL,
            classMap: {
                active: 'dropdown--opened',
                container: 'dropdown',
                title: 'dropdown-title',
                element: 'dropdown-element',
                leftPlaced: 'dropdown-element--x-left',
                rightPlaced: 'dropdown-element--x-right',
                topPlaced: 'dropdown-element--y-top'
            }
        };

        App.modules.dropdown.init = function(config) {
            if(this._initialized) return;

            if($.isPlainObject(config)) $.extend(true, this.config, config);

            Object.defineProperties(this, {
                activeClass: {
                    get: function() {
                        return this.config.cssPrefix + this.config.classMap.active;
                    }
                },
                containerClass: {
                    get: function() {
                        return this.config.cssPrefix + this.config.classMap.container;
                    }
                },
                titleClass: {
                    get: function() {
                        return this.config.cssPrefix + this.config.classMap.title;
                    }
                },
                elementClass: {
                    get: function() {
                        return this.config.cssPrefix + this.config.classMap.element;
                    }
                },
                rightPlacedClass: {
                    get: function() {
                        return this.config.cssPrefix + this.config.classMap.rightPlaced;
                    }
                },
                leftPlacedClass: {
                    get: function() {
                        return this.config.cssPrefix + this.config.classMap.leftPlaced;
                    }
                },
                topPlacedClass: {
                    get: function() {
                        return this.config.cssPrefix + this.config.classMap.topPlaced;
                    }
                },
                $dropdowns: {
                    get: function() {
                        return $('.' + this.containerClass);
                    }
                }
            });

            this._bindEvents();
        };

        App.modules.dropdown._bindEvents = function() {
            var self = this;

            $doc.off('click.MileniaDropdown').on('click.MileniaDropdown', function(e) {
                var $target = $(e.target);

                if(!$target.closest(self.config.uncloseable).length) {
                    self.close(self.$dropdowns);
                }
            }).on('keydown.MileniaDropdown', function(event) {
                if(event.keyCode && event.keyCode == 27) {
                    self.close(self.$dropdowns);
                }
            });

            $body.off('click.MileniaDropdown').on('click.MileniaDropdown', '.' + self.titleClass, function(e) {
                var $dropdown = $(this).closest('.' + self.containerClass),
                    $others = self.$dropdowns.not($dropdown);

                if($dropdown.length) {
                    self.toggle($dropdown);
                    e.preventDefault();
                }

                self.close($others);
            });

            this._initialized = true;
        };

        App.modules.dropdown.close = function($dropdowns) {
            if(!$.isjQuery($dropdowns, true)) return;

            $dropdowns.removeClass(this.activeClass)
                      .find('.' + this.elementClass)
                      .attr('aria-hidden', 'true')
                      .end()
                      .find('.' + this.titleClass)
                      .attr('aria-expanded', 'false');
        };

        App.modules.dropdown.open = function($dropdowns) {
            if(!$.isjQuery($dropdowns, true)) return;

            this.fixPosition($dropdowns);

            $dropdowns.addClass(this.activeClass)
                      .find('.' + this.elementClass)
                      .attr('aria-hidden', 'false')
                      .end()
                      .find('.' + this.titleClass)
                      .attr('aria-expanded', 'true');
        };

        App.modules.dropdown.fixPosition = function($dropdowns) {
            var self = this,
                $w = $(window);

            if(!$.isjQuery($dropdowns, true)) return;

            return $dropdowns.each(function(index, dropdown) {
                var $dropdown = $(dropdown),
                    $element = $dropdown.find('.' + self.elementClass),
                    dOffset;

                $element.removeClass(self.leftPlacedClass)
                        .removeClass(self.rightPlacedClass)
                        .removeClass(self.topPlacedClass);

                dOffset = $element.offset();

                // x
                if(dOffset.left - self.config.availableError < 0) {
                    $element.addClass(self.leftPlacedClass);
                }
                else if(dOffset.left + $element.outerWidth() + self.config.availableError > $w.width()) {
                    $element.addClass(self.rightPlacedClass);
                }

                // y
                if(dOffset.top + $element.outerHeight() + self.config.availableError > $w.scrollTop() + $w.height()) {
                    $element.addClass(self.topPlacedClass);
                }
            });
        };

        App.modules.dropdown.toggle = function($dropdowns) {
            if(!$.isjQuery($dropdowns, true)) return;
            var self = this;

            return $dropdowns.each(function(index, dropdown){
                var $dropdown = $(dropdown);

                if($dropdown.hasClass(self.activeClass)) self.close($dropdown);
                else self.open($dropdown);
            });
        };

    /* ----------------------------------------
            End of Dropdown
     ---------------------------------------- */
    /* ----------------------------------------
            Arctic Modal
     ---------------------------------------- */

        App.modules.arcticModals = {
             _config: {
                 type: 'html',
                 closeOnOverlayClick: true,
                 overlay: {
                     css: {
                         opacity: .8,
                         backgroundColor: '#000000'
                     }
                 },
                 clickableElements: null
             },
             init: function(config ) {

                 config = $.isPlainObject( config ) ? $.extend(true, {}, this._config, config) : this._config;

                 config = this._prepareCallbacks( config );

                 if( config && config.clickableElements ) {
                     $body.on('click.MileniaArcticModals', '.arcticmodal-container', function(e){
                         var $target = $(e.target);
                         if( !$target.closest( config.clickableElements ).length ) {
                             $.arcticmodal('close');
                         }
                     });
                 }

                 $('body').on('click.MileniaArcticModals', '[data-arctic-modal]', function(e) {

                     var $this = $(this);

                     if( $this.data('arctic-modal-type') == 'ajax' ) {
                         if(!$this.data('arctic-modal-ajax-action')) {
                             return;
                         }

                         $.arcticmodal($.extend(true, {}, config, {
                             type: 'ajax',
                             url: MileniaAJAXData.url,
                             ajax: {
                                 cache: false,
                                 dataType: 'html',
                                 data: {
                                     action: $this.data('arctic-modal-ajax-action'),
                                     data: $this.data('arctic-modal-ajax-data'),
                                     AJAX_token: MileniaAJAXData.AJAX_token
                                 },
                                 success: function(data, el, response) {
                                     data.body.html( response );
                                 }
                             }
                         }));
                     }
                     else {
                         $($this.data('arctic-modal')).arcticmodal(config);
                     }

                     e.preventDefault();
                 });
             },
             _prepareCallbacks: function(config) {
                var beforeOpenCallback = config.beforeOpen || function(){},
                	beforeCloseCallback = config.beforeClose || function(){},
                	afterOpenCallback = config.afterOpen || function(){},
                	afterCloseCallback = config.afterClose || function(){};

                config.beforeOpen = function() {

                	beforeOpenCallback.apply(this, Array.prototype.slice(arguments, 0));
                };

                config.afterOpen = function () {
                	if(App.LinkUnderliner) {
                        App.LinkUnderliner.init(this.body.find('a'), {
                            except: '.custom-logo-link',
                            exceptClass: 'milenia-ln--independent'
                        });
                	}
                	afterOpenCallback.apply(this, Array.prototype.slice(arguments, 0));
                };

                config.beforeClose = function(event) {

                	beforeCloseCallback.apply(this, Array.prototype.slice(arguments, 0));
                };

                config.afterClose = function(event) {
                    $body.css('overflow', '');

                	afterCloseCallback.apply(this, Array.prototype.slice(arguments, 0));
                };

                return config;

			}
        };

    /* ----------------------------------------
            End of Arctic Modal
     ---------------------------------------- */

    /* ----------------------------------------
        Alert Message Module
    ---------------------------------------- */

        App.modules.alertMessage = function(options) {
            if(!('Handlebars' in window)) return;
            var config = {
                target: $body.children().last(),
                type: 'info',
                timeout: 4000
            };
            config = options && $.isPlainObject(options) ? $.extend(true, {}, config, options) : config;

            var template =
                '<div class="milenia-alert-box milenia-alert-box--{{type}}" style="display: none;">\
                    <div class="milenia-alert-box-inner">\
                        {{message}}\
                    </div>\
                </div>';

            var messageBox = $(Handlebars.compile(template)(config));
            messageBox.data('timeOut', setTimeout(function(){
                messageBox.stop().slideUp({
                    duration: 350,
                    easing: 'linear',
                    complete: function() {
                        $(this).remove();
                        App.helpers.updateGlobalNiceScroll();
                    },
                    step: function() {
                        var $this = $(this),
                            $niceScrolled = $this.closest('.milenia--nice-scrolled');

                        if($niceScrolled.length) {
                            $niceScrolled.getNiceScroll().resize();
                        }
                        App.helpers.updateGlobalNiceScroll();

                    }
                });
            }, config.timeout)).insertAfter(config.target).stop().slideDown({
                duration: 350,
                easing: 'linear',
                step: function() {
                    var $this = $(this),
                        $niceScrolled = $this.closest('.milenia--nice-scrolled');

                    if($niceScrolled.length) {
                        $niceScrolled.getNiceScroll().resize();
                    }
                    App.helpers.updateGlobalNiceScroll();

                },
                completer: function() {
                    App.helpers.updateGlobalNiceScroll();
                }
            });
        };

    /* ----------------------------------------
        End of Alert Message Module
    ---------------------------------------- */


    /* ----------------------------------------
            Section Module
     ---------------------------------------- */

        App.modules.Section = {};
        App.modules.Section._$collection = $();
        App.modules.Section.config = {
            cssPrefix: 'milenia-',
            resizeDelay: 10,
            boddyPaddings: false,
            classMap: {
                loading: 'section--loading',
                stretched: 'section--stretched',
                stretchedContent: 'section--stretched-content',
                stretchedContentNoPadding: 'section--stretched-content-no-px',
                bgColorElementClass: 'colorizer-bg-color',
                bgImageElementClass: 'colorizer-bg-image'
            }
        };

        Object.defineProperties(App.modules.Section, {
            bgColorElementClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.bgColorElementClass;
                }
            },
            bgImageElementClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.bgImageElementClass;
                }
            },
            stretchedClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.stretched;
                }
            },
            stretchedContentClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.stretchedContent;
                }
            },
            stretchedContentNoPaddingClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.stretchedContentNoPadding;
                }
            },
            loadingClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.loading;
                }
            }
        });

        App.modules.Section.changeConfig = function(config) {
            return $.extend(true, this.config, config);
        };

        App.modules.Section.init = function($collection) {
            var self = this;

            if(!$.isjQuery($collection, true)) return;

            if(!this._bindedGlobalEvents) this._bindGlobalEvents();

            return $collection.each(function(index, section){
                var $section = $(section);

                if(self._$collection.filter($section).length) return;

                self.build($section);
                self._$collection = self._$collection.add($section);
            });
        };

        App.modules.Section._bindGlobalEvents = function () {
            var self = this;

            $(window).on('resize.App.modules.Section', function() {
                if(self._resizeTimeOutId) clearTimeout(self._resizeTimeOutId);

                self._resizeTimeOutId = setTimeout(function(){
                    self.rebuild();
                }, self.config.resizeDelay);
            });
        };

        App.modules.Section.rebuild = function() {
            var self = this;

            return this._$collection.each(function(index, section){
                var $section = $(section);

                self.reset($section).build($section);
            });
        };

        App.modules.Section.reset = function($section) {
            if(!$.isjQuery($section, true)) return;

            $section.css({
                'margin-left': '',
                'margin-right': ''
            });

            return this;
        };

        App.modules.Section.build = function($section) {
            if(!$.isjQuery($section, true)) return;

            if($section.hasClass(this.stretchedClass)) {
                this.stretch($section);
            }
            else if($section.hasClass(this.stretchedContentClass) || $section.hasClass(this.stretchedContentNoPaddingClass)) {
                this.stretchContent($section);
            }

            return this;
        };

        App.modules.Section.getDocumentGeometry = function() {
            return {
                'padding-left': parseInt($body.css('padding-left'), 10),
                'padding-right': parseInt($body.css('padding-right'), 10)
            };
        };

        App.modules.Section.stretch = function($section) {
            var $bgs, xOffsetDiff, documentGeometry;

            if(!$.isjQuery($section, true)) return;

            $bgs = $section.find('.' + this.bgColorElementClass + ', .' + this.bgImageElementClass);

            if(!$bgs.length) return;

            xOffsetDiff = $section.offset().left;
            documentGeometry = this.getDocumentGeometry();

            if(xOffsetDiff > 0) {
                $bgs.css({
                    left: (xOffsetDiff - documentGeometry['padding-left']) / -1,
                    right: (xOffsetDiff - documentGeometry['padding-right']) / -1
                });
            }

            $section.removeClass(this.loadingClass).trigger('stretched.milenia.Section', [$section]);

            return $section;
        };

        App.modules.Section.stretchContent = function($section) {
            var xOffsetDiff, documentGeometry;

            if(!$.isjQuery($section) || !$section.length) return;

            xOffsetDiff = $section.offset().left;
            documentGeometry = this.getDocumentGeometry();

            if(xOffsetDiff > 0) {
                $section.css({
                    'margin-left': (xOffsetDiff - documentGeometry['padding-left']) / -1,
                    'margin-right': (xOffsetDiff - documentGeometry['padding-right']) / -1
                });
            }

            $section.removeClass(this.loadingClass).trigger('stretched.milenia.Section', [$section]);

            return $section;
        };

     /* ----------------------------------------
            End of Section Module
      ---------------------------------------- */

     /* ----------------------------------------
            Nice Scroll updater
      ---------------------------------------- */

        App.helpers.updateGlobalNiceScroll = function() {
            $body.getNiceScroll().resize();
        };

     /* ----------------------------------------
            End of Nice Scroll updater
      ---------------------------------------- */

     /* ----------------------------------------
            Footer Widgets Settings
      ---------------------------------------- */

        App.helpers.footerWidgetsSettings = function(data) {
            if(!$.isPlainObject(data) || !data) return;

            var $section, $widgets, $configurableWidget, widgetsOptions;

            for(var sectionId in data) {
                $section = $('#milenia-' + sectionId);
                $widgets = $section.find('.milenia-widget');
                $configurableWidget;
                widgetsOptions = data[sectionId];

                if(!$widgets.length) break;

                for(var j in widgetsOptions)
                {
                    // $configurableWidget = $widgets.eq(widgetsOptions[j]['index'] - 1);
                    $configurableWidget = $widgets.eq(j);

                    if($configurableWidget.length) {
                        if('horizontal' in widgetsOptions[j]) {
                            $configurableWidget.addClass('milenia-widget--'+ widgetsOptions[j]['horizontal']['default'] +'-aligned')
                                   .addClass('milenia-widget--'+ widgetsOptions[j]['horizontal']['custom'] +'-aligned-' + widgetsOptions[j]['horizontal']['custom-breakpoint']);
                        }

                        if('vertical' in widgetsOptions[j]) {
                            $configurableWidget.addClass('milenia-widget--'+ widgetsOptions[j]['vertical']['default'] +'-valigned')
                                   .addClass('milenia-widget--'+ widgetsOptions[j]['vertical']['custom'] +'-valigned-' + widgetsOptions[j]['vertical']['custom-breakpoint']);
                        }

                        if('lists-direction' in widgetsOptions[j]) {
                            $configurableWidget.addClass('milenia-widget--list-' + widgetsOptions[j]['lists-direction']);
                        }
                    }
                }
            }
        };

     /* ----------------------------------------
            End of Footer Widgets Settings
      ---------------------------------------- */

     /* ----------------------------------------
            ID Randomizer
      ---------------------------------------- */

        App.helpers.getRandomId = function(idPart) {
            if(!('ids' in App._localCache)) App._localCache['ids'] = [];
            idPart = idPart || 'identifier';

            var id = idPart + '-' + +(new Date());

            if(App._localCache['ids'].indexOf(id) != -1) {
                id = App.helpers.getRandomId(idPart);
            }

            App._localCache['ids'].push(id);

            return id;
        };

     /* ----------------------------------------
            End of ID Randomizer
      ---------------------------------------- */

    /* ----------------------------------------
        Video Wrapper
    ---------------------------------------- */

          App.helpers.wrapYoutube = function( collection ) {
              if( !collection || !collection.length ) return;

              return collection.each(function(i, el){
                  var $el = $(el),
                      $parent = $el.parent();

                  if( !$parent.hasClass('milenia-responsive-iframe') ) {
                      $el.wrap('<div class="milenia-responsive-iframe"></div>');
                  }
              });
          }

    /* ----------------------------------------
        End of Video Wrapper
    ---------------------------------------- */

     /* ----------------------------------------
            PageStretcher
      ---------------------------------------- */

        App.helpers.PageStretcher = {};
        App.helpers.PageStretcher.config = {
            target: null,
            ignore: null,
            both: false
        };

        App.helpers.PageStretcher.init = function(config) {
            this.$body = $('body');
            this.$html = $('html');
            this.config = $.isPlainObject(config) ? $.extend(true, {}, this.config, config) : this.config;

            if(this.config.target === null) return;

            this.$ignore = $(this.config.ignore);
            this.$target = $(this.config.target);

            if(!this.$target.length) return;

            this.defaultTargetPT = parseInt(this.$target.css('padding-top'), 10);
            this.defaultTargetPB = parseInt(this.$target.css('padding-bottom'), 10);

            this.bindEvents().stretch();
        };

        App.helpers.PageStretcher.updatePageState = function() {
            var self = this,
                bodyPT,
                bodyPB;

            this.reset();

            this.superfluous = 0;

            if(this.$ignore.length) {
                this.$ignore.each(function(index, element) {
                    self.superfluous += $(element).outerHeight();
                });
            }

            this.superfluous -= parseInt(this.$html.css('margin-top'), 10);

            this.windowHeight = $(window).height();
            this.bodyHeight = this.$body.outerHeight();

            return this;
        };

        App.helpers.PageStretcher.stretch = function() {
            var self = this,
                difference;

            if(null !== this.config.target) {
                this.updatePageState();

                if(this.windowHeight > this.bodyHeight) {
                    if(this.config.both) {
                        difference = self.windowHeight - self.bodyHeight + self.defaultTargetPB + self.defaultTargetPT - self.superfluous;

                        this.$target.css({
                            'padding-top': difference / 2,
                            'padding-bottom': difference / 2
                        });
                    }
                    else {
                        this.$target.css({
                            'padding-bottom': self.windowHeight - self.bodyHeight + self.defaultTargetPB - self.superfluous
                        });
                    }
                }
                else {
                    this.reset();
                }
            }
            return this;
        };

        App.helpers.PageStretcher.bindEvents = function() {
            var timeOutId,
                self = this;

            $(window).off('resize.MileniaPageStretcher').on('resize.MileniaPageStretcher', function(event) {
                if(timeOutId) clearTimeout(timeOutId);

                timeOutId = setTimeout(function() {
                    self.stretch();
                }, 300);
            });

            return this;
        };

        App.helpers.PageStretcher.reset = function() {
            var self = this;

            if(this.$target.length) {
                this.$target.css({
                    'padding-top': self.defaultTargetPT,
                    'padding-bottom': self.defaultTargetPB
                });
            }
        };

     /* ----------------------------------------
            End of PageStretcher
      ---------------------------------------- */


     /* ----------------------------------------
            Colorizer
      ---------------------------------------- */

        App.helpers.Colorizer = {};
        App.helpers.Colorizer._$collection = $();
        App.helpers.Colorizer.config = {
            cssPrefix: 'milenia-',
            classMap: {
                bgColorElement: 'colorizer-bg-color',
                bgImageElement: 'colorizer-bg-image',
                videoContainer: 'vc_video-bg-container',
                bgVideoElement: 'vc_video-bg',
                VCParallaxContainer: 'vc_parallax',
                VCParallax: 'vc_parallax-inner',
                VCRow: 'vc_row',
                parallax: 'colorizer--parallax'
            },
            afterInit: function() {}
        };

        Object.defineProperties(App.helpers.Colorizer, {
            bgColorElementClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.bgColorElement;
                }
            },
            bgImageElementClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.bgImageElement;
                }
            },
            bgVideoElementClass: {
                get: function() {
                    return this.config.classMap.bgVideoElement;
                }
            },
            parallaxClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.parallax;
                }
            }
        });

        /**
         *
         * @param {jQuery} $collection - collection of elements to colorize
         * @returns {jQuery} $collection
         */
        App.helpers.Colorizer.init = function($collection, config) {
            var self = this;

            if(!$.isjQuery($collection, true)) return $collection;

            this.config = $.extend(true, {}, this.config, config);

            $collection.each(function(index, element) {
                var $element = $(element);

                if(self._$collection.filter($element).length) return;

                self.initElement($element);
                self._$collection = self._$collection.add($element);
            });

            this.config.afterInit.call(this);

            return $collection;
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {Object} Colorizer
         */
        App.helpers.Colorizer.add = function($element) {
            if(!this._$collection.filter($element).length) {
                this._$collection = this._$collection.add($element);
            }

            return this;
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {Object} Colorizer
         */
        App.helpers.Colorizer.initElement = function($element) {
            if(this.hasBGVideoElement($element)) {
                this.prepareVideoElement($element);
            }

            if(this.hasVCParallax($element)) {
                this.prepareParallaxElement($element);
            }

            if(this.hasBGColorElement($element)) {
                this.appendBGColorElement($element);
            }

            if(!this.hasBGImageElement($element) && $element.data('bg-image-src') && !this.hasVCParallax($element)) {
                this.appendBGImageElement($element);
            }

            return this;
        };

        /**
         *
         * @returns {Object} Colorizer
         */
        App.helpers.Colorizer.update = function() {
            var self = this;

            this._$collection.each(function(index, element) {
                var $element = $(element);
                self.reset($element).initElement($element);
            });

            return this;
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {Object} Colorizer
         */
        App.helpers.Colorizer.reset = function($element) {
            var $bgElements = $element.find('.' + this.bgColorElementClass + ', .' + this.bgImageElementClass);

            if($bgElements.length) $bgElements.remove();

            return this;
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {Boolean}
         */
        App.helpers.Colorizer.hasVCParallax = function($element) {
            return $element.hasClass(this.config.classMap.VCParallaxContainer) && !$element.hasClass(this.config.classMap.videoContainer);
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {Boolean}
         */
        App.helpers.Colorizer.hasBGColorElement = function($element) {
            return $element.data('bg-color') || $element.is('[class*="' + this.config.cssPrefix + 'colorizer--scheme-"]');
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {Boolean}
         */
        App.helpers.Colorizer.hasBGVideoElement = function($element) {
            return $element.hasClass(this.config.classMap.videoContainer);
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {Boolean}
         */
        App.helpers.Colorizer.hasBGImageElement = function($element) {
            return $element.children('.' + this.bgImageElementClass).length;
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {jQuery}
         */
        App.helpers.Colorizer.prepareVideoElement = function($element) {
            var self = this,
                opacity = $element.data('bg-video-opacity'),
                $videoElement;

            if(!opacity) return $element;

            $element.data('MileniaIntervalID', setInterval(function(){
                $videoElement = $element.find('.' + self.bgVideoElementClass);
                if($videoElement.length) {
                    $videoElement.css('opacity', opacity);
                    clearInterval($element.data('MileniaIntervalID'));
                }
            }, 100));

            return $element;
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {jQuery}
         */
        App.helpers.Colorizer.prepareParallaxElement = function($element) {
            var self = this,
                opacity = $element.data('bg-image-opacity'),
                $imageElement;

            if(!opacity) return $element;


            $element.data('MileniaIntervalID', setInterval(function(){
                $imageElement = $element.find('.' + self.config.classMap.VCParallax);
                if($imageElement.length) {
                    $imageElement.css('opacity', opacity);
                    clearInterval($element.data('MileniaIntervalID'));
                }
            }, 100));

            return $element;
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {jQuery}
         */
        App.helpers.Colorizer.appendBGColorElement = function($element) {
            var self = this,
                backgroundColorData = $element.data('bg-color'),
                $bgColorElement = $('<div></div>', {
                    class: self.bgColorElementClass
                });

            if(backgroundColorData) {
                $bgColorElement.css('background-color', backgroundColorData);
            }

            return $element.prepend($bgColorElement);
        };

        /**
         *
         * @param {jQuery} $element
         * @returns {jQuery}
         */
        App.helpers.Colorizer.appendBGImageElement = function($element) {
            var self = this,
                src = $element.data('bg-image-src'),
                bgImageOpacityData = $element.data('bg-image-opacity'),
                $bgImageElement = $('<div></div>', {
                    class: self.bgImageElementClass
                });


            $bgImageElement.css({
                'background-image':  'url("'+src+'")'
            });

            if($element.hasClass(this.config.classMap.VCRow)) {
                $bgImageElement.css({
                    'background-repeat': $element.css('background-repeat'),
                    'background-attachment': $element.css('background-attachment'),
                    'background-size': $element.css('background-size'),
                    'background-position': $element.css('background-position')
                });
            }

            if(bgImageOpacityData) {
                $bgImageElement.css('opacity', bgImageOpacityData);
            }

            $element.prepend($bgImageElement);

            return $element;
        };

     /* ----------------------------------------
            End of Colorizer
      ---------------------------------------- */


     /* ----------------------------------------
            Breadcrumb
      ---------------------------------------- */

        App.helpers.Breadcrumb = {};
        App.helpers.Breadcrumb.$collection = $();
        App.helpers.Breadcrumb.$w = $(window);

        App.helpers.Breadcrumb.config = {
            until: 767,
            cssPrefix: 'milenia-',
            resizeTimeoutDelay: 10,
            classMap: {
                bgColorElement: 'colorizer-bg-color',
                bgImageElement: 'colorizer-bg-image'
            }
        };

        Object.defineProperties(App.helpers.Breadcrumb, {
            bgColorElementClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.bgColorElement;
                }
            },
            bgImageElementClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.bgImageElement;
                }
            },
            bgsSelectors: {
                get: function() {
                    return '.' + this.bgColorElementClass + ', .' + this.bgImageElementClass;
                }
            }
        });

        App.helpers.Breadcrumb.init = function($breadcrumbs) {
            var self = this;

            if(!$.isjQuery($breadcrumbs, true)) return;

            this._bindEvents();

            $breadcrumbs.each(function(index, element) {
                var $element = $(element);

                if(self.$collection.filter($element).length) return;

                self.initCertainElement($element);
            });
        };

        App.helpers.Breadcrumb._bindEvents = function() {
            var self = this;

            if(!this._eventsBinded) {
                $body.on('spaceremoved.milenia.stickysection', function() {
                    self.$collection.each(function(index, element){
                        self.stretch($(element));
                    });
                });

                this.$w.on('resize', function() {
                    if(self._resizeTimeOutId) clearTimeout(self._resizeTimeOutId);

                    self._resizeTimeOutId = setTimeout(function(){
                        self.$collection.each(function(index, element){
                            self.stretch($(element));
                        });
                    }, self.config.resizeTimeoutDelay);
                });
            }
        };

        App.helpers.Breadcrumb.initCertainElement = function($breadcrumb) {
            this.$collection = this.$collection.add($breadcrumb);

            this.stretch($breadcrumb);
        };

        App.helpers.Breadcrumb.stretch = function($breadcrumb) {
            var $prev = $breadcrumb.prev(),
                prevOH,
                $bgs,
                $stickedSections;

            if($.isjQuery($prev, true)) {
                $prev.removeClass('milenia-header--transparent-single');

                prevOH = $prev.outerHeight();
                $bgs = $breadcrumb.find(this.bgsSelectors);
                $stickedSections = $('.milenia-header-section--sticked');

                if($bgs.length) {
                    $bgs.css({
                        top: -prevOH
                    });
                }
                if($stickedSections.length) {
                    $bgs.css({
                        top: $stickedSections.outerHeight() * -1
                    });
                }
            }

            return $breadcrumb;
        };

     /* ----------------------------------------
            End of Breadcrumb
      ---------------------------------------- */

    /* ----------------------------------------
        Sharer
    ---------------------------------------- */

        App.helpers.sharer = function() {
            var $sharers = $('[class*="milenia-sharer--"]');

            if(!$sharers.length) return;

            $sharers.each(function(index, element) {
                var $element = $(element);

                if($element.hasClass('milenia-sharer--facebook')) {
                    $element.attr('href', 'http://www.facebook.com/sharer.php?m2w&s=100&p[url]='+$element.data('sharer-url')+'&p[images][0]='+$element.data('sharer-thumbnail')+'&p[title]='+$element.data('sharer-title'));
                }
                else if($element.hasClass('milenia-sharer--twitter')) {
                    $element.attr('href', 'https://twitter.com/intent/tweet?text='+$element.data('sharer-text')+'&url='+$element.data('sharer-url'));
                }
                else if($element.hasClass('milenia-sharer--google-plus')) {
                    $element.attr('href', 'https://plus.google.com/share?url='+$element.data('sharer-url'));
                }
                else if($element.hasClass('milenia-sharer--pinterest')) {
                    $element.attr('href', 'https://pinterest.com/pin/create/button/?url='+$element.data('sharer-url')+'&media='+$element.data('sharer-media')+'&description='+$element.data('sharer-description'));
                }
            });

        };

    /* ----------------------------------------
        End of Share
    ---------------------------------------- */

    /* ----------------------------------------
        WPRowCSS
    ---------------------------------------- */

        App.helpers.WPRowCSS = function() {
            var $rows = $('[data-css-row]'),
                $mileniaInlineStyle = $('style#milenia-style-inline-css');

            if($rows.length && $mileniaInlineStyle.length) {
                $rows.each(function(index, row){
                    var $row = $(row);

                    if($row.data('MileniaCSSInitialized')) return;

                    $mileniaInlineStyle.text($mileniaInlineStyle.text() + $row.data('css-row'));

                    $row.removeAttr('data-css-row');

                    $row.data('MileniaCSSInitialized', true);
                });
            }
        };

    /* ----------------------------------------
        End of WPRowCSS
    ---------------------------------------- */

    /* ----------------------------------------
        Critical Error
    ---------------------------------------- */

        App.helpers.showCriticalFullScreenMessage = function(config) {
            var _config = {
                after: '',
                before: '',
                content: '',
                cssPrefix: 'milenia-',
                cssClass: ''
            },
            template = '<div class="%cssPrefix%fullscreen-message %cssClass% %cssPrefix%aligner">\
                            <div class="%cssPrefix%aligner-outer">\
                                <div class="%cssPrefix%aligner-inner">\
                                    <div class="%cssPrefix%fullscreen-message-before">%before%</div>\
                                    <div class="%cssPrefix%fullscreen-message-content">%content%</div>\
                                    <div class="%cssPrefix%fullscreen-message-after">%after%</div>\
                                </div>\
                            </div>\
                        </div>';


            config = $.extend(_config, config);

            for(var option in config) {
                template = template.replace(new RegExp('%' + option + '%', 'g'), config[option]);
            }

            $body.html('').addClass(config.cssPrefix + 'body--has-critical-fullscreen-message').append(template);
        };

    /* ----------------------------------------
        End of Critical Error
    ---------------------------------------- */


      /* ----------------------------------------
            Dynamic background image
       ---------------------------------------- */

            App.helpers.dynamicBgImage = function(collection) {
                collection = $.isjQuery(collection) ? collection : $('[data-bg-image-src]');
                if(!collection.length) return;

                return collection.each(function(i, el){
                    var $this = $(el);
                    if( !$this.data('bg-image-src') ) return;

                    $this.css('background-image', 'url("'+ $this.data('bg-image-src') +'")');
                });
            },

       /* ----------------------------------------
            End of Dynamic background image
        ---------------------------------------- */

        /* ----------------------------------------
            Booking Form V2V4
         ---------------------------------------- */

            App.helpers.bookingFormV2V4 = function() {
                $body.on('click.MileniaBookingFormV2V4', '.milenia-booking-form-wrapper--v2 .form-control, .milenia-booking-form-wrapper--v4 .form-control', function(event) {
                    var $current = $(this),
                        $form = $current.closest('.milenia-booking-form-wrapper--v2, .milenia-booking-form-wrapper--v4');

                    $current.addClass('form-control--over');

                    $form.find('.form-control').not($current).removeClass('form-control--over');
                });

                $doc.on('click.MileniaBookingFormV2V4', function(event) {
                    var $target = $(event.target);

                    if(!$target.closest('.milenia-booking-form-wrapper--v2, .milenia-booking-form-wrapper--v4').length) {
                        $('.milenia-booking-form-wrapper--v2 .form-control--over, .milenia-booking-form-wrapper--v4 .form-control--over').removeClass('form-control--over');
                    }
                });
            };

        /* ----------------------------------------
            End of Booking Form V2
         ---------------------------------------- */

         /* ----------------------------------------
             Booking Form V3
          ---------------------------------------- */

             App.helpers.bookingFormV3 = function() {
                 $body.on('click.MileniaBookingFormV3', '.milenia-booking-form-wrapper--v3 [class*="form-col"]', function(event) {
                     var $current = $(this),
                         $form = $current.closest('.milenia-booking-form-wrapper--v3');

                     $current.addClass('form-col--over');

                     $form.find('[class*="form-col"]').not($current).removeClass('form-col--over');
                 });

                 $doc.on('click.MileniaBookingFormV3', function(event) {
                     var $target = $(event.target);

                     if(!$target.closest('.milenia-booking-form-wrapper--v3').length) {
                         $('.milenia-booking-form-wrapper--v3 .form-col--over').removeClass('form-col--over');
                     }
                 });
             };

         /* ----------------------------------------
             End of Booking Form V3
          ---------------------------------------- */

         /* ----------------------------------------
             Toggled fields
          ---------------------------------------- */

            App.helpers.toggledFields = function() {
                $body.off('click.MileniaToggledFields').on('click.MileniaToggledFields', '.milenia-toggled-fields-invoker', function(event) {
                    var $this = $(this),
                        $fields = $this.siblings('.milenia-toggled-fields');

                    $this.toggleClass('milenia-toggled-fields-invoker--opened');

                    if($fields.length) {
                        $fields.stop().slideToggle({
                            duration: App.ANIMATIONDURATION,
                            easing: App.ANIMATIONEASING,
                        });
                    }
                });
            };

         /* ----------------------------------------
             End of Toggled fields
          ---------------------------------------- */

          /* ----------------------------------------
              MPHB Checkbox and Radio
           ---------------------------------------- */

            App.helpers.MPHBCheckboxNRadio = function() {
                var $radios = $('.mphb-room-rate-variant label');

                if($radios.length)
                {
                    $radios.each(function(index, el){
                        var $el = $(el),
                            $radio = $el.find('input[type="radio"]');

                        if($radio.length && $radio.is(':checked')) $el.addClass('mphb-radio-label--checked');
                    });
                }

                $('body').on('click.MPHB', '.mphb-checkbox-label, .mphb-room-rate-variant label', function(event) {
                    var $this = $(this),
                    $checkbox = $this.find('input[type="checkbox"]'),
                    $radio = $this.find('input[type="radio"]');

                    if($checkbox.length) {
                        $this[$checkbox.is(':checked') ? 'addClass' : 'removeClass']('mphb-checkbox-label--checked');
                    }
                    else if($radio.length) {
                        $this[$radio.is(':checked') ? 'addClass' : 'removeClass']('mphb-radio-label--checked');
                        $('.mphb-room-rate-variant label').not($this).removeClass('mphb-radio-label--checked');
                    }
                });
            };

        /* ----------------------------------------
            End of MPHB Checkbox
         ---------------------------------------- */

         /* ----------------------------------------
             Calendar Widget
          ---------------------------------------- */

            App.helpers.calendarWidget = function() {
                var $calendar = $('.calendar_wrap'),
                    $caption,
                    $prev,
                    $next;
                if(!$calendar.length || $calendar.hasClass('milenia-calendar-rendered')) return;

                $caption = $calendar.find('caption');

                if(!$caption.length) return;

                $prev = $calendar.find('#prev > a');
                $next = $calendar.find('#next > a');

                if($prev.length) {
                    $('<a></a>', {
                        class: 'calendar-caption-prev milenia-ln--independent',
                        html: App.RTL ? '<i class="icon icon-chevron-right"></i>' : '<i class="icon icon-chevron-left"></i>',
                        href: $prev.attr('href')
                    }).appendTo($caption);
                }

                if($next.length) {
                    $('<a></a>', {
                        class: 'calendar-caption-next milenia-ln--independent',
                        html: App.RTL ? '<i class="icon icon-chevron-left"></i>' : '<i class="icon icon-chevron-right"></i>',
                        href: $next.attr('href')
                    }).appendTo($caption);
                }

                $calendar.addClass('milenia-calendar-rendered');
            };

         /* ----------------------------------------
             End of Calendar Widget
          ---------------------------------------- */


        /* ----------------------------------------
            Owl Carousel helpers
         ---------------------------------------- */

             App.baseOwlSettings = {
                 items: 1,
                 margin: 30,
                 nav: true,
                 rtl: App.RTL,
                 navText: App.RTL ? ['<span class="icon icon-chevron-right"></span>', '<span class="icon icon-chevron-left"></span>'] : ['<span class="icon icon-chevron-left"></span>', '<span class="icon icon-chevron-right"></span>'],
                 dots: false,
                 autoplayHoverPause: true,
                 smartSpeed: App.ANIMATIONDURATION,
                 fluidSpeed: App.ANIMATIONDURATION,
                 autoplaySpeed: App.ANIMATIONDURATION,
                 navSpeed: App.ANIMATIONDURATION,
                 dotsSpeed: App.ANIMATIONDURATION,
                 dragEndSpeed: App.ANIMATIONDURATION,
                 onInitialized: function() {
                     App.helpers.updateGlobalNiceScroll();
                 }
             };

             App.helpers.owlAdaptive = function(collection) {

                 collection = collection ? collection : $('.owl-carousel');
                 if(!collection.length) return;


                 collection.each(function(i, el){

                     var $this = $(el);

                     $this.on('initialized.owl.carousel', function(e){

                       App.helpers.owlUpdateIsotopeParent($this, true);

                     });

                     $this.on('resized.owl.carousel', function(e){

                         App.helpers.owlContainerHeight($this, true);

                     });

                     $this.on('changed.owl.carousel', function(e){

                         App.helpers.owlContainerHeight($this, true);

                     });

                 });

             };

             App.helpers.owlContainerHeight = function(owl, resized) {

                 if(owl.hasClass('owl-carousel--vadaptive')) return;

                 setTimeout(function(){

                     var max = 0,
                         items = owl.find('.owl-item'),
                         activeItems = items.filter('.active').children();

                     items.children().css('height', 'auto');

                     activeItems.each(function(i, el){

                         var $this = $(el),
                             height = $this.outerHeight();

                         if(height > max) max = height;

                     });

                     owl.find('.owl-stage-outer').stop().animate({
                         height: max
                     }, {
                         duration: 150,
                         complete: function() {
                            if(!resized) return;
                            App.helpers.owlUpdateIsotopeParent($(this));
                         }
                     });

                 }, 20);

             };

             App.helpers.owlUpdateIsotopeParent = function($owl) {
                 var $isotope = $owl.closest('.milenia-grid--isotope');
                 if($isotope.length && $isotope.data('isotope')) $isotope.isotope('layout');
             };

             App.helpers.owlNav = function(owl) {

                 setTimeout(function(){

                     var settings = owl.data('owl.carousel').settings;
                     if(settings.autoplay || settings.loop) return;

                     var prev = owl.find('.owl-prev'),
                         next = owl.find('.owl-next');

                     if(owl.find('.owl-item').first().hasClass('active')) prev.addClass('milenia-disabled');
                     else prev.removeClass('milenia-disabled');

                     if(owl.find('.owl-item').last().hasClass('active')) next.addClass('milenia-disabled');
                     else next.removeClass('milenia-disabled');

                 }, 100);

             };

             App.helpers.owlSettings = function(settings) {

                 return $.extend(true, {}, App.baseOwlSettings, settings);
             };

             App.helpers.owlSync = {

				init: function() {

					this.collection = $('.owl-carousel[data-sync]');
					if(!this.collection.length) return;

					this.prepare();
				},

				prepare: function(){

					this.collection.each(function(i, el){

						var $this = $(el),
							sync = $($this.data('sync'));

						sync.on('changed.owl.carousel', function(e){

							var index = e.item.index;

							if(!sync.data('afterClicked')) $this.trigger('to.owl.carousel', [index, 350, true]);

							sync.data('afterClicked', false);

						});

						$this.on('prev.owl.carousel', function(){

							sync.trigger('prev.owl.carousel');

						});

						$this.on('next.owl.carousel', function(){

							sync.trigger('next.owl.carousel');

						});

						$this.on('click.owlSync', '.owl-item', function(e){

							e.preventDefault();

							var index = $(this).index();

							sync.data('afterClicked', true);

							sync.trigger('to.owl.carousel', [index, 350, true]);

						});

					});

				}

			};

         /* ----------------------------------------
            End of Owl Carousel helpers
          ---------------------------------------- */

         /* ----------------------------------------
               Rating
          ---------------------------------------- */

            function MileniaRating($element, config) {
                this.$element = $element;
                this.config = $.extend(MileniaRating.config, config);

                Object.defineProperties(this, {
                    bottomLevelElementClass: {
                        get: function() {
                            return this.config.cssPrefix + this.config.classMap.bottomLevelElement;
                        }
                    },
                    topLevelElementClass: {
                        get: function() {
                            return this.config.cssPrefix + this.config.classMap.topLevelElement;
                        }
                    }
                });
            };

            MileniaRating.config = {
                cssPrefix: 'milenia-',
                bottomLevelElements: '<i class="icon icon-star-empty"></i>',
                topLevelElements: '<i class="icon icon-star"></i>',
                estimate: 5,
                rtl: App.RTL,
                classMap: {
                    bottomLevelElement: 'rating-bottom-level',
                    topLevelElement: 'rating-top-level'
                }
            };

            MileniaRating.prototype.init = function() {
                this._buildMarkup();

                return this;
            };

            MileniaRating.prototype._buildMarkup = function() {
                var _self = this;

                if(this._markupBuilded) return;

                this.$element.css({
                    'position': 'relative',
                    'display': 'inline-block'
                });

                if(this.config.topLevelElements) {
                    this.$topLevelEl = $('<div></div>', {
                        class: _self.topLevelElementClass,
                        style: 'position: absolute; top: 0; right: 0; bottom: 0; left: 0; z-index: 2; white-space: nowrap; overflow: hidden;'
                    });

                    for(var i = 0; i < 5; i++) this.$topLevelEl.append(this.config.topLevelElements);

                    this.$element.append(this.$topLevelEl);
                }

                if(this.config.bottomLevelElements) {
                    this.$bottomLevelEl = $('<div></div>', {
                        class: _self.bottomLevelElementClass,
                        style: 'position: relative; z-index: 1;'
                    });

                    for(var i = 0; i < 5; i++) this.$bottomLevelEl.append(this.config.bottomLevelElements);

                    this.$element.append(this.$bottomLevelEl);
                }


                this.update(this.config.estimate);

                this._markupBuilded = true;

                this.$element.trigger('built.milenia.Rating', [this.$element]);
            };

            MileniaRating.prototype.update = function(estimate) {
                if(this.config.topLevelElements) {
                    this.$topLevelEl.css('width', (estimate / 5 * 100) + '%');
                }
                else {
                    if(this.config.bottomLevelElements) {
                        this.$bottomLevelEl.html('');
                        for(var i = 0; i < Math.round(estimate); i++) this.$bottomLevelEl.append(this.config.bottomLevelElements);
                    }
                }
            };

            App.helpers.rating = function($collection, config) {
                config = config || {};

                if(!$.isjQuery($collection) || !$collection.length) return $collection;

                return $collection.each(function(index, element) {
                    var $element = $(element),
                        elementConfig = $.extend(true, {}, config, {estimate: $element.data('estimate')});

                    if(!$element.data('Rating')) $element.data('Rating', new MileniaRating($element, elementConfig).init());
                });
            };



            App.helpers.ratingField = function($collection) {
                if(!$.isjQuery($collection)) return;

                $collection.on('click.MileniaRatingField', '.icon', function(event) {
                    var $icon = $(this),
                        $rating = $icon.closest('[data-estimate]'),
                        index = $icon.index() + 1,
                        Rating = $rating.data('Rating'),
                        $field = $rating.siblings('input[type="hidden"]');

                    if ( Rating ) {

                        index = App.RTL ? 6 - index : index;

                        Rating.update(index);

                        if ( $field.length ) {
                            $field.val(index);
                        }
                    }

                    event.preventDefault();
                    event.stopPropagation();
                });
            };

         /* ----------------------------------------
               End of Rating
          ---------------------------------------- */

         /* ----------------------------------------
               Touch hover emulator
          ---------------------------------------- */

            App.helpers.touchHoverEmulator = function($container, targetSelector, itemSelector) {
                if(!App.ISTOUCH || !$.isjQuery($container) || !$container.length) return;

                var hoverClass = 'milenia-touch-hover',
                    preventedClass = 'milenia-event-prevented';

                $container.on('click.touchHoverEmulator', targetSelector, function(event){
                    var $link = $(this),
                        $items,
                        $targets,
                        $item = $link.closest(itemSelector);

                    if($link.get(0).tagName.toUpperCase() != 'A') return;

                    $items = $container.find(itemSelector);
                    if($items.not($item).length) $items.not($item).removeClass(hoverClass);

                    $targets = $container.find(targetSelector);
                    if($targets.not($link).length) $targets.not($link).removeClass(preventedClass);

                    if(!$link.hasClass(preventedClass)) {
                        $link.addClass(preventedClass);
                        if($item.length) $item.addClass(hoverClass);

                        event.preventDefault();
                    }
                });
            };

         /* ----------------------------------------
               End Touch hover emulator
          ---------------------------------------- */


         /* ----------------------------------------
               Revolution slider helpers
          ---------------------------------------- */

            App.helpers.revArrowsOutside = function() {
                if(window.MileniaRevArrowsOutsideEvents) return;

                $body.on('click.revArrowsOutside', '.milenia-rev-arrows-prev, .milenia-rev-arrows-next', function(event) {
                    var $button = $(this),
                        $nav = $button.closest('.milenia-rev-arrows-outside'),
                        revApi;


                    if(!$nav.length) return;

                    revApi = window[$nav.data('rev-api')];

                    if(!revApi) return;

                    revApi[$button.hasClass('milenia-rev-arrows-prev') ? 'revprev' : 'revnext']();

                    event.preventDefault();
                });

                window.MileniaRevArrowsOutsideEvents = true;
            };

         /* ----------------------------------------
               End Revolution slider helpers
          ---------------------------------------- */

         /* ----------------------------------------
               gridOwl
          ---------------------------------------- */

            App.helpers.gridOwl = {
                _commonLayoutConfig: {
                    'columns-4': {
                        responsive: {
                            0: {
                                items: 1
                            },
                            768: {
                                items: 2
                            },
                            1200: {
                                items: 4
                            }
                        }
                    },
                    'columns-4-sidebar': {
                        responsive: {
                            0: {
                                items: 1
                            },
                            992: {
                                items: 2
                            },
                            1200: {
                                items: 3
                            }
                        }
                    },
                    'columns-3': {
                        responsive: {
                            0: {
                                items: 1
                            },
                            768: {
                                items: 2
                            },
                            1200: {
                                items: 3
                            }
                        }
                    },
                    'columns-3-sidebar': {
                        responsive: {
                            0: {
                                items: 1
                            },
                            992: {
                                items: 2
                            },
                            1200: {
                                items: 3
                            }
                        }
                    },
                    'columns-2': {
                        responsive: {
                            0: {
                                items: 1
                            },
                            768: {
                                items: 2
                            }
                        }
                    },
                    'columns-2-sidebar': {
                        responsive: {
                            0: {
                                items: 1
                            },
                            992: {
                                items: 2
                            }
                        }
                    }
                },
                _$collection: $(),
                _individualConfigs: {}
            };

            /**
             * Initializes the gridOwl helper
             * @param {jQuery} $collection
             *
             * @returns {jQuery}
             */
            App.helpers.gridOwl.init = function($collection) {
                var self = this;

                $collection = $.isjQuery($collection) ? $collection : $('.milenia-grid.owl-carousel');

                 $collection.each(function(index, element){
                    var $element = $(element);

                    if(self._$collection.filter($element).length) return;

                    self._$collection = self._$collection.add($element);
                });

                this.update();

                return $collection;
            };

            /**
             * Modifies config for the elements with parents that match specified selector.
             * @param {String} selector
             * @param {Object} config
             *
             * @returns {Object}
             */
            App.helpers.gridOwl.extendConfigFor = function(selector, config) {
                this._individualConfigs[selector] = config;

                return this;
            };

            /**
             * Adds new carousel to the collection
             *
             * @param {jQuery} $carousel
             *
             * @returns {Object}
             */
            App.helpers.gridOwl.add = function($carousel) {
                if($.isjQuery($carousel) && !this._$collection.filter($carousel).length) {
                    this._$collection = this._$collection.add($carousel);
                    this.update();
                }

                return this;
            };

            /**
             * Initializes not initialized carousels.
             *
             * @returns {Object}
             */
            App.helpers.gridOwl.update = function() {
                var self = this;

                this._$collection.each(function(index, element){
                    var $element = $(element),
                        config = {},
                        columnsCount,
                        layoutConfigProp;

                    if($element.data('owl.carousel')) return;

                    // detect layout settings
                    columnsCount = self._getColumnsCount($element);

                    if(columnsCount > 1) {
                        // check if sidebar
                        if($element.closest('.milenia-has-sidebar').length) {
                            layoutConfigProp = 'columns-' + columnsCount + '-sidebar';
                        }
                        else {
                            layoutConfigProp = 'columns-' + columnsCount;
                        }

                        $.extend(config, self._commonLayoutConfig[layoutConfigProp]);
                    }

                    for(var selector in self._individualConfigs) {
                        if($element.closest(selector).length) {
                            $.extend(config, self._individualConfigs[selector]);

                            if($element.closest('.milenia-has-sidebar').length) {
                                config.responsive = config.responsiveWithSidebar;
                            }
                        }
                    }


                    $element.owlCarousel(App.helpers.owlSettings(config));
                });

                return this;
            };

            /**
             * Returns amount of columns in the specified element.
             *
             * @param {jQuery} $element
             * @returns {Number}
             */
            App.helpers.gridOwl._getColumnsCount = function($element) {
                if($element.hasClass('milenia-grid--cols-4')) return 4;
                else if($element.hasClass('milenia-grid--cols-3')) return 3;
                else if($element.hasClass('milenia-grid--cols-2')) return 2;

                return 1;
            };

         /* ----------------------------------------
               End of gridOwl
          ---------------------------------------- */

         /* ----------------------------------------
               Full Screen Area
          ---------------------------------------- */

            App.helpers.fullScreenArea = {
            	init: function(config){

            		var self = this;

            		this.collection = $('.milenia-fullscreen-area');
            		if(!this.collection.length) return;

                    this.config = config || {};
            		this.defPaddingTop = parseInt(this.collection.css('padding-top'), 10);
            		this.defPaddingBottom = parseInt(this.collection.css('padding-bottom'), 10);

            		this.w = $(window);
                    this.$body = $body;

            		this.run();

            		this.w.on('resize.fullscreen', function() {
                        self.run();
                    });

            		return this.collection;

            	},

            	reset: function(){

            		if(!this.collection) return;

            		this.run();

            	},

            	updateDocumentState: function(){

            		var self = this;

            		this.collection.css({
            			'padding-top': self.defPaddingTop,
            			'padding-bottom': self.defPaddingBottom
            		});

            		this.cH = this.collection.outerHeight();

                    this.eH = this.config.except && this.config.except.length ? this.getTotalHeightOfExceptedElements() : 0;
                    this.documentPadding = parseInt(this.$body.css('padding-top'), 10) + parseInt(this.$body.css('padding-bottom'), 10);

                    if($body.hasClass('milenia-body--border-layout')) {
                        this.documentPadding += 60;
                    }

                    this.wH = this.w.height();

            	},

                getTotalHeightOfExceptedElements: function() {
                    return this.config.except.toArray().reduce(function(accumulator, currentValue, index, array){
                        return accumulator + $(currentValue).outerHeight();
                    }, 0);
                },

            	run: function(){

            		var self = this;

            		// this.updateDocumentState();

            		if(this.timeoutId) clearTimeout(this.timeoutId);

            		this.timeoutId = setTimeout(function(){

            			if(self.cH < self.wH){

            				var diff = (self.wH - self.cH) / 2;

            				self.collection.css({
            					'padding-top': diff + self.defPaddingTop - ((self.eH + self.documentPadding)/2),
            					'padding-bottom': diff + self.defPaddingBottom - ((self.eH + self.documentPadding)/2)
            				});

            			}

                        self.collection.addClass('milenia-fullscreen-area--ready');

            		}, 100);

            	}

            };

         /* ----------------------------------------
               End of Full Screen Area
          ---------------------------------------- */

         /* ----------------------------------------
               owlSyncTabbed
          ---------------------------------------- */

            App.helpers.owlSyncTabbed = {};
            App.helpers.owlSyncTabbed.init = function($collection) {
                var _self = this,
                    $w = $(window);

                if(!$.isjQuery($collection)) return;

                $collection.on('click', '.owl-item', this.changeTab);
                $collection.on('translated.owl.carousel', function(event) {
                    var $this = $(this),
                        OWL = $this.data('owl.carousel');

                    if($.isPlainObject(OWL.options.responsive)) {
                        for(var screenSize in OWL.options.responsive) {
                            if(OWL.options.responsive[screenSize]['items'] == 1 && $w.width() < 480) {
                                $this.find('.owl-item.active').trigger('click');
                            }
                        }
                    }

                });

                this.setCurrentSlideClass($collection);
            };

            App.helpers.owlSyncTabbed.changeTab = function(event) {
                var $this = $(this),
                    index = $this.index(),
                    $carousel = $this.closest('.owl-carousel[data-tabbed-sync]'),
                    $tabbedContainer;


                if($carousel.length) {
                    $tabbedContainer = $('#' + $carousel.data('tabbed-sync'));

                    if($tabbedContainer.length && $tabbedContainer.data('TabbedGrid')) {
                        $tabbedContainer.data('TabbedGrid').show(index);
                        $this.addClass('milenia-grid--tabbed-active').siblings().removeClass('milenia-grid--tabbed-active');
                    }
                }

                event.preventDefault();
                event.stopPropagation();
            };

            App.helpers.owlSyncTabbed.setCurrentSlideClass = function($collection) {
                var self = this;

                if(!$collection.length) return $();

                $collection.on('initialized.owl.carousel', function() {
                    self.setCurrentSlideClassToCarousel($(this));
                });

                return $collection.each(function(index, element) {
                    self.setCurrentSlideClassToCarousel($(element));
                });
            };

            App.helpers.owlSyncTabbed.setCurrentSlideClassToCarousel = function($element) {
                var $tabbedContainer = $('#' + $element.data('tabbed-sync')),
                    $items,
                    $currentItem;

                if($tabbedContainer.length) {
                    $items = $element.find('.owl-item');

                    if($items.length) {
                        $tabbedContainer.on('item.shown.tabbedgrid', function(event, $container){
                            if($element.data('synced')) return;

                            $currentItem = $items.eq($container.data('TabbedGrid').getCurrentItemIndex());
                            if($currentItem.length) $currentItem.addClass('milenia-grid--tabbed-active');

                            $element.data('synced', true);
                        });
                    }
                }
            };

         /* ----------------------------------------
               End of owlSyncTabbed
          ---------------------------------------- */

         /* ----------------------------------------
               Google Maps
          ---------------------------------------- */

            App.modules.googleMaps = {

				config: {
                    map_options: {
            			zoom: 16,
            			scrollwheel: false
            		},
            		locations: [],
            		generate_controls: false,
            		controls_on_map: false,
            		view_all: false
				},

				init: function(collection, config){

					var self = this;

					if( !window.Maplace ) return;

					this.collection = collection ? collection : $('.nv-map');
					if(!this.collection.length) return;

					this.MapPlaceCollection = [];

					if(config) $.extend(this.config, config);

					this.collection.each(function(i, el){
						var $this = $(el),
							options = {},
							MaplaceInstance;

						if($this.attr('data-locations')) options.locations = JSON.parse( $this.attr('data-locations') );
						if($this.attr('data-map-options')) options.map_options = JSON.parse( $this.attr('data-map-options') );

						options.map_div = '#' + $this.attr('id');

						MaplaceInstance = new Maplace($.extend(true, {}, self.config, options)).Load();
						$this.data('Maplace', MaplaceInstance);
						self.MapPlaceCollection.push(MaplaceInstance);
					});

					this.bindEvents();

				},

				bindEvents: function(){

					var self = this;

					$(window).on('resize.map', function(){

						if(self.mapTimeoutId) clearTimeout(self.mapTimeoutId);

					 	self.mapTimeoutId = setTimeout(function(){

						 	self.MapPlaceCollection.forEach(function(elem, index, arr){
						 		elem.Load();
						 	});

						 }, 100);

		            });
				}
            }

            /* ----------------------------------------
                End of Google Maps
            ---------------------------------------- */

    $.extend({
        isjQuery: function(element, elementExists) {
			if(element === undefined || element === null) return false;

			if(elementExists === undefined) {
				return element instanceof jQuery;
			}
			else {
				return $.isjQuery(element) && element.length;
			}
		}
    });

    $.fn.extend({
        jQueryImagesLoaded : function () {
		    var $imgs = this.find('img[src!=""]');

		    if (!$imgs.length) {return $.Deferred().resolve().promise();}

		    var dfds = [];

		    $imgs.each(function(){
		        var dfd = $.Deferred();
		        dfds.push(dfd);
		        var img = new Image();
		        img.onload = function(){dfd.resolve();};
		        img.onerror = function(){dfd.resolve();};
		        img.src = this.src;
		    });

		    return $.when.apply($,dfds);
		}
    });

    $doc.on('beforeClose', function(event) {
        if($(event.target).hasClass('milenia-modal')) {
            event.stopImmediatePropagation();
        }
    });

    $doc.ready(function() {
        App.afterDOMReady();
    });

    $(window).on('load', function() {

        DOMDfd.done(function() {
            App.afterOuterResourcesLoaded();
        });

    });

    function openMonkeysanTabByHash(hash, offsetFix) {
        var tab = this.nav.find('a[href="'+hash+'"]'),
            Tabs = this;

        if(hash && tab.length) {
            this.openTab($(hash));
            tab.parent().addClass(this.activeClass).siblings().removeClass(this.activeClass);

            if(offsetFix) {
                setTimeout(function() {
                    $('body, html').stop().animate({
                        scrollTop: $(window).scrollTop() - 150,
                        complete: function() {
                            Tabs.updateContainer();
                        }
                    })
                }, 100);
            }
        }
    };

    return App;

})(window.jQuery);
