;(function($){
	'use strict';

	/**
	 * CustomSelect construct function
	 * @return undefined;
	 **/
	function CustomSelect(options){
		this.collection = $();

		this.config = {
			target: '.milenia-custom-select',
			cssPrefix: 'milenia-'
		};

		options = options || {};
		$.extend(this.config, options);

		this.collection = this.collection.add($(this.config.target));

		if(this.collection.length) this.build().bindEvents();

	}

	/**
	 * Creates necessary select elements and adds them to container element
	 * @return undefined;
	 **/
	CustomSelect.prototype.build = function(){

		var self = this;

		this.collection.each(function(index, element) {
			var $element = $(element),
				$select = $element.children('select'),
				$selectedOption,
				$optionsList,
				options,
				selectedFlag = false;

			if (!$select.length) return;

			$select.hide();

			options = $select.find('option');

			$selectedOption = $('<div></div>', {
				class: self.config.cssPrefix + 'selected-option',
				text: $select.data('default-text')
			});

			$optionsList = $('<ul></ul>', {
				class: self.config.cssPrefix + 'options-list milenia-list--unstyled'
			});

			for(var i = 0, l = options.length; i < l; i++){

				var option = options.eq(i);

				var li = $('<li></li>', {
					'text': option.text(),
					'data-value': option.val()
				});

				if(option.attr('selected')){
					li.addClass(self.config.cssPrefix + 'active');
					$selectedOption.text(option.text());
					selectedFlag = true;
				}

				$optionsList.append(li);

			}

			if(!$select.data('default-text') && !selectedFlag){

				$selectedOption.text(options.eq(0).text());
				$optionsList.children('li').eq(0).addClass(self.config.cssPrefix + 'active');

			}

			$element.append($selectedOption);
			$element.append($optionsList);

		});

		return this;

	}

	CustomSelect.prototype.toDefaultState = function(e){

		e.preventDefault();
		e.stopPropagation();

		var $this = $(this),
			self = e.data.self,
			$element = $this.closest(self.config.target);

		if($element.length && !$element.hasClass(self.config.cssPrefix + 'opened')){
			$element.removeClass(self.config.cssPrefix + 'over');
		}

	}

	/**
	 * Binds events to select elements
	 * @return undefined;
	 **/
	CustomSelect.prototype.bindEvents = function(){

		var self = this;

		$('body').on('click.MadCustomSelects', this.config.target + ' .' + this.config.cssPrefix + 'selected-option', function(e) {
			var $element = $(this).closest(self.config.target);

			if($element.length) {
				$element.addClass(self.config.cssPrefix + 'over');
				$element.toggleClass(self.config.cssPrefix + 'opened');
			}

			e.stopPropagation();
		}).on('focus.MadCustomSelects', this.config.target + ' select', function(e) {
			var $element = $(this).closest(self.config.target);

			$element.addClass(self.config.cssPrefix + 'opened');
			e.preventDefault();
		}).on('click.MadCustomSelects', this.config.target + ' .' + this.config.cssPrefix + 'options-list li', function(e) {

			var $this = $(this),
				value = $this.data('value'),
				$element = $this.closest(self.config.target),
				$select = $element.find('select'),
				$selectedOption = $element.find('.' + self.config.cssPrefix + 'selected-option'),
				$options = $select.find('option'),
				$currentOption;

			$this.addClass(self.config.cssPrefix + 'active').siblings().removeClass(self.config.cssPrefix + 'active');

			$selectedOption.text($this.text());

			if($options.length) {
				$currentOption = $options.filter('[value="'+value+'"]');
				$options.not($currentOption).removeAttr('selected');
				if($currentOption.length) {
					$currentOption.prop('selected', 'true');
				}
			}

			$select.val(value);
			$select.trigger('change');
			$element.removeClass(self.config.cssPrefix + 'opened');

			e.stopPropagation();
		}).on('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', this.config.target + ' .' + this.config.cssPrefix + 'options-list', {self: this},  this.toDefaultState);

		$(document).on('click.selectFocusOut', function(e){
			e.stopPropagation();
			if(!$(e.target).closest('.' + self.config.cssPrefix + 'custom-select').length) $('.' + self.config.cssPrefix +  'custom-select').removeClass(self.config.cssPrefix + 'opened');
		});

	}

	$.extend({
		MadCustomSelect: function(options) {
			return new CustomSelect(options);
		}
	});

})(jQuery);
