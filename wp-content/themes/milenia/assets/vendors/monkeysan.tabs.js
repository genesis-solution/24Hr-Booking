/**
 * Tabs jQuery plugin
 *
 * @author Monkeysan
 * @version 1.0
 **/
;(function($){
	'use strict';

	var $doc = $(document);

	/**
	 * Tabs Constructor
	 * @param jQuery element
	 * @param Object options
	 * @return undefined;
	 **/
	function Tabs(element, options){

		this.el = element;

		this.config = {
			speed: $.fx.speed,
			easing: 'linear',
			cssPrefix: '',
			afterOpen: function(){},
			afterClose: function(){},
			afterInit: function() {}
		}

		options = options || {};

		$.extend(this.config, options);

		this.activeClass = this.config.cssPrefix + 'active';

		this.nav = this.el.find('.' + this.config.cssPrefix + 'tabs-nav');
		this.tabsContainer = this.el.find('.' + this.config.cssPrefix + 'tabs-container');
		this.tabs = this.tabsContainer.find('.' + this.config.cssPrefix + 'tab');
		this.tabLinkClass = this.config.cssPrefix + 'tab-link';

		this.toDefaultState();
		this.bindEvents();

	}

	Tabs.prototype.toDefaultState = function(){

		var active = this.nav.find('.' + this.activeClass);

		if(!active.length){
			active = this.nav.find('li').first();
			active.addClass(this.activeClass);
		}

		var tab = $(active.children('.' + this.tabLinkClass).attr('href'));

		if(tab.length){

			this.tabsContainer.css({
				'position': 'relative'
			});

			this.tabs.css({
				'position': 'absolute',
				'top': 0,
				'left': 0,
				'width': '100%'
			});

			this.tabs.not(tab).css({
				'opacity': 0,
				'visibility': 'hidden'
			});

			this.openTab(tab);

		}

		this.config.afterInit.call(this);
	}

	Tabs.prototype.bindEvents = function(){

		var _self = this;

		this.nav.on('click', '.' + this.tabLinkClass, {self: this}, function(e){

			e.preventDefault();
			var $this = $(this),
				self = e.data.self,
				tab = $($this.attr('href'));

			if($this.parent().hasClass(self.activeClass)) return false;

			$this
				.attr('aria-selected', 'true')
				.parent()
				.addClass(self.activeClass)
				.siblings()
				.removeClass(self.activeClass)
				.children('.' + self.tabLinkClass)
				.attr('aria-selected', 'false');

			if(tab.length) self.openTab(tab);

		});

		$(window).on('resize.tabs', function(){
			_self.updateContainer();
		});

	}

	Tabs.prototype.updateContainer = function(){

		var self = this;
		if(self.timeOutId) clearTimeout(self.timeOutId);

		self.timeOutId = setTimeout(function(){

			var tabHeight = self.tabsContainer.find('.' + self.activeClass).outerHeight();

			self.tabsContainer.stop().animate({
				'height': tabHeight
			}, {
				complete: function(){
					clearTimeout(self.timeOutId);
					$doc.trigger('container.updated.mokeysan.tabs', [$(this)]);
				},
				duration: self.config.speed,
				easing: self.config.easing
			});

		}, 100);

	}

	Tabs.prototype.openTab = function(tab) {

		var self = this,
			tabHeight = tab.outerHeight(),
			currentTab = tab.siblings('.' + this.activeClass);

		if(currentTab.length) this.closeTab(currentTab);

		tab
			.addClass(this.activeClass)
			.siblings()
			.removeClass(this.activeClass);

		this.tabsContainer.stop().animate({
			'height': tabHeight
		}, {
			duration: self.config.speed,
			easing: self.config.easing,
			complete: function() {
				$doc.trigger('container.updated.mokeysan.tabs', [$(this)]);
			}
		});

		tab.css('visibility', 'visible').stop().animate({
			'opacity': 1
		}, {
			complete: function(){
				self.config.afterOpen.call($(this));
			},
			duration: self.config.speed,
			easing: self.config.easing
		});

	}

	Tabs.prototype.closeTab = function(tab){

		var self = this;

		tab.stop().animate({
			'opacity': 0
		}, {
			complete: function(){
				var $this = $(this);

				$this.css('visibility', 'hidden');
				self.config.afterClose.call($this);

			},
			duration: self.config.speed,
			easing: self.config.easing
		});

	}


	$.fn.MonkeysanTabs = function(options){

		return this.each(function(){

			var $this = $(this);

			if(!$this.data('tabs')){

				$this.data('tabs', new Tabs($this, options));
			}

		});

	}

})(jQuery);
