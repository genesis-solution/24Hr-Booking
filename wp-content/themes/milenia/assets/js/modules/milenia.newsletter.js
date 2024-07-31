/*
|--------------------------------------------------------------------------
| apolo.newsletter.js
|--------------------------------------------------------------------------
| Defines subscribe module.
*/
;(function($){
	'use strict';

	if(!('Apolo' in $)) {
		throw new Error('apolo.core.js file must be included.');
	};

	var _config = {
		url: 'php/subscribe.php',
		onSuccess: function(data){},
		onError: function(data){}
	};

	$.Apolo.modules.newsletter = function( collection, config ) {
		if(!collection || !collection.length) return false;
		config = config && $.isPlainObject(config) ? $.extend(true, {}, _config, config) : _config;

		return collection.each(function(i, el){
			var $this = $(el);
			if($this.data('Newsletter')) return;
			$this.data('Newsletter', new Newsletter($this, config));
		});
	};

	function Newsletter(form, config) {
		var self = this;

		this.form = form;
		this.config = config;

		this.initValidator();
		form.data('config', config);
	};

	Newsletter.prototype.initValidator = function(){

		var self = this;

		if(!(this.form.get(0) instanceof HTMLFormElement) || !window.Validator) return;

		this.form.data('validator', new Validator({
			form: self.form.get(0),
			cssPrefix: 'milenia-',
			incorrectClass: 'invalid',
			correctClass: 'valid',
			rules: [
				{
					element: self.form.get(0).elements['email'],
					name: 'Email',
					rules: {
						empty: null,
						pattern: /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i
					}
				}
			],
			onIncorrect: function(errorsList){

				$.Apolo.modules.alertMessage({
					target: self.form,
					type: 'error',
					icon: 'warning',
					message: errorsList
				});

			},
			onCorrect: self.send
		}));

	};

	Newsletter.prototype.send = function() {
		var $form = $(this),
			config = $form.data('config');

		$.ajax({
			url: config.url,
			type: 'POST',
			dataType: 'json',
			data: $form.serialize(),
			success: function(data){
				if(data.status && data.status == 'fail') {
					$.Apolo.modules.alertMessage({
						target: $form,
						type: 'error',
						message: data.errors,
						icon: 'warning'
					});
					config.onError.call($form, data);
				}
				else if(data.status && data.status == 'success') {
					$.Apolo.modules.alertMessage({
						target: $form,
						type: 'success',
						message: data.statusText,
						icon: 'check'
					});
					config.onSuccess.call($form, data);
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				$.Apolo.modules.alertMessage({
					target: $form,
					type: 'error',
					message: errorThrown,
					icon: 'warning'
				});
				config.onError.call($form, arguments);
			}
		});
	}


})(jQuery);