var MileniaEventsCalendar = (function($){
    'use srtict';

    var _config = {
        isTouch: false,
        breakpoint: 768,
        cssPrefix: '',
        rtl: getComputedStyle(document.body).direction === 'rtl',
        classMap: {
            event: 'events-event',
            tdEventTitle: 'events-month-event-title',
            eventTooltip: 'events-tooltip',
            dayNum: 'events-daynum',
            tdSelected: 'events-td--selected',
            eventTooltipReverseX: 'event-tooltip--reverse-x',
            eventTooltipReverseY: 'event-tooltip--reverse-y',
            eventOpened: 'events-event--opened',
            prevented: 'link--prevented',
            mobileContainer: 'events-mobile-container'
        }
    };

    function EventsCalendar($container, config) {
        this.config = $.isPlainObject(config) ? $.extend(_config, config) : _config;
        this.$container = $container;

        Object.defineProperties(this, {
            tdEventTitleClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.tdEventTitle;
                }
            },
            eventTooltipClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.eventTooltip;
                }
            },
            eventTooltipReverseXClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.eventTooltipReverseX;
                }
            },
            eventTooltipReverseYClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.eventTooltipReverseY;
                }
            },
            eventClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.event;
                }
            },
            eventOpenedClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.eventOpened;
                }
            },
            preventedClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.prevented;
                }
            },
            dayNumClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.dayNum;
                }
            },
            tdSelectedClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.tdSelected;
                }
            },
            mobileContainerClass: {
                get: function() {
                    return this.config.cssPrefix + this.config.classMap.mobileContainer;
                }
            }
        });

        this._bindEvents();
    };

    EventsCalendar.prototype._bindEvents = function () {
        var self = this,
            $w = $(window);

        this.$container.on('mouseenter.MileniaEventsCalendar', '.' + this.tdEventTitleClass, function(e) {
            var $this = $(this),
                $event = $this.siblings('.' + self.eventTooltipClass);

            if(!$event.length) return;

            self.position($event);
        })
        .on('mouseleave.MileniaEventsCalendar', '.' + this.eventClass, function(e) {
            $(this).removeClass(self.eventOpenedClass);
        })
        .on('click.MileniaEventsCalendar', '.' + this.tdEventTitleClass + ' a', function(e) {
            var $this = $(this);

            if(self.config.isTouch && !$this.hasClass(self.preventedClass)) {
                $this.addClass(self.preventedClass);
                self.$container.find('a').not($this).removeClass(self.preventedClass);
                e.preventDefault();
            }
        })
        .on('click.MileniaEventsCalendar', '.' + this.dayNumClass, function(e) {
            if($(window).width() >= self.config.breakpoint) return;

            self.openEvents($(this));
        });

        $w.on('resize.MileniaEventsCalendar', function() {
            if(self.resizeTimeOutId) clearTimeout(self.resizeTimeOutId);

            self.resizeTimeOutId = setTimeout(function() {
                if($w.width() > self.config.breakpoint) {
                    var $mobileContainer = self.$container.siblings('.' + self.mobileContainerClass);

                    self.$container.find('.' + self.tdSelectedClass).removeClass(self.tdSelectedClass);

                    if($mobileContainer.length) {
                        $mobileContainer.add($mobileContainer.find('[data-day]')).hide();
                    }
                }
            }, 200);
        });
    };

    EventsCalendar.prototype.position = function ($event) {
        var xEndPoint = $event.offset().left + $event.outerWidth(),
            yEndPoint = $event.offset().top,
            $w = $(window);

        if($event.hasClass(this.eventTooltipReverseXClass) || $event.hasClass(this.eventTooltipReverseYClass)) {
            $event.removeClass(this.eventTooltipReverseXClass).removeClass(this.eventTooltipReverseYClass);
            this.position($event);
        }

        if(!this.config.rtl) {
            if(xEndPoint > $w.width()) $event.addClass(this.eventTooltipReverseXClass);
        }
        else {
            if($event.offset().left < 0) $event.addClass(this.eventTooltipReverseXClass);
        }

        if(yEndPoint < $w.scrollTop()) $event.addClass(this.eventTooltipReverseYClass);

        $event.stop().parent().addClass(this.eventOpenedClass);
    };

    EventsCalendar.prototype.openEvents = function ($day) {
        var $mobileContainer = this.$container.siblings('.' + this.mobileContainerClass),
            $events,
            $currentEvents,
            $td = $day.closest('td'),
            date = $td.data('day');

        $td.addClass(this.tdSelectedClass);

        this.$container.find('.' + this.tdSelectedClass).not($td).removeClass(this.tdSelectedClass);

        if(!$mobileContainer.length) return;

        $events = $mobileContainer.find('[data-day]');

        if($events.length) {
            $currentEvents = $events.hide().filter('[data-day="'+date+'"]');

            if($currentEvents.length) {
                $mobileContainer.add($currentEvents).show();
            }
            else {
                $mobileContainer.hide();
            }
        }
    };

    return {
        init: function($calendar, config) {
            if(!$calendar || !$calendar.length) return;

            return $calendar.each(function(index, element) {
                var $element = $(element);

                if($element.data('MileniaEventsCalendar')) return;

                $element.data('MileniaEventsCalendar', new EventsCalendar($element, config));
            });
        }
    };
})(window.jQuery);
