var MileniaTabbedGrid = (function($){
    'use strict';

    var _$collection = $(),
        _resizeTimeOutId;

    var _config = {
        cssPrefix: '',
        startAt: 0,
        easing: 'linear',
        duration: 400,
        classMap: {
            container: 'grid',
            item: 'grid-item',
            active: 'grid-item--current',
            loading: 'grid--tabbed-loading'
        }
    };

    $(window).on('resize.TabbedGrid', function() {
        if(_resizeTimeOutId) clearTimeout(_resizeTimeOutId);

        _resizeTimeOutId = setTimeout(function(){
            _$collection.each(function(index, element){
                $(element).data('TabbedGrid').resize();
            });
        }, 300);
    });

    /**
     * The TabbedGrid constructor.
     *
     * @param {jQuery} $element
     * @param {Object} config
     * @constructor
     */
    function TabbedGrid($element, config) {
        this.$element = $element;
        this.config = $.extend(true, {}, _config, config);

        Object.defineProperties(this, {
            containerClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.container;
                }
            },
            itemClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.item;
                }
            },
            activeItemClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.active;
                }
            },
            loadingClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.loading;
                }
            },
            $items: {
                get: function() {
                    return this.$element.find('.' + this.itemClass);
                }
            }
        });

        this._prepare().show(this.config.startAt, true);
    };

    /**
     * Prepares all necessary elements.
     *
     * @returns TabbedGrid
     */
    TabbedGrid.prototype._prepare = function () {
        this.$element.css('position', 'relative');

        this.$items.css({
            'overflow': 'hidden',
            'position': 'absolute',
            'top': 0,
            'right': 0,
            'left': 0
        });

        return this;
    };

    /**
     * Returns index of the current item.
     *
     * @returns {Number}
     */
    TabbedGrid.prototype.getCurrentItemIndex = function () {
        if(!this.$items.length) return 0;

        return this.$items.filter('.' + this.activeItemClass).index();
    };

    /**
     * Shows the slide with specified index
     * @param {Number} index
     * @param {Boolean} firstLoad
     * @returns undefined
     */
    TabbedGrid.prototype.show = function(index, firstLoad) {
        var $items = this.$items,
            self = this;

        this._hideItems($items.not($items.eq(index)));

        if(!$items.eq(index).length) {
            this.$element.stop().animate({
                height: 0
            }, {
                easing: self.config.easing,
                duration: self.config.duration,
                complete: function() {
                    if(firstLoad) self.$element.removeClass(self.loadingClass);
                }
            });
            return;
        }

        this._showItem($items.eq(index), firstLoad);
    };

    /**
     * Hides the specified collection of items.
     * @param {jQuery} $items
     * @returns {TabbedGrid}
     */
    TabbedGrid.prototype._hideItems = function($items) {
        var self = this;

        $items.removeClass(this.activeItemClass).css('z-index', 1).stop().animate({
            opacity: 0
        }, {
            easing: self.config.easing,
            duration: self.config.duration
        });

        return this;
    };

    /**
     * Shows the specified item.
     *
     * @param {jQuery} $item
     * @param {Boolean} firstLoad
     * @returns {TabbedGrid}
     */
    TabbedGrid.prototype._showItem = function($item, firstLoad) {
        var self = this;

        this.$element.stop().animate({
            height: $item.outerHeight()
        }, {
            easing: self.config.easing,
            duration: self.config.duration,
            complete: function() {
                if(firstLoad) self.$element.removeClass(self.loadingClass);

                $item.addClass(self.activeItemClass).stop().css('z-index', 2).animate({
                    opacity: 1
                }, {
                    easing: self.config.easing,
                    duration: self.config.duration,
                    complete: function() {
                        self.$element.trigger('item.shown.tabbedgrid', [self.$element]);
                    }
                });
            }
        });

        return this;
    };

    /**
     * Updates the container height.
     */
    TabbedGrid.prototype.resize = function() {
        var self = this;

        this.$element.stop().animate({
            height: self.$items.filter('.' + self.activeItemClass).outerHeight()
        }, {
            easing: self.config.easing,
            duration: self.config.duration,
            complete: function() {
                self.$element.removeClass(self.loadingClass);
                self.$element.trigger('grid.resized.tabbedgrid', [self.$element]);
            }
        });

        return this;
    };

    return {
        /**
         * Displays slide by specified index.
         * @param {jQuery} $collection
         * @returns {jQuery}
         */
        init: function($collection, config) {
            config = config || {};

            return $collection.each(function(index, element) {
                var $element = $(element);

                if($element.data('TabbedGrid')) return;

                $element.data('TabbedGrid', new TabbedGrid($element, config));
                _$collection = _$collection.add($element);
            });
        }
    };
})(window.jQuery);
