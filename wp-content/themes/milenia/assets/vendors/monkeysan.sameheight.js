/**
 * Same Height jQuery plugin
 *
 * @version 1.0.0
 **/
(function(e) {
    function MonkeysanSameHeight(a, b) {

        this.config = {
            timeOut: 50,
            target: null,
            isIsotope: !1,
            columns: !1
        };

        if (b.target) this.items = a.find(b.target);
        else throw Error("'target' option should be specified in the initialization of 'MonkeysanSameHeight' plugin");

        this.items.length && (e.extend(this.config, b), Object.defineProperty(this, "prepare", {
            value: function() {
                this.run();
                e(window).on("resize.MonkeysanSameHeight", this.run.bind(this))
            }
        }), Object.defineProperty(this, "run", {
            value: function() {
                var c = this;
                this.timeoutId && clearTimeout(this.timeoutId);
                c.items.css("height", "auto");
                this.timeoutId = setTimeout(function() {
                    c.calcMax(c.items)
                }, c.config.timeOut)
            }
        }), Object.defineProperty(this, "calcMax", {
            value: function(c, f) {
                var b = 0,
                    d = this;
                if (!f && this.columns) {
                    var g = e(),
                        h = 0;
                    c.each(function(c, a) {
                        var b = e(a);
                        b.closest(".milenia-is-out").length || (g = g.add(b), h++, 0 == h % d.columns && (d.calcMax(g, !0), g = e(), h = 0))
                    })
                } else c.each(function(c, a) {
                    var d = e(a).outerHeight();
                    d > b && (b = d)
                }), c.css("height", b), this.config.isIsotope && a.data('isotope') && a.isotope("layout")
            }
        }), Object.defineProperty(this, "columns", {
            get: function() {
                return this.config.columns
            },
            set: function(a) {
                this.config.columns = a
            }
        }), this.prepare())
    }

    Object.defineProperty(MonkeysanSameHeight.prototype, "appendItems", {
        value: function(a) {
            this.items = this.items.add(a);
            this.run()
        }
    });

    Object.defineProperty(MonkeysanSameHeight.prototype, "getOption", {
        value: function(a) {
            if (a in this.config) return this.config[a]
        }
    });

    e.fn.MonkeysanSameHeight = function(a) {
        return this.each(function(b, c) {
            var f = e(this);
            f.data("sameHeight") || f.data("sameHeight", new MonkeysanSameHeight(f, a))
        })
    }

})(jQuery);
