;(function($){
    'use strict';

    var $body = $('body');

    $.MileniaAdmin = {};
    $.MileniaAdmin.helpers = {};

    $.MileniaAdmin.DOMReady = function() {
        if(this.helpers.metaBoxConditionalLogicPostFormat) {
            this.helpers.metaBoxConditionalLogicPostFormat.init($('#post-formats-select'), {
                optionsBlockIDMask: 'milenia-post-options-%s%'
            });
        }

        if(this.helpers.uploadButton) this.helpers.uploadButton();
    };

    $.MileniaAdmin.outerResourcesReady = function() {
        if(this.helpers.independentLinks) this.helpers.independentLinks('.vc_ui-button');
        if(this.helpers.adminIconPicker) this.helpers.adminIconPicker();

        var $formItems = $('.rwmb-select, .milenia-styled-select, .rwmb-checkbox, .milenia-styled-input, .rwmb-number');

        if($formItems && $.fn.styler) {
            $formItems.styler();
        }

        $.MileniaAdmin.LinkUnderliner.init($('a.milenia-admin-underlined-link'));


    };

    $.MileniaAdmin.LinkUnderliner = {
        _$collection: $(),
        _config: {
            except: null,
            exceptClass: null
        },
		init: function($collection, config) {
			var self = this,
				$currentFilteredCollection;

			if(!$collection.length) return;

            this.config = $.extend(true, {}, this._config, $.isPlainObject(config) ? config : {});

			if(!this._bindedEvents) this._bindEvents();

			$currentFilteredCollection = $();

			$collection.each(function(index, element){
				var $element = $(element);

				if(self._$collection.filter($element).length) return;

                if($element.is(self.config.except)) {
                    $element.addClass(self.config.exceptClass);
                    return;
                }

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

			$(window).on('resize.MileniaLinksUnderline', function() {
				if(self.resizeTimeOutId) clearTimeout(self.resizeTimeOutId);

				self.resizeTimeOutId = setTimeout(function(){
					self.toUnderline(self._$collection);
				}, 100);
			});
		},
		toUnderline: function($collection) {
			var self = this;

			if(!$collection.length) return;

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

    $(function(){
        $.MileniaAdmin.DOMReady();
    });

    $(window).on('load', function(event){
        $.MileniaAdmin.outerResourcesReady();
    });

    $.MileniaAdmin.helpers.adminIconPicker = function() {
        var $iconpickers = $('.milenia-admin-iconpicker');

        if($iconpickers.length) {
            $iconpickers.each(function(index, iconpicker){
                var $iconpicker = $(iconpicker),
                    $input = $iconpicker.find('input[type="hidden"]'),
                    $buttons = $iconpicker.find('[data-icon-value]');

                $buttons.removeClass('milenia-admin-iconpicker-active').filter('[data-icon-value="'+$input.val()+'"]').addClass('milenia-admin-iconpicker-active');

            });
        }

        $('body').on('click.MileniaAdmin', '.milenia-admin-iconpicker button', function(event) {
            var $button = $(this),
                $input = $button.closest('.milenia-admin-iconpicker').find('input[type="hidden"]'),
                value = $button.data('icon-value');

            $button.addClass('milenia-admin-iconpicker-active').parent().siblings().children().removeClass('milenia-admin-iconpicker-active');

            if($input.length) $input.val(value);
            event.preventDefault();
        });
    };



    $.MileniaAdmin.helpers.metaBoxConditionalLogicPostFormat = {};
    $.MileniaAdmin.helpers.metaBoxConditionalLogicPostFormat._config = {
        optionsBlockIDMask: 'post-options-%s%'
    };

    $.MileniaAdmin.helpers.metaBoxConditionalLogicPostFormat.init = function($inputsContainer, config) {
        var self;

        if(!$inputsContainer || !$inputsContainer.length) return;

        self = this;


        this.config = $.isPlainObject(config) ? $.extend(true, {}, this._config, config) : this._config;

        self.setOptionsBlockState($inputsContainer.find('input[type="radio"]:checked'));

        $inputsContainer.on('change.MileniaAdminMetaBoxPostFormat', 'input[type="radio"]', function(event) {
            self.setOptionsBlockState($(this));
            event.preventDefault();
        });
    };

    $.MileniaAdmin.helpers.metaBoxConditionalLogicPostFormat.setOptionsBlockState = function($input) {
        var postFormat = $input.val(),
            $optionsBlocks = $('div[id*="'+this.config.optionsBlockIDMask.replace('%s%', '')+'"]'),
            $activeOptionBlock = $('#' + this.config.optionsBlockIDMask.replace('%s%', postFormat));

        $optionsBlocks.not($activeOptionBlock).hide();
        $activeOptionBlock.show();
    };


    $.MileniaAdmin.helpers.independentLinks = function(selector) {
        $('a').each(function(index, element){
            var $el = $(element);

            if($el.is(selector)) $el.addClass('milenia-ln--independent');
        });
    };

    $.MileniaAdmin.helpers.uploadButton = function() {
        /*
        * Select/Upload image(s) event
        */
        $('body').off('click.MileniaAdminUploadButton').on('click.MileniaAdminUploadButton', '.milenia-upload-btn-select', function(e){
            e.preventDefault();

            var button = $(this),
                removeButton = button.siblings('.milenia-upload-btn-remove'),
                input = button.siblings('.milenia-upload-btn-input'),
                imagesContainer = button.siblings('.milenia-upload-btn-images'),
                widget, saveButton,
                isMultiple = !!button.data('multiple'),
                multipleValues,
                customUploader = wp.media({
                    title: window.MileniaAdminLocalization && window.MileniaAdminLocalization.custom_uploader_title || 'Insert Image',
                    library : {
                        type : 'image'
                    },
                    button: {
                        text: window.MileniaAdminLocalization && window.MileniaAdminLocalization.custom_uploader_title || 'Use this image'
                    },
                    multiple: isMultiple
                }).on('select', function(event) {
                    var selection, template;

                    if(!removeButton.length || !input.length) return;

                    if(isMultiple) {
                        selection = customUploader.state().get('selection');

                        if(selection) {
                            widget = button.closest('.widget');
                            multipleValues = [];

                            selection.each(function(attachment){
                                multipleValues.push(attachment.attributes.id);
                                template = '<figure class="milenia-upload-btn-image">\
                                    <img src="%image_url%" alt="">\
                                    <div class="milenia-upload-btn-image-controls">\
                                        <div class="milenia-upload-btn-image-control">\
                                            <label for="%id%">%label%</label>\
                                            <input type="text" class="widefat" id="%id%" name="%name%">\
                                        </div>\
                                    </div>\
                                </figure>';

                                if(imagesContainer.length) {
                                    imagesContainer.append(
                                        template
                                            .replace('%image_url%', attachment.attributes.sizes.full.url)
                                            .replace('%id%', imagesContainer.data('id') + '-' + attachment.attributes.id + '-' + 'href' )
                                            .replace('%id%', imagesContainer.data('id') + '-' + attachment.attributes.id + '-' + 'href' )
                                            .replace('%label%', imagesContainer.data('label'))
                                            .replace('%name%', imagesContainer.data('name') + '['+ attachment.attributes.id +']')
                                    );
                                }
                            });

                            button.hide();
                            input.val(multipleValues.join(','));
                            removeButton.show();

                            if(widget.length) {
                                saveButton = widget.find('.widget-control-save');
                                if(saveButton.length) saveButton.removeAttr('disabled');
                            }
                        }
                    }
                    else {
                        selection = customUploader.state().get('selection').first().toJSON();
                        if(selection) {
                            widget = button.closest('.widget');

                            button.hide();
                            input.val(selection.id);
                            removeButton.show();

                            if(imagesContainer.length) {
                                imagesContainer.html('<img src="'+selection.sizes.thumbnail.url+'" alt="">');
                            }

                            if(widget.length) {
                                saveButton = widget.find('.widget-control-save');
                                if(saveButton.length) saveButton.removeAttr('disabled');
                            }
                        }
                    }

                    /* if you sen multiple to true, here is some code for getting the image IDs
                    var attachments = frame.state().get('selection'),
                    attachment_ids = new Array(),
                    i = 0;
                    attachments.each(function(attachment) {
                    attachment_ids[i] = attachment['id'];
                    console.log( attachment );
                    i++;
                    });
                    */
                    })
                    .open();
                });

        /*
        * Remove image event
        */
        $('body').off('click.MileniaAdminUploadRemoveButton').on('click.MileniaAdminUploadRemoveButton', '.milenia-upload-btn-remove', function(){
            var removeButton = $(this),
                selectBtn = removeButton.siblings('.milenia-upload-btn-select'),
                input = removeButton.siblings('.milenia-upload-btn-input'),
                imagesContainer = removeButton.siblings('.milenia-upload-btn-images'),
                widget, saveButton;

            if(!selectBtn.length || !input.length) return;

            selectBtn.show();
            removeButton.hide();
            input.val('');

            widget = removeButton.closest('.widget');

            if(widget.length) {
                saveButton = widget.find('.widget-control-save');
                if(saveButton.length) saveButton.removeAttr('disabled');
            }

            if(imagesContainer.length) imagesContainer.html('');
        });
    };



})(window.jQuery);
