/**
 * Simple jQuery plugin for creating animated counters.
 *
 * @author Monkeysan
 * @version 1.0.0
 */
(function(f) {
    function WATCounters(a, c) {
        this.element = a;
        this.value = isFinite(this.element.data("value")) ? this.element.data("value") : 0;
        this.config = c && f.isPlainObject(c) ? f.extend(!0, {}, g, c) : g;
        this.waiting = !0;
        this.init()
    }
    var g = {
            duration: 1500,
            countWhenScrolled: "on"
        },
        k = [];
    WATCounters.prototype.init = function() {
        "off" != this.config.countWhenScrolled && this.reset()
    };
    WATCounters.prototype.reset = function() {
        this.waiting = !0;
        return this.element.data("value", 0).attr("data-value", 0)
    };
    WATCounters.prototype.count = function(a, c) {
        this.reset();
        this.waiting = !1;
        return c ?
            "requestAnimationFrame" in window && "performance" in window ? this.requestAnimation(a) : this.baseAnimation(a) : this.element.data("value", a).attr("data-value", a)
    };
    WATCounters.prototype.requestAnimation = function(a) {
        var c = performance.now(),
            d = this;
        this.requestId = requestAnimationFrame(function h(e) {
            e = performance.now() - c;
            e > d.config.duration && (e = d.config.duration);
            var b = Math.ceil(e / d.config.duration * a);
            d.element.data("value", b).attr("data-value", b);
            e < d.config.duration && requestAnimationFrame(h)
        })
    };
    WATCounters.prototype.baseAnimation = function(a) {
        var c = (new Date).getTime(),
            d = this;
        this.animationIntervalId = setInterval(function() {
            var b = (new Date).getTime() - c;
            b > d.config.duration && (b = d.config.duration);
            var h = Math.ceil(b / d.config.duration * a);
            d.element.data("value", h).attr("data-value", h);
            b >= d.config.duration && clearInterval(d.animationIntervalId)
        }, this.config.duration / 60)
    };
    WATCounters.prototype.isWaitingForCounting = function() {
        return this.waiting
    };
    f.fn.WATCounters = function(a) {
        var c = f(window);
        c.on("scroll.WATCounters", function(b) {
            var a = !0,
                d = c.scrollTop() +
                c.height() - c.height() / 4;
            k.forEach(function(a, b, c) {
                d >= a.element.offset().top && a.isWaitingForCounting() && a.count(a.value, !0)
            });
            k.forEach(function(b) {
                b.isWaitingForCounting() && (a = !1)
            });
            a && c.off("scroll.WATCounters")
        });
        setTimeout(function() {
            c.trigger("scroll.WATCounters")
        }, 10);
        return this.each(function(c, g) {
            var d = f(g);
            if (!d.data("WATCounters")) {
                var e = new WATCounters(d, a);
                a && a.countWhenScrolled && "on" == a.countWhenScrolled && k.push(e);
                d.data("WATCounters", e)
            }
        })
    }
})(window.jQuery);
