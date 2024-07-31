/*
|--------------------------------------------------------------------------
| milenia.contact-form.js
|--------------------------------------------------------------------------
| Defines contact form module.
*/
var MileniaContactForm = (function($){
	'use strict';

	var _config = {
		url: 'php/contact.php',
		onSuccess: function(data){},
		onError: function(data){}
	};

	function ContactForm(form, config) {
		var self = this;

		this.form = form;
		this.config = config;

		this.initValidator();
		form.data('config', config);
	};

	ContactForm.prototype.initValidator = function(){

		var self = this,
			form = this.form.get(0);

		if(!(form instanceof HTMLFormElement) || !window.Validator) return;

		this.form.data('validator', new Validator({
			form: form,
			cssPrefix: 'milenia-',
			incorrectClass: 'invalid',
			correctClass: 'valid',
			rules: [
				{
					element: form.elements.cf_name,
					name: 'Name',
					rules: {
						empty: null
					}
				},
				{
					element: form.elements.cf_message,
					name: 'Message',
					rules: {
						empty: null,
						min: 10
					}
				},
				{
					element: form.elements.cf_email,
					name: 'Email',
					rules: {
						empty: null,
						pattern: /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i
					}
				}
			],
			onIncorrect: function(errorsList){
				var previousMessage = $(form).siblings('.milenia-alert-box-error');

				if(Milenia && Milenia.modules && Milenia.modules.alertMessage) {
					Milenia.modules.alertMessage({
						target: self.form,
						type: 'error',
						message: errorsList
					});
				}
			},
			onCorrect: self.send
		}));

	};

	ContactForm.prototype.send = function() {
		var $form = $(this),
			config = $form.data('config');

		$.ajax({
			url: config.url,
			type: 'POST',
			dataType: 'json',
			data: $form.serialize(),
			success: function(data){
				if(data.status && data.status == 'fail') {
					if(Milenia && Milenia.modules && Milenia.modules.alertMessage) {
						Milenia.modules.alertMessage({
							target: $form,
							type: 'error',
							message: data.errors
						});
					}
					$form.trigger('milenia.contactFormMessage');
					config.onError.call($form, data);
				}
				else if(data.status && data.status == 'success') {
					if(Milenia && Milenia.modules && Milenia.modules.alertMessage) {
						Milenia.modules.alertMessage({
							target: $form,
							type: 'success',
							message: data.statusText
						});
					}
					$form.find('input, textarea').val('');
					$form.trigger('milenia.contactFormMessage');
					config.onSuccess.call($form, data);
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				if(Milenia && Milenia.modules && Milenia.modules.alertMessage) {
					Milenia.modules.alertMessage({
						target: $form,
						type: 'error',
						message: errorThrown
					});
				}
				$form.trigger('milenia.contactFormMessage');
				config.onError.call($form, arguments);
			}
		});
	}

	return {
		init: function( collection, config ) {
	   		if(!collection || !collection.length) return false;
	   		config = config && $.isPlainObject(config) ? $.extend(true, {}, _config, config) : _config;

	   		return collection.each(function(i, el){
	   			var $this = $(el);
	   			if($this.data('ContactForm')) return;
	   			$this.data('ContactForm', new ContactForm($this, config));
	   		});
	   	}
	}


})(jQuery);
