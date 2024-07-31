var MileniaLinkUnderliner = (function($){
    'use strict';

    return {
        _$collection: $(),
		init: function($collection) {
			var self = this,
				$currentFilteredCollection;

			if(!$.isjQuery($collection) || !$collection.length) return;

			if(!this._bindedEvents) this._bindEvents();

			$currentFilteredCollection = $();

			$collection.each(function(index, element){
				var $element = $(element);

				if(self._$collection.filter($element).length) return;

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

			$(window).on('resize.ApolaLinksUnderline', function() {
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
})(window.jQuery);
