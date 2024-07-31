var MileniaIsotopeWrapper = (function($){
	'use strict';

	/**
	 * Base configuration of the module.
	 * @var {Object}
	 */
	var _config = {
		itemSelector: '.grid-item',
		percentPosition: true,
		transitionDuration: 500,
		originLeft: getComputedStyle( document.body ).direction !== "rtl",
		masonry: {
			columnWidth: '.milenia-grid-sizer'
		},
		activeFilterClass: 'milenia-active'
	};

	/**
	 * Initializes new IsotopeWrapper instances.
	 * @param {jQuery} collection
	 * @param {Object} config
	 * @return {jQuery}
	 */
	var isotope = function( collection, config ) {
		collection = collection && collection.length ? collection : $();
		if( !collection.length ) return collection;

		config = config && $.isPlainObject( config ) ? config : {};

		return collection.each(function(){
			var $container = $(this),
				containerConfig = $.extend( true, {}, _config, config, $container.data() );

			if($container.data('isotope-layout') == 'grid') {
				delete containerConfig['masonry'];
				containerConfig.layoutMode = 'fitRows';
			}

			if( !$container.data( 'IsotopeWrapper' ) ) {
				$container.data( 'IsotopeWrapper', new IsotopeWrapper( $container, containerConfig ) );
			}
		});
	};

	/**
	 * Isotope wrapper.
	 * @param {jQuery} container
	 * @param {Object} config
	 * @constructor
	 */
	function IsotopeWrapper(container, config) {

		/**
		 * Contains link to the current container.
		 * @type {jQuery}
		 * @public
		 */
		this.container = container;

		/**
		 * Contains configuration object.
		 * @type {Object}
		 * @public
		 */
		this.config = config;


		this.init();
	};

	/**
	 * Initialization of the current isotope container.
	 * @return {undefined}
	 */
	IsotopeWrapper.prototype.init = function() {

		/**
		 * Contains link to the current object.
		 * @private
		 */
		var _self = this;

		this.container.isotope( this.config );

		this.container.jQueryImagesLoaded().progress( function() {
			_self.container.isotope( 'layout' );
			_self.container.trigger('milenia.isotopeReady');
		} );

		if( this.container.data('isotope-filter') ) this.initFilter();
	};

	/**
	 * Reinitialization of the container layout.
	 * @public
	 * @return {undefined}
	 */
	IsotopeWrapper.prototype.relayout = function() {
		this.container.isotope( 'layout' );
	};

	/**
	 * Initialization of the filter of the isotope container.
	 * @public
	 */
	IsotopeWrapper.prototype.initFilter = function() {
		if( this.filter ) return;

		var _self = this;

		if( this.container.data('isotope-filter') ) {

			this.filter = $( this.container.data('isotope-filter') );

			if( !this.filter.length ) {
				this.filter = null;
				return;
			}

			this.filter.on('click.IsotopeWrapper', '[data-filter]', function(e){

				var $this = $(this);
				if( !$this.data('filter') ) return;

				$this
					.closest(_self.filter)
					.find('.' + _self.config.activeFilterClass)
					.removeClass(_self.config.activeFilterClass);

				$this.addClass(_self.config.activeFilterClass);

				_self.container.isotope({
					filter: $this.data('filter')
				});

				e.preventDefault();
			});

		}
	};

	return {
		init: isotope
	}

})(jQuery);
