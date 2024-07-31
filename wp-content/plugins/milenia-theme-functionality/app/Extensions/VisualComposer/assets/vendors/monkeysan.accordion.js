/**
 * Accordion jQuery plugin
 *
 * @author Monkeysan
 * @version 1.0
 **/
(function(d) {
    function MonkeysanAccordion(a, b) {
        this.el = a;
        this.config = {
            toggle: !1,
            easing: "linear",
            speed: 350,
            afterOpen: function() {},
            afterClose: function() {},
            cssPrefix: ""
        };
        b = b || {};
        d.extend(this.config, b);
        this.titleClass = this.config.cssPrefix + "panels-title";
        this.defClass = this.config.cssPrefix + "panels-definition";
        this.activeClass = this.config.cssPrefix + "panels-active";
        this.toDefaultState();
        this.bindEvents()
    }
    MonkeysanAccordion.prototype.toDefaultState = function() {
        var a = this.el.find("." + this.activeClass);
        a.length || (a = this.el.find("." + this.titleClass).eq(0).addClass(this.activeClass));
        if (this.config.toggle) return this.el.find("." + this.titleClass).next("." + this.defClass).hide(), a.next("." + this.defClass).show(), !1;
        a.next("." + this.defClass).siblings("." + this.defClass).hide()
    };
    MonkeysanAccordion.prototype.bindEvents = function() {
        var a = this;
        this.el.on("click", "." + a.titleClass, function(b) {
            var c = d(this);
            b.preventDefault();
            a.config.toggle ? a.toggleHandler(c) :
                a.accordionHandler(c)
        })
    };
    MonkeysanAccordion.prototype.accordionHandler = function(a) {
        var $innerBtn = a.find('button[type="button"]'),
            $siblingsInnerBtns;

        if (a.hasClass(this.activeClass)) {
            if($innerBtn.length) {
                $innerBtn.attr('aria-expanded', 'false');
            }

            if(a.filter('[aria-expanded]').length) {
                a.attr('aria-expanded', 'false');
            }

            return a.removeClass(this.activeClass).next("." + this.defClass).stop().slideUp({
                duration: this.config.speed,
                easing: this.config.easing,
                complete: this.config.afterClose.bind(a.next("." + this.defClass))
            });
        }

        if($innerBtn.length) {
            $innerBtn.attr('aria-expanded', 'true');

            $siblingsInnerBtns = a.siblings('.' + this.titleClass).find('button[type="button"]').attr('aria-expanded', 'false');
            if($siblingsInnerBtns.length) $siblingsInnerBtns.attr('aria-expanded', 'false');
        }

        if(a.filter('[aria-expanded]').length) {
            a.attr('aria-expanded', 'true').siblings('.' + this.titleClass).attr('aria-expanded', 'false');
        }

        a.addClass(this.activeClass).next("." + this.defClass).stop().slideDown({
            duration: this.config.speed,
            easing: this.config.easing,
            complete: this.config.afterOpen.bind(a.next("." + this.defClass))
        }).siblings("." +
            this.defClass).stop().slideUp({
            duration: this.config.speed,
            easing: this.config.easing,
            complete: this.config.afterClose.bind(a.next("." + this.defClass))
        }).prev("." + this.titleClass).removeClass(this.activeClass)
    };
    MonkeysanAccordion.prototype.toggleHandler = function(a) {
        var $innerBtn = a.find('button[type="button"]');

        if($innerBtn.length) {
            $innerBtn.attr('aria-expanded', a.hasClass(this.activeClass) ? 'false': 'true');
        }

        if(a.filter('[aria-expanded]').length) {
            a.attr('aria-expanded', a.hasClass(this.activeClass) ? 'false': 'true');
        }

        a.hasClass(this.activeClass) ? a.removeClass(this.activeClass).next("." + this.defClass).stop().slideUp({
            duration: this.config.speed,
            easing: this.config.easing,
            complete: this.config.afterClose.bind(a.next("." + this.defClass))
        }) : a.addClass(this.activeClass).next("." +
            this.defClass).stop().slideDown({
            duration: this.config.speed,
            easing: this.config.easing,
            complete: this.config.afterOpen.bind(a.next(this.defClass))
        })
    };
    d.fn.MonkeysanAccordion = function(a) {
        return this.each(function() {
            var b = d(this);
            b.data("accordion") || b.data("accordion", new MonkeysanAccordion(b, a))
        })
    }
})(window.jQuery);
