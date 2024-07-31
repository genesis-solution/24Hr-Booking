;(function($){
    'use strict';

    if(!$) return;

    $.MileniaThemeFunctionality = {
        DOMReady: function() {
            if($.MileniaThemeFunctionality.modules.AJAXPortfolioItemLikes) {
    			$.MileniaThemeFunctionality.modules.AJAXPortfolioItemLikes.init('.apo-item-likes-btn, .apo-photo-likes-btn', 'increment_item_likes');
    		}

            if($.MileniaThemeFunctionality.modules.newsletterForm) {
                $.MileniaThemeFunctionality.modules.newsletterForm();
            }

            if($.MileniaThemeFunctionality.helpers.breadcrumb) {
                $.MileniaThemeFunctionality.helpers.breadcrumb();
            }
        },
        outerResourcesReady: function() {

        },
        modules: {
            AJAXPortfolioItemLikes: {
                init: function(selector, WPAction) {
                    if(!selector || (typeof selector !== 'string')) return;
                    if(!WPAction || (typeof WPAction !== 'string')) WPAction = 'increment_item_likes';

                    this.$body = $('body');

                    this.$body.on('click.MileniaAJAXPortfolioItemLikes', selector, {
                        self: this,
                        action: WPAction
                    }, this.request);
                },
                request: function(event) {
                    var self = event.data.self,
                        $btn = $(this);

                    event.preventDefault();

                    $.ajax({
                        type: 'POST',
                        data: {
                            data: $btn.data('item-likes-data'),
                            action: event.data.action,
                            AJAX_token: MileniaFunctionalityAJAXData.AJAX_token
                        },
                        dataType: 'json',
                        url: MileniaFunctionalityAJAXData.url,
                        success: function(response) {
                            if(response.status == 1) {
                                self.updateButtonState($btn, response);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {

                        }
                    });
                },

                updateButtonState: function($button, data) {
                    var $targetElement = $button.find('.apo-item-likes');

                    $button[data.state == 'liked' ? 'addClass' : 'removeClass']('apo-item-likes-btn-liked')
                            .data('item-likes-liked', data.state == 'liked');

                    if($targetElement.length) {
                        $targetElement.text(data.likes);
                    }

                    this.updateSiblingsElements($button, data);
                },

                updateSiblingsElements: function($button, data) {
                    var $siblings = $('[data-post-data]'),
                        likesData = $button.data('item-likes-data');

                    if(!$siblings.length) return;

                    $siblings.each(function(index, element) {
                        var $element = $(element),
                            postData = $element.data('post-data');

                        if(likesData.item_id && postData.item_id && likesData.item_id == postData.item_id) {
                            postData.likes = data.likes;
                            $element.data('post-data', postData).attr('data-post-data', JSON.stringify(postData));
                        }
                    });
                }
            },
            newsletterForm: function($form) {
                var $forms = $form && $form.length ? $form : $('form.widget_wysija');

                if($forms.length) {
                    $forms.each(function(index, element){
                        var $element = $(element),
                            $input = $element.find('input[type="text"]'),
                            $wrapper = $('<div></div>', {
                                class: 'milenia-singlefield-form'
                            }),
                            $submit;

                        if($input.length) $input = $input.filter(function(index, input){
                            return !($(input).parent('.abs-req').length);
                        });

                        if($input.length) {
                            $input.wrap($wrapper);
                            $input.after('<button type="submit"><span class="icon icon-envelope"></span></button>');
                            $submit = $element.find('input[type="submit"]');
                            if($submit.length) $submit.remove();
                        }
                    });
                }
            }
        },
        helpers: {
            breadcrumb: function() {
                var $path = $('.milenia-breadcrumb-path');

                if($path.length) {
                    $path.get(0).innerHTML = $path.get(0).innerHTML.replace(new RegExp(', ', 'g'), ',');
                }
            }
        }
    };

    $(function() {
        $.MileniaThemeFunctionality.DOMReady();
    });

    $(window).on('load', function(event) {
        $.MileniaThemeFunctionality.outerResourcesReady();
    });
})(window.jQuery);
