/**
 * MonkeysanNav navigation jQuery plugin.
 *
 * @author Monkeysan Team
 * @version 1.0
 * @required Modernizr
 */
;(function($){

	'use strict';

	/**
	 * Base plugin configuration.
	 *
	 * @var private Object _baseConfig
	 */
	var _baseConfig = {
		cssPrefix: '',
		mobileBreakpoint: 767,
		movingToAnotherPageDelay: 700,
		classes: {
			desktopActive: 'selected',
			tabletActive: 'tapped',
			mobileActive: 'tapped',
			reverse: 'reverse',
			mobileBtnAdditionalClass: '',
			bodyMovingToAnotherPage: 'body--moving-to-another-page'
		},
		mobileAnimation: {
			easing: 'easeOutQuint',
			speed: 350
		}
	}

	/**
	 * Contains info about text derection.
	 *
	 * @var private Boolean _isRTL
	 */
	var _isRTL = getComputedStyle(document.body).direction === 'rtl';

	/**
	 * Adds class, if the sub-menu is not placed into the container.
	 *
	 * @param jQuery subMenu
	 * @param String reverseClass
	 *
	 * @return undefined
	 */
	function smartPosition(subMenu, reverseClass){

		var width = subMenu.outerWidth(),
			wWidth = $(window).width();

		if(_isRTL){

			if(subMenu.offset().left <= 0) subMenu.addClass(reverseClass);

		}
		else{

			var offset = subMenu.offset().left;

			if(offset + width > wWidth) subMenu.addClass(reverseClass);

		}

	}

	/**
	 * Navigation Constructor function.
	 *
	 * @param Object options
	 * @param jQuery $element
	 *
	 */
	function Navigation(options, $element){

		var _w = $(window),
			_self = this;

		this.config = $.extend({}, _baseConfig, options);

		Object.defineProperties(this, {

			element : {

				get: function(){

					return $element;

				}

			}

		});

		_w.on('resize.MonkeysanNav', function() {

			if(_self.timeOutId) clearTimeout(_self.timeOutId);

			_self.timeOutId = setTimeout(function(){

				_self._refresh();

			}, 100);

		});

		this._refresh();

	}

	/**
	 * Initialize or refresh the navigation.
	 *
	 * @return undefined
	 */
	Navigation.prototype._refresh = function(){

		if($(window).width() <= this.config.mobileBreakpoint && !(this.state instanceof MobileState)){

			if(this.state) this.state.destroy();

			this.state = new MobileState(this.config, this.element);
			this.state.init();

		} else if($(window).width() >= this.config.mobileBreakpoint){

			if ( Modernizr.touchevents ) {

				if ( !(this.state instanceof TabletState)) {

					if(this.state) this.state.destroy();

					this.state = new TabletState(this.config, this.element);
					this.state.init();

				}

			} else {

				if ( !(this.state instanceof DesktopState) ) {

					if(this.state) this.state.destroy();

					this.state = new DesktopState(this.config, this.element);
					this.state.init();

				}

			}

		}

	}

	/**
	 * AbstractState constructor function. Defines base properties for all of the states.
	 *
	 * @param Object config
	 * @param jQuery $element
	 *
	 */
	function AbstractState(config, $element) {

		Object.defineProperties(this, {

			/**
			 * Defines active class for the current state.
			 *
			 * @var public string
			 */
			activeClass: {

				get: function(){

					return this.prefix + config.classes.desktopActive;

				},
				configurable: true,
				enumerable: true

			},

			/**
			 * Defines reverse class for the current state.
			 *
			 * @var public string
			 */
			reverseClass: {

				get: function(){

					return this.prefix + config.classes.reverse;

				},
				configurable: true,
				enumerable: true

			},

			/**
			 * Link to the main navigation jQuery element.
			 *
			 * @var public jQuery
			 */
			element: {

				get: function(){

					return $element;

				},
				configurable: false,
				enumerable: false

			},

			/**
			 * Defines css prefix.
			 *
			 * @var public string
			 */
			classPrefix: {

				get: function(){

					return '.' + config.cssPrefix;

				},
				configurable: false,
				enumerable: false

			},

			/**
			 * Defines css prefix.
			 *
			 * @var public string
			 */
			prefix: {

				get: function(){

					return config.cssPrefix;

				}

			},

			/**
			 * Link to the configuration object.
			 *
			 * @var public string
			 */
			config: {

				get: function(){

					return config;

				},
				configurable: false,
				enumerable: false

			}

		});

	}

	/**
	 * DesktopState constructor function.
	 *
	 * @param Object config
	 * @param jQuery $element
	 *
	 */
	function DesktopState(config, $element){

		AbstractState.call(this, config, $element);

	}

	/**
	 * Initialization of Desktop navigation state.
	 *
	 * @return undefined
	 */
	DesktopState.prototype.init = function(){

		var _self = this;

		_self.element.on('click', 'a:not([target="_blank"])', function(event){
			var $this = $(this),
				href = $this.attr('href');

			if ( href == '#' ) return;

			event.preventDefault();

			$('body').addClass(_self.config.cssPrefix + _self.config.classes.bodyMovingToAnotherPage);

			setTimeout(function(){
				window.location.href = href;
			}, _self.config.movingToAnotherPageDelay);
		});

		_self.element.on('mouseenter.MonkeysanNavDesktop', _self.classPrefix + 'has-children, .menu-item-has-children', function(e){

			var $this = $(this),
				subMenu = $this.children(_self.classPrefix + 'sub-menu, .sub-menu');

			if(!$this.hasClass(_self.activeClass)){

				if(subMenu.length){

					if(subMenu.data('timeOutId')) clearTimeout(subMenu.data('timeOutId'));

					smartPosition(subMenu, _self.reverseClass);
				}

				$this.addClass(_self.activeClass);

			}

			e.stopPropagation();
			e.preventDefault();

		});

		_self.element.on('mouseleave.MonkeysanNavDesktop', _self.classPrefix + 'has-children.' + _self.activeClass + ', .menu-item-has-children.' + _self.activeClass, function(e){

			var $this = $(this);

			$this.removeClass(_self.activeClass);

			e.preventDefault();

		});

		_self.element.on('mouseleave.MonkeysanNavDesktop', function(e){

			$(this).find(_self.classPrefix + 'has-children.' + _self.activeClass + ', .menu-item-has-children.' + _self.activeClass)
					.removeClass(_self.activeClass);

			$(this).find('.' + _self.reverseClass).each(function(i, el){

				var $this = $(el);

				$this.data('timeOutId', setTimeout(function(){

					$this.removeClass(_self.reverseClass);

				}, 350));

			});

			e.stopPropagation();
			e.preventDefault();

		});

	}

	/**
	 * Destroy-function for the Desktop state.
	 *
	 * @return undefined.
	 */
	DesktopState.prototype.destroy = function(){

		this.element.off('.MonkeysanNavDesktop');
		this.element.find(this.classPrefix + this.activeClass).removeClass(this.activeClass);

	}


	/**
	 * TabletState constructor function.
	 *
	 * @param Object config
	 * @param jQuery $element
	 *
	 */
	function TabletState(config, $element){

		AbstractState.call(this, config, $element);

		/**
		 * Defines active class for the Tablet state.
		 *
		 * @var
		 */
		Object.defineProperty(this, 'activeClass', {

			get: function(){

				return this.prefix + config.classes.tabletActive;

			},
			configurable: false

		});

	}

	/**
	 * Initialization of the Tablet navigation state.
	 *
	 * @return undefined
	 */
	TabletState.prototype.init = function(){

		var _self = this,
			_nav = this.element,
			preventedClass = this.prefix + 'prevented';

		_nav.on('click.MonkeysanNavTablet', 'a', function(e){

			var $link = $(this),
				href = $link.attr('href');

			_self.closeAllSubMenus($link.parents('.' + _self.activeClass));

			if($link.parent('.menu-item-has-children').length && !$link.hasClass(preventedClass)){

				$link.addClass(preventedClass);

				var $this = $link.parent(),
					subMenu = $this.children(_self.classPrefix + 'sub-menu, .sub-menu');

				if(!$this.hasClass(_self.activeClass)){

					if(subMenu.length){

						if(subMenu.data('timeOutId')) clearTimeout(subMenu.data('timeOutId'));
						smartPosition(subMenu, _self.reverseClass);

					}

					$this.addClass(_self.activeClass);

				}

				e.stopPropagation();
				e.preventDefault();

			}
			else {
				if(href == '#') return;

				e.preventDefault();
				$('body').addClass(_self.config.cssPrefix + _self.config.classes.bodyMovingToAnotherPage);


				setTimeout(function(){
					window.location.href = href;
				}, _self.config.movingToAnotherPageDelay);
			}

		});

		$(document).on('click.MonkeysanNavTablet', function(e){

			e.stopPropagation();

			if(!$(e.target).closest(_self.element).length) _self.closeAllSubMenus();

		});

	}

	/**
	 * It closes all open sub-menus, except sub-menu which is passed as an argument.
	 *
	 * @param jQuery except
	 *
	 * @return undefined
	 */
	TabletState.prototype.closeAllSubMenus = function(except){

		var _self = this,
			selectedItems = _self.element.find('.' + _self.activeClass),
			preventedClass = this.prefix + 'prevented',
			preventedCClass = this.classPrefix + 'prevented';

		if(except) selectedItems = selectedItems.not(except);

		if(selectedItems.length){

			var openedSubMenus = selectedItems.children(_self.classPrefix + 'sub-menu, .sub-menu');

			if(openedSubMenus.length){

				openedSubMenus.each(function(i, el){

					var $currentSubMenu = $(el);

					$currentSubMenu.data('timeOutId', setTimeout(function(){

						$currentSubMenu.removeClass(_self.reverseClass);

					}, 350));

				});

			}

			selectedItems
				.removeClass(_self.activeClass)
				.children(preventedCClass).removeClass(preventedClass);

		}

	}

	/**
	 * Destroy-function for the Tablet state.
	 *
	 * @return undefined
	 */
	TabletState.prototype.destroy = function(){

		this.element.off('.MonkeysanNavTablet');
		this.closeAllSubMenus();

	}

	/**
	 * MobileState constructor function.
	 *
	 * @param Object config
	 * @param jQuery $element
	 *
	 */
	function MobileState(config, $element){

		AbstractState.call(this, config, $element);

		/**
		 * Defines active class for the Mobile state.
		 *
		 * @var
		 */
		Object.defineProperty(this, 'activeClass', {

			get: function(){

				return this.prefix + config.classes.mobileActive;

			},
			configurable: false

		});

	}

	/**
	 * Initialization of mobile navigation state.
	 *
	 * @return undefined
	 */
	MobileState.prototype.init = function(){

		var _self = this,
			navBtnClass = _self.prefix + 'mobile-nav-btn';
			// navBtnClass = _self.prefix + 'mobile-nav-btn' + ' ' + _self.config.classes.mobileBtnAdditionalClass;

		_self.element.add(_self.element.find(_self.classPrefix + 'sub-menu, .sub-menu')).hide();

		// if(!_self.element.prev('.' + navBtnClass).length){
		var elem = '.' + navBtnClass;

		if ( !$(elem).length ) {

			if ($('.milenia-header-justify').length) {

				var $navBtn = $('<button></button>', {
					'class': navBtnClass
				});

				$('.milenia-header-justify').append($navBtn);

				$navBtn.on('click.MonkeysanNavMobile', function(e){

					$(this).toggleClass(_self.prefix + 'opened');

					_self.element
						.stop()
						.slideToggle({
							duration: _self.config.mobileAnimation.speed,
							easing: _self.config.mobileAnimation.easing,
							step: function() {
								_self.element.trigger('navigationopening.jquery.nav', [_self.element]);
							}
						});

					e.stopPropagation();
					e.preventDefault();

				});

			}

		}

		_self.element.on('click.MonkeysanNavMobile', 'a', function(e){

			var $link = $(this),
				href = $link.attr('href'),
				preventedClass = _self.prefix + 'prevented';

			if($link.parent('.menu-item-has-children').length && !$link.hasClass(preventedClass)){

				$link.addClass(preventedClass);

				var $this = $link.parent();

				$this
					.addClass(_self.activeClass)
					.children(_self.classPrefix + 'sub-menu, .sub-menu')
					.stop()
					.slideDown({
						duration: _self.config.mobileAnimation.speed,
						easing: _self.config.mobileAnimation.easing,
						step: function() {
							_self.element.trigger('submenumobileopening.jquery.nav', [$(this)]);
						},
						complete: function() {
							_self.element.trigger('submenumobileopened.jquery.nav', [$(this)]);
						}
					})
					.parent()
					.siblings('.' + _self.activeClass)
					.removeClass(_self.activeClass)
					.children('.' + preventedClass)
					.removeClass(preventedClass)
					.siblings(_self.classPrefix + 'sub-menu, .sub-menu')
					.stop()
					.slideUp({
						duration: _self.config.mobileAnimation.speed,
						easing: _self.config.mobileAnimation.easing,
						step: function() {
							_self.element.trigger('submenumobileclosing.jquery.nav', [$(this)]);
						},
						complete: function() {
							_self.element.trigger('submenumobileclosed.jquery.nav', [$(this)]);
						}
					});

				e.preventDefault();
				e.stopPropagation();

			}
			else {
				if(href == '#') return;

				e.preventDefault();

				$('body').addClass(_self.config.cssPrefix + _self.config.classes.bodyMovingToAnotherPage);

				setTimeout(function(){
					window.location.href = href;
				}, _self.config.movingToAnotherPageDelay);
			}

		});

	}

	/**
	 * Destroy-function for the Mobile state.
	 *
	 * @return undefined
	 */
	MobileState.prototype.destroy = function(){

		this.element
			.show()
			.off('.MonkeysanNavMobile')
			.prev(this.classPrefix + 'mobile-nav-btn')
			.removeClass(this.prefix + 'opened')
			.end()
			.find('.' + this.activeClass)
			.removeClass(this.activeClass)
			.end()
			.find(this.classPrefix + 'prevented')
			.removeClass(this.prefix + 'prevented')
			.end()
			.find(this.classPrefix + 'sub-menu, .sub-menu')
			.show();

	}


	$.fn.MonkeysanNav = function(options){

		return this.each(function(i, el){

			var $this = $(el);

			if ( !$this.data('MonkeysanNav') ) {
				$this.data('MonkeysanNav', new Navigation(options, $this));
			}

		});

	}

})(jQuery);
