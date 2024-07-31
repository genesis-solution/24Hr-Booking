
(function ($) {

	$.milenia_gallery_builder = $.milenia_gallery_builder || {};

	/*	Init
	 /* --------------------------------------------- */

	$.milenia_gallery_builder.init = function () {

		this.$container = $('#milenia_gallery_builder');

		this.$container.on('click', '.add_image_available_media', function (e) {
			$.milenia_gallery_builder.add_available_media(e);
		}).on('click', '.select_attach_id_from_media_library', function (e) {
			$.milenia_gallery_builder.select_attach_id_from_media_library(e);
		}).on('click', '.remove-item', function (e) {
			$.milenia_gallery_builder.remove_item(e);
		}).on('click', '.edit-item', function (e) {
			$.milenia_gallery_builder.edit_item(e);
		});

		$('.sortable-img-items').sortable({
			activate: function( event, ui ) {
				var $this = $(this),
					$height = $this.outerHeight() - 7;
					$this.children('.ui-state-highlight').css('height', $height);
			},
			placeholder: 'ui-state-highlight',
			handle: '.drag-item'
		});

		$('select.strip_custom_select').styler({
			selectSearch: true
		});

	}

	/*	Add
	/* --------------------------------------------- */

	$.milenia_gallery_builder.add_available_media = function (e) {

		e.preventDefault();

		var $element = $(e.target),
			$d_target = $(e.delegateTarget),
			type = $element.data('type'),
			nonce = $('input[name=milenia_gallery_builder_nonce]', $d_target).val(),
			data = {
				action: 'milenia_generate_inserted_media_to_slider',
				type: type,
				milenia_gallery_builder_nonce: nonce
			}

		if (type == 'video') {

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				success: function (response) {
					$element.parents('.slider_option').find(".sortable-img-items").append(response);
				},
				complete: function () {
					setTimeout(function () {
						$('.img-item.add_animation').removeClass('add_animation');
					}, 500);
				},
				error: function(){
					// console.dir(args);
				}
			});

			return true;
		}

		var file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Select Images',
			button: { text: 'Select' },
			multiple: true,
			library: { type: 'image' }
		});

		var itemsIDs = [];

		file_frame.on('select', function () {

			file_frame.state().get('selection').forEach(function(item, i, arr){
				itemsIDs[itemsIDs.length] = item.id;
			});

			data.itemsIDs = itemsIDs.join(',');

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				success: function (response) {
					$element.parents('.slider_option').find(".sortable-img-items").append(response);
				},
				complete: function () {

					setTimeout(function () {
						$('.img-item.add_animation').removeClass('add_animation');
					}, 500);

				}
			});

		});

		file_frame.open();

	}

	/*	Select attach id from media library
	/* --------------------------------------------- */

	$.milenia_gallery_builder.select_attach_id_from_media_library = function (e) {

		e.preventDefault();

		var $element = $(e.target),
			select_image_root = $element.parent(".meta-control");

		if ( file_frame_new ) {
			file_frame_new.open();
			return;
		}

		var file_frame_new = wp.media.frames.file_frame = wp.media({
			title: $(this).data( 'uploader_title' ),
			button: {
				text: $(this).data( 'uploader_button_text' )
			},
			multiple: false
		});

		file_frame_new.on( 'select', function () {
			attachment = file_frame_new.state().get('selection').first().toJSON();
			select_image_root.children(".select_img_attachid").val(attachment.id);
			select_image_root.children(".select_img_preview").html("<img src='" + attachment.url + "' alt=''>");
		});

		file_frame_new.open();

	}

	/*	Remove Item
	/* --------------------------------------------- */

	$.milenia_gallery_builder.remove_item = function (e) {

		e.preventDefault();

		if (!$(e.target).length) return;

		$(e.target).parents(".img-item").animate({
			'opacity': 0
		}, 200, function () {
			$(this).animate({ 'width' : 0 }, 200, function () {
				$(this).remove();
			});
		});

	}

	/*	Edit Item
	 /* --------------------------------------------- */

	$.milenia_gallery_builder.edit_item = function (e) {

		e.preventDefault();
		new $.milenia_popup_gallery_builder(e, {
			on_load : function () {

				var base = this,
					$slider_range_x = $('.slider-range-x', base.modal),
					$hidden_xpos = $('.bgposition_x', base.modal),
					$slider_range_y = $('.slider-range-y', base.modal),
					$hidden_ypos = $('.bgposition_y', base.modal);

				// if ($slider_range_x.length) {
                //
				// 	$slider_range_x.ionRangeSlider({
				// 		grid: true,
				// 		min: 0,
				// 		max: 100,
				// 		from: $slider_range_x.data('from'),
				// 		postfix: "%",
				// 		onFinish: function (ui) {
				// 			$hidden_xpos.val(parseInt(ui.from, 10));
				// 		}
				// 	});
                //
				// }
                //
				// if ($slider_range_y.length) {
                //
				// 	$slider_range_y.ionRangeSlider({
				// 		grid: true,
				// 		min: 0,
				// 		max: 100,
				// 		from: $slider_range_y.data('from'),
				// 		postfix: "%",
				// 		onFinish: function (ui) {
				// 			$hidden_ypos.val(parseInt(ui.from, 10));
				// 		}
				// 	});
                //
				// }

				// if ($('.wp-color-picker').length) {
				// 	$('.wp-color-picker').wpColorPicker();
				// }

			}
		});
	}

	/*	Edit Gallery Builder Popup
	/* --------------------------------------------- */

	$.milenia_popup_gallery_builder = function (e, options) {
		this.el = $(e.target);
		this.options = $.extend({}, $.milenia_popup_gallery_builder.DEFAULTS, options);
		this.init();
	}

	$.milenia_popup_gallery_builder.DEFAULTS = {
		on_load : function () { }
	}

	$.milenia_popup_gallery_builder.openInstance = [];

	$.milenia_popup_gallery_builder.prototype = {
		init: function () {
			$.milenia_popup_gallery_builder.openInstance.unshift(this);
			var base = this;
				base.scope = false;
				base.doc = $(document);
				base.body = $('#milenia_gallery_builder');
				base.instance	= $.milenia_popup_gallery_builder.openInstance.length;
				base.namespace	= '.milenia_popup_modal_' + base.instance;

			if (!base.el.length) return;

			base.container = base.el.parents('.img-item');
			base.modal	= $('.popup-modal', base.container);
			base.overlay = $('.popup-modal-overlay', base.container);

			var animEndEventNames = {
				'WebkitAnimation' : 'webkitAnimationEnd',
				'OAnimation' : 'oAnimationEnd',
				'msAnimation' : 'MSAnimationEnd',
				'animation' : 'animationend'
			};
			base.animEndEventName = animEndEventNames[ Modernizr.prefixed('animation') ];

			base.support = {
				animations: Modernizr.cssanimations,
				touch : Modernizr.touch,
				csstransitions : Modernizr.csstransitions
			};

			base.loadPopup();
		},
		loadPopup: function () {
			var base = this;

			if(!base._fieldsEventsBinded) {
				base._bindFieldEvents();
			}

			base.modal.addClass('modal-show');
			base.onLoadCallback();
			base.behavior();
		},
		_bindFieldEvents: function() {
			var base = this;

			base.modal.off('click.MileniaGalleryBuilder').on('click.MileniaGalleryBuilder', '.popup-modal-manage-key-value-row', function(event){
				base[$(this).data('action') == 'remove' ? 'removeKeyValueRow' : 'addKeyValueRow'].call(base, $(this));
				event.preventDefault();
			});
		},
		removeKeyValueRow: function($invoker) {
			var currentRow;
			if(!$invoker || !$invoker.length) return;

			currentRow = $invoker.closest('.popup-modal-key-value-row');

			if(currentRow.length) {
				currentRow.remove();
			}
		},
		addKeyValueRow: function($invoker) {
			if(!$invoker || !$invoker.length) return;

			var namePlaceholder = $invoker.data('name-placeholder'),
				idPlaceholder = $invoker.data('id-placeholder'),
				currentIndex = $invoker.data('current-index'),
				nameAttr,
				tpl = '<div class="popup-modal-key-value-row">\
					<div class="popup-modal-key-value-col">\
						<label for="@key_id@">@key_label@</label>\
						<input id="@key_id@"\
							   type="text"\
						       class="widefat"\
						       name="@key_name@"\
						       value="@key_value@">\
					</div>\
					<div class="popup-modal-key-value-col">\
						<label for="@value_id@">@value_label@</label>\
						<input id="@value_id@"\
							   type="text"\
						       class="widefat"\
						       name="@value_name@"\
						       value="@value_value@">\
					</div>\
					<div class="popup-modal-key-value-col popup-modal-key-value-col-button">\
						<button type="button" data-action="remove" class="button button-primary popup-modal-manage-key-value-row"><i class="dashicons dashicons-trash"></i></button>\
					</div>\
				</div>';

			if(namePlaceholder == undefined || currentIndex == undefined) return;

			currentIndex++;
			nameAttr = compileTemplate(namePlaceholder, {
				'index': currentIndex
			});

			$invoker.data('currentIndex', currentIndex);

			$invoker.before(compileTemplate(tpl, {
				'key_id': compileTemplate(idPlaceholder, {
					'globalindex': currentIndex,
					'index': 0
				}),
				'key_name': nameAttr,
				'key_label': MileniaGalleryBuilderLocalizedData.key,
				'key_value': '',
				'value_id': compileTemplate(idPlaceholder, {
					'globalindex': currentIndex,
					'index': 1
				}),
				'value_name': nameAttr,
				'value_label': MileniaGalleryBuilderLocalizedData.value,
				'value_value': ''
			}));
		},
		closeModal: function () {
			var base = this;
				base.modal.removeClass('modal-show');

			base.scope = false;
			$.milenia_popup_gallery_builder.openInstance.shift();
		},
		onLoadCallback: function () {
			var callback = this.options.on_load;
			if (typeof callback == 'function') {
				callback.call(this);
			}
		},
		behavior: function () {
			var base = this;

			$('.popup-modal-close', base.modal)
				.add(base.overlay)
				.on('click' + base.namespace, function (e) {
				e.preventDefault();
				base.closeModal();
			});

		}
	}

	function compileTemplate(template, data) {
		var result,
			reg;

		if(!template || !data) return;

		result = template;

		for(var key in data) {
			reg = new RegExp('@' + key + '@', 'g');
			result = result.replace(reg, data[key]);
		}

		return result;
	};

	$(function () {
		$.milenia_gallery_builder.init();
	});

})(window.jQuery);
