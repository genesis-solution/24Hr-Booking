var MileniaAlertBox = (function($){
    'use strict';

    var _cache = {};

    /**
     * Constructor
     *
     * @param {Object} config
     * @constructor
     */
    function AlertBox(config, $element) {
        this.config = $.extend({
            cssPrefix: '',
            type: 'info',
            message: null,
            hasClose: true,
            closeBtnText: 'Close',
            anchor: $('body'),
            duration: 400,
            easing: 'linear',
            classMap: {
                container: 'alert-box',
                inner: 'alert-box-inner',
                close: 'alert-box-close',

                success: 'alert-box--success',
                warning: 'alert-box--warning',
                info: 'alert-box--info',
                error: 'alert-box--error'
            }
        }, config);

        Object.defineProperties(this, {
            containerClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.container + ' ' + (this.config.cssPrefix + this.config.classMap[this.config.type]);
                }
            },
            innerClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.inner;
                }
            },
            closeClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.close;
                }
            },
            $elementClose: {
                get: function() {
                    return this.$element.find('.' + this.closeClass);
                }
            }
        });

        if($element === undefined) this.$element = this._generateTemplate();
        else this.$element = $element;

        this._bindEvents();
    };

    /**
     * Generates the alert box markup.
     *
     * @returns {jQuery}
     */
    AlertBox.prototype._generateTemplate = function() {
        var self = this;

        this.$alert = $('<div></div>', {
            role: 'alert',
            class: this.containerClass,
            style: 'display: none'
        });

        this.$alertInner = $('<div></div>', {
            class: this.innerClass,
            text: this.config.message
        });

        if(this.config.hasClose) {
            this.$alertClose = $('<button></button>', {
                type: 'button',
                class: this.closeClass,
                text: this.config.closeBtnText
            });

            this.$alertInner.append(this.$alertClose);
        }

        this.$alert.append(this.$alertInner);

        return this.$alert;
    };

    /**
     * Binds necessary events.
     *
     * @returns undefined
     */
    AlertBox.prototype._bindEvents = function() {
        var self = this;

        if(this.config.hasClose) {
            this.$elementClose.on('click.AlertBox', function(e) {
                self.close();
                e.preventDefault();
            });
        }
    };

    /**
     * Inserts markup of the alert box into the page.
     *
     * @returns {jQuery}
     */
    AlertBox.prototype.render = function() {
        var self = this,
            $anchor = this.config.anchor;

        if(!$anchor.length) $anchor = $('body');

        $anchor.after(this.$element);

        setTimeout(function(){
            self.$element.slideDown({
                easing: self.config.easing,
                duration: self.config.duration,
                complete: function () {
                    $(document).trigger('pushed.milenia.alert', [$(this), self]);
                }
            });
        }, 4);

        return this.$element;
    };

    AlertBox.prototype.close = function () {
        var self = this;

        this.$element.slideUp({
            easing: self.config.easing,
            duration: self.config.duration,
            complete: function() {
                $(this).remove();
                $(document).trigger('closed.milenia.alert', [$(this), self]);
            }
        });
    };

    return {
        /**
         * Initializes existing alert boxes.
         *
         * @param {jQuery} $collection
         * @returns {jQuery}
         */
        init: function($collection, config) {
            return $collection.each(function(index, element) {
                var $element = $(element);

                $element.data('AlertBox', new AlertBox(config, $element));
            });
        },

        /**
         * Adds new alert box to the page.
         *
         * @param {Object} config
         * @returns {jQuery}
         */
        push: function (config) {
            var box = new AlertBox(config);

            box.$element.data('AlertBox', box);

            return box.render();
        }
    };

})(window.jQuery);
