(function ($, window) {

	'use strict';

	$.milenia_woocommerce_mod = $.milenia_woocommerce_mod || {};

	/*	Product Thumbs Carousel
	/* --------------------------------------------- */

	$.milenia_woocommerce_mod.thumbs_carousel = function () {

		if ($('.flex-control-nav').length) {

			var $thumbs_carousel = $('.flex-control-nav'),
				$items = $('.woocommerce-product-gallery__image');
				$thumbs_carousel.addClass('owl-carousel');

			if ( $items && $items.length > 3 ) {
				$thumbs_carousel.owlCarousel({
					items : 3,
					navSpeed : 800,
					nav: true,
					margin: 21,
					dots: false,
					loop: false,
					rtl: Milenia.RTL ? true : false,
					navText:false,
					responsive : {
						0: {
							items: 2
						},
						481: {
							items: 3
						},
						767: {
							items: 3
						},
						1200: {
							items: 3
						}
					}
				});

			}

		}

	}

	/*	Qty
	 /* --------------------------------------------- */

	$.milenia_woocommerce_mod.qty = function () {

		$(document).on('click', '.qty-plus, .qty-minus', function (e) {

			e.preventDefault();

			// Get values
			var $qty = $(this).closest('.quantity').find('.input-text'),
				currentVal = parseFloat($qty.val()),
				max = parseFloat($qty.attr('max')),
				min = parseFloat($qty.attr('min')),
				step = $qty.attr('step');

			// Format values
			if (!currentVal || currentVal === '' || currentVal === 'NaN') currentVal = 0;
			if (max === '' || max === 'NaN') max = '';
			if (min === '' || min === 'NaN') min = 0;
			if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN') step = 1;

			// Change the value
			if ($(this).is('.qty-plus')) {
				if (max && ( max == currentVal || currentVal > max )) {
					$qty.val(max);
				} else {
					$qty.val(currentVal + parseFloat(step));
				}
			} else {
				if (min && ( min == currentVal || currentVal < min )) {
					$qty.val(min);
				} else if (currentVal > 0) {
					$qty.val(currentVal - parseFloat(step));
				}
			}

			// Trigger change event
			$qty.trigger('change input');

			$( '.woocommerce-cart-form :input[name="update_cart"]' ).prop( 'disabled', false );

		});

	}


	/*	Cart
	 /* --------------------------------------------- */

	$.milenia_woocommerce_mod.cart = function () {
		({
			init: function () {
				var base = this;

				base.support = {
					touchevents: Modernizr.touchevents,
					transitions: Modernizr.csstransitions
				};

				base.eventtype = base.support.touchevents ? 'touchstart' : 'click';
				base.listeners();
			},
			listeners: function () {
				var base = this;



				base.track_ajax_refresh_cart();
				base.track_ajax_adding_to_cart();
				base.track_ajax_added_to_cart();
			},
			track_ajax_refresh_cart: function () {

				var base = this;

				$('body').on('removed_from_cart', function (e, fragments, hash, $thisbutton) {
					base.update_cart_count(fragments);
				});

			},
			track_ajax_adding_to_cart: function () {

				$('body').on('adding_to_cart', function (e, $thisbutton, $data) {
					e.preventDefault();

					$thisbutton.block({
							message: null,
							overlayCSS: {
								borderRadius: 0,
								opacity: 0.6
							}
						}
					);

				});

			},
			track_ajax_added_to_cart: function () {

				var base = this;

				$('body').on('added_to_cart', function (e, fragments, cart_hash, $thisbutton) {
					$thisbutton.unblock().hide();
					base.update_cart_count(fragments);

				});

			},
			update_cart_count: function(fragments) {
				if ( $('.woo-cart-count').length ) {
					$('.woo-cart-count').text(fragments.count);
				}
			},

		}.init());
	}

	/*	DOM READY
	/* --------------------------------------------- */

	$(window).load(function() {
		$.milenia_woocommerce_mod.thumbs_carousel();
	});

	$(function () {

		$(document).on('click', '.zoomImg', function(e) {
			e.preventDefault();
			$('.woocommerce-product-gallery__trigger').trigger('click');
		});

		$.milenia_woocommerce_mod.cart();
		$.milenia_woocommerce_mod.qty();
	});

})(jQuery, window);

