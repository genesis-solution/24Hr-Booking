var MileniaStickyHeaderSection = (function($){
    'use strict';

    if(!$) return;

    var _config = {
        cssPrefix: 'milenia-',
        resizeDelay: 50,
        classMap: {
            hiddenSection: 'header-section--sticky-hidden',
            active: 'header-section--sticked',
            container: 'header',
            spaceException: 'header--transparent-single',
            exceptionForSpaceException: 'header--breadcrumb-part'
        },
        breakpointMap: {
            xs: 0,
            sm: 576,
            md: 768,
            lg: 992,
            xl: 1200,
            xxl: 1380,
            xxxl: 1600
        },
        animationDuration: 400,
        animationEasing: 'linear'
    };

    var $body = $('body');

    function StickySection($element, config) {
        this.$element = $element;
        this.config = $.extend(true, {}, _config, (config || {}));

        this.updateDocumentState();

        Object.defineProperties(this, {
            activeClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.active;
                }
            },
            sticked: {
                get: function() {
                    return this.$element.hasClass(this.activeClass);
                }
            },
            $hiddenSections: {
                get: function() {
                    return this.$element.find('.' + this.config.cssPrefix + this.config.classMap.hiddenSection);
                }
            },
            isSuitable: {
                get: function() {
                    var $w = $(window);

                    return this.$element.hasClass(this.config.cssPrefix + 'header-section--sticky-xs') && $w.width() > this.config.breakpointMap.xs ||
                            this.$element.hasClass(this.config.cssPrefix + 'header-section--sticky-sm') && $w.width() > this.config.breakpointMap.sm ||
                            this.$element.hasClass(this.config.cssPrefix + 'header-section--sticky-md') && $w.width() > this.config.breakpointMap.md ||
                            this.$element.hasClass(this.config.cssPrefix + 'header-section--sticky-lg') && $w.width() > this.config.breakpointMap.lg ||
                            this.$element.hasClass(this.config.cssPrefix + 'header-section--sticky-xl') && $w.width() > this.config.breakpointMap.xl ||
                            this.$element.hasClass(this.config.cssPrefix + 'header-section--sticky-xxl') && $w.width() > this.config.breakpointMap.xxl ||
                            this.$element.hasClass(this.config.cssPrefix + 'header-section--sticky-xxxl') && $w.width() > this.config.breakpointMap.xxxl;
                }
            },
            $container: {
                get: function() {
                    return this.$element.closest('.' + this.config.cssPrefix + this.config.classMap.container);
                }
            },
            spaceExceptionClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.spaceException;
                }
            },
            exceptionForSpaceExceptionClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.exceptionForSpaceException;
                }
            }
        });

        this._bindEvents();
    };

    StickySection.prototype.updateDocumentState = function() {
        var $wpAdminBar = $('#wpadminbar');

        this.topOffset = this.$element.offset().top;
        this.defaultBodyPadding = parseInt($body.css('padding-top'), 10);

        if($wpAdminBar.length) {
            this.topOffset -= $wpAdminBar.outerHeight();
        }
        return this;
    };

    StickySection.prototype.sticky = function() {
        this.$element.addClass(this.activeClass);
        this._addSpace().hideSections();

        return this;
    };

    StickySection.prototype.unsticky = function () {
        this.$element.removeClass(this.activeClass);
        this._removeSpace().showSections();

        return this;
    };

    StickySection.prototype._addSpace = function() {
        if(!this.$container.hasClass(this.spaceExceptionClass) || this.$container.hasClass(this.exceptionForSpaceExceptionClass)) {
            $body.css('padding-top', this.defaultBodyPadding + this.$element.outerHeight()).trigger('spaceadded.milenia.stickysection');
        }

        return this;
    };

    StickySection.prototype._removeSpace = function() {
        if(!this.$container.hasClass(this.spaceExceptionClass) || this.$container.hasClass(this.exceptionForSpaceExceptionClass)) {
            $body.css('padding-top', this.defaultBodyPadding).trigger('spaceremoved.milenia.stickysection');
        }

        return this;
    };

    StickySection.prototype.hideSections = function() {
        var self = this,
            timeOutId;

        if(this.$hiddenSections.length) {
            this.$hiddenSections.stop().slideUp({
                easing: self.config.animationEasing,
                duration: self.config.animationDuration,
                step: function() {
                    self._addSpace();

                    if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.PageStretcher) {
                        window.Milenia.helpers.PageStretcher.stretch();
                    }
                },
                complete: function() {
                    $(this).css({
                        'height': ''
                    });

                    if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.PageStretcher) {
                        window.Milenia.helpers.PageStretcher.stretch();
                    }
                }
            });
        }

        return this.$hiddenSections;
    };

    StickySection.prototype.showSections = function() {
        var self = this;

        if(this.$hiddenSections.length) {
            this.$hiddenSections.stop().slideDown({
                easing: self.config.animationEasing,
                duration: self.config.animationDuration,
                step: function() {
                    var timeOutId;

                    if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.PageStretcher) {
                        window.Milenia.helpers.PageStretcher.stretch();
                    }
                },
                complete: function() {
                    $(this).css({
                        'height': '',
                        'display': ''
                    });

                    if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.PageStretcher) {
                        window.Milenia.helpers.PageStretcher.stretch();
                    }
                }
            });
        }

        return this.$hiddenSections;
    };

    StickySection.prototype._bindEvents = function() {
        var $w = $(window),
            self = this;

        $w.on('scroll.MileniaStickyHeaderSection', function(event) {

            if($w.scrollTop() > self.topOffset && !self.sticked && self.isSuitable) {
                self.sticky();
            }
            else if($w.scrollTop() <= self.topOffset && self.sticked && self.isSuitable) {
                self.unsticky();
            }
        })
        .on('resize.MileniaStickyHeaderSection', function(event) {
            if(self.resizeTimeOutId) clearTimeout(self.resizeTimeOutId);

            self.resizeTimeOutId = setTimeout(function() {
                self.unsticky().updateDocumentState();
                if($w.scrollTop() > self.topOffset && !self.sticked && self.isSuitable) self.sticky();

                if(!self.isSuitable) self.showSections();

            }, self.config.resizeDelay);
        })
        .trigger('scroll.MileniaStickyHeaderSection');
    };

    return StickySection;

})(window.jQuery);
