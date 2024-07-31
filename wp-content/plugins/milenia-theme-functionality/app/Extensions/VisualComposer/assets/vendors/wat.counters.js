/**
 * Simple jQuery plugin for creating animated counters.
 * 
 * @version 1.0.0
 * @license GPL2
 */
 ;(function($){
 	'use strict';

 	/**
	 * Base configuration object for the Counter instance.
	 *
	 * @var Object _baseConfig
	 */
 	var _baseConfig = {
 		duration: 1500,
 		countWhenScrolled: 'on'
 	},

 	/**
 	 * Array of all instances on the page.
 	 *
 	 * @var Array _collection
 	 */
 	 _collection = [];

 	/**
	 * ProgressBar Constructor.
	 *
	 * @param jQuery element
	 * @param Object config
	 */
 	 function Counter(element, config) {
 	 	this.element = element;
 	 	this.value =  isFinite(this.element.data('value')) ? this.element.data('value') : 0;
 	 	this.config = config && $.isPlainObject(config) ? $.extend(true, {}, _baseConfig, config ) : _baseConfig;
 	 	this.waiting = this.config.countWhenScrolled == 'on';

 	 	this.init();
 	 }

 	 /**
 	  * Initialization of Counter instance.
 	  *
 	  * @return undefined
 	  */
 	 Counter.prototype.init = function() {
 	 	if( this.config.countWhenScrolled == 'off' ) return;

 	 	this.reset();
 	 }

 	 /**
 	  * Resets value of the counter.
 	  *
 	  * @return jQuery
 	  */
 	 Counter.prototype.reset = function() {
 	 	this.waiting = true;
 	 	return this.element
 	 		.data('value', 0)
 	 		.attr('data-value', 0);
 	 }

 	 /**
 	  * Set new value of the Counter instance.
 	  *
 	  * @param Number value
 	  * @param Boolean animated
 	  *
 	  * @return jQuery
 	  */
 	 Counter.prototype.count = function(value, animated) {

 	 	this.reset();
 	 	this.waiting = false;

 	 	if(!animated) {
 	 		return this.element
 	 			.data('value', value)
 	 			.attr('data-value', value);
 	 	}

 	 	if( ('requestAnimationFrame' in window ) && ('performance' in window)) {
 	 		return this.requestAnimation(value);
 	 	}
 	 	else {
 	 		return this.baseAnimation(value);
 	 	}

 	}

 	/**
 	 * Animation method for major browsers which support 'requestAnimationFrame' function.
 	 *
 	 * @param Number value
 	 *
 	 * @return undefined
 	 */
 	Counter.prototype.requestAnimation = function(value) {
 		var start = performance.now(),
 			self = this;

 		this.requestId = requestAnimationFrame( function count(time) {
 			var diff = performance.now() - start,
 				currentValue;

 			if( diff > self.config.duration ) diff = self.config.duration;

 			currentValue = Math.ceil(diff / self.config.duration * value);

 			self.element
 				.data('value', currentValue )
 				.attr('data-value', currentValue );

 			if(diff < self.config.duration) {
 				requestAnimationFrame(count);
 			}

 		} );
 	}

 	/**
 	 * Animation method for old browsers which don't support 'requestAnimationFrame' function.
 	 *
 	 * @param Number value
 	 *
 	 * @return undefined
 	 */
 	Counter.prototype.baseAnimation = function(value) {


 		var start = (new Date()).getTime(),
 			self = this,
 			fps = this.config.duration / 60;

 		this.animationIntervalId = setInterval( function(){

 			var diff = (new Date()).getTime() - start,
 				currentValue;

 			if(diff > self.config.duration) diff = self.config.duration;

 			currentValue = Math.ceil( diff / self.config.duration * value );

 			self.element
 				.data('value', currentValue)
 				.attr('data-value', currentValue);

 			if(diff >= self.config.duration) clearInterval( self.animationIntervalId );

 		}, fps);

 	}

 	/**
 	 * Returns true if the counter is waiting for counting.
 	 *
 	 * @return Boolean
 	 */
 	Counter.prototype.isWaitingForCounting = function() {
 		return this.waiting;
 	}


 	/**
 	 * Integration in jQuery.
 	 *
 	 * @param Object config
 	 *
 	 * @return jQuery
 	 */
 	$.fn.WATCounters = function(config){

 		var $w = $(window);

 		$w.on('scroll.WATCounters', function(event){
 			var allCounted = true,
 				offset = $w.scrollTop() + $w.height() - ($w.height() / 4);

 			_collection.forEach(function(e, i, a){
 				if(offset >= e.element.offset().top && e.isWaitingForCounting()) {
 					e.count(e.value, true);
 				}
 			});

 			_collection.forEach( function(e){
				if(e.isWaitingForCounting()) allCounted = false;
			} );

			if(allCounted) $w.off('scroll.WATCounters');

 		});

 		setTimeout(function(){
 			$w.trigger('scroll.WATCounters');
 		},10);

 		return this.each(function(i, el){

 			var $this = $(el);

 			if( !$this.data('WATCounters') ) {
 				var instance = new Counter( $this, config );
 				_collection.push(instance);

 				$this.data('WATCounters', instance);
 			}

 		});
 	}

 })(jQuery);
