var MileniaSidebarHidden = (function($){
    'use strict';

    var $body = $('body'),
        transitionTimeOutID;

    var _config = {
        cssPrefix: '',
        classMap: {
            sidebar: 'sidebar-hidden',
            invoker: 'sidebar-hidden-btn',
            close: 'sidebar-hidden-close',
            pushedContainer: 'page-wrapper',
            active: 'body--hidden-sidebar-opened',
            niceScrolled: '-nice-scrolled',
            sidebarActive: 'hidden-sidebar--opened'
        }
    };

    function HiddenSidebar(config) {
        this.config = $.isPlainObject(config) ? $.extend(true, {}, _config, config) : _config;

        Object.defineProperties(this, {
            sidebarClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.sidebar;
                }
            },
            sidebarSelector: {
                get: function() {
                    return '.' + this.sidebarClass;
                }
            },
            invokerClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.invoker;
                }
            },
            invokerSelector: {
                get: function() {
                    return '.' + this.invokerClass;
                }
            },
            niceScrolledClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.niceScrolled;
                }
            },
            niceScrolledSelector: {
                get: function() {
                    return '.' + this.niceScrolledClass;
                }
            },
            closeClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.close;
                }
            },
            closeSelector: {
                get: function() {
                    return '.' + this.closeClass;
                }
            },
            activeClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.active;
                }
            },
            sidebarActiveClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.sidebarActive;
                }
            }
        });

        this.initialize();
    };

    HiddenSidebar.prototype.initialize = function() {
        var self = this;

        $body.off('click.MileniaSidebarHidden').on('click.MileniaSidebarHidden', self.invokerSelector, function(e) {
            var $this = $(this),
                $sidebar = $($this.data('sidebar-hidden'));


            if(!$sidebar.length) return;

            self.toggle($this);
            e.preventDefault();
        }).on('click.MileniaSidebarHidden', self.closeSelector, function(e) {
            var $this = $(this),
                $sidebar = $($this.data('sidebar-hidden'));

            if(!$sidebar.length) return;

            self.hide($this);
            e.preventDefault();
        });

        $(document).off('keydown.MileniaSidebarHidden').on('keydown.MileniaSidebarHidden', function(event) {
            if(event.keyCode && event.keyCode == 27) {
                self.hideAll();
            }
        }).off('click.MileniaSidebarHidden').on('click.MileniaSidebarHidden', function(event){
            var $target = $(event.target);

            if(!$target.closest(self.sidebarSelector + ',' + self.invokerSelector + ', .fancybox-container, .arcticmodal-container, .arcticmodal-overlay').length) {
                self.hideAll();
            }
        });
    };

    HiddenSidebar.prototype.toggle = function ($invoker) {
        this.isOpened() ? this.hide($invoker) : this.show($invoker);
    };

    HiddenSidebar.prototype.isOpened = function() {
        return $body.hasClass(this.activeClass);
    };

    HiddenSidebar.prototype.hide = function($invoker) {
        var sidebarId = $invoker.data('sidebar-hidden'),
            $sidebar = $(sidebarId),
            $niceScrolled = $sidebar.closest(this.niceScrolledSelector).add($sidebar.find(this.niceScrolledSelector)),
            $controls = $('[data-sidebar-hidden="' + sidebarId + '"]'),
            transitionDuration = parseFloat($sidebar.css('transition-duration'), 10) * 1000;

        $body.removeClass(this.activeClass);
        $sidebar.removeClass(this.sidebarActiveClass);

        $controls.attr('aria-expanded', 'false');
        $sidebar.attr('aria-hidden', 'true');

        if($niceScrolled.length) {
            transitionTimeOutID = setInterval(function(){
                $niceScrolled.getNiceScroll().resize();
            },4);

            setTimeout(function() {
                clearInterval(transitionTimeOutID);
            }, transitionDuration);
        }
    };

    HiddenSidebar.prototype.show = function($invoker) {
        var sidebarId = $invoker.data('sidebar-hidden'),
            $sidebar = $(sidebarId),
            $niceScrolled = $sidebar.closest(this.niceScrolledSelector),
            $controls = $('[data-sidebar-hidden="' + sidebarId + '"]'),
            transitionDuration = parseFloat($sidebar.css('transition-duration'), 10) * 1000;

        $body.addClass(this.activeClass);
        $sidebar.addClass(this.sidebarActiveClass);

        $controls.attr('aria-expanded', 'true');
        $sidebar.attr('aria-hidden', 'false');

        if($niceScrolled.length) {
            transitionTimeOutID = setInterval(function(){
                $niceScrolled.getNiceScroll().resize();
            },4);

            setTimeout(function() {
                clearInterval(transitionTimeOutID);
            }, transitionDuration);
        }
    };

    HiddenSidebar.prototype.hideAll = function() {
        var $invokers = $(this.invokerSelector),
            self = this;

        if($invokers.length) $invokers.each(function(index, element){
            self.hide($(element));
        });
    };

    return HiddenSidebar;
})(window.jQuery);
