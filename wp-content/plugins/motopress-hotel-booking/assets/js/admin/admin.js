(function ($) {
	$(function () {
		MPHBAdmin.BookingsCalendar = can.Control.extend({}, {
	filtersForm: null,
	customPeriodWrapper: null,
	btnPeriodPrev: null,
	btnPeriodNext: null,
	periodEl: null,
	popup: null,
	init: function (el, args) {
		this.filtersForm = this.element.find('#mphb-bookings-calendar-filters');
		this.customPeriodWrapper = this.filtersForm.find('.mphb-custom-period-wrapper');
		this.btnPeriodPrev = this.filtersForm.find('.mphb-period-prev');
		this.btnPeriodNext = this.filtersForm.find('.mphb-period-next');
		this.periodEl = this.filtersForm.find('#mphb-bookings-calendar-filter-period');
		this.searchDateFromEl = this.filtersForm.find('.mphb-search-date-from');
		this.searchDateToEl = this.filtersForm.find('.mphb-search-date-to');
		this.popup = new MPHBAdmin.CalendarPopup(this.element.find('#mphb-bookings-calendar-popup'), {
			clickTargets: this.element.find('.mphb-link-to-booking, .mphb-silent-link-to-booking')
		});
		this.initDatepickers();
	},
	initDatepickers: function () {
		var datepickers = this.filtersForm.find('.mphb-datepick');
		datepickers.datepick({
			dateFormat: MPHBAdmin.Plugin.myThis.data.settings.dateFormat,
			firstDay: MPHBAdmin.Plugin.myThis.data.settings.firstDay,
			showSpeed: 0,
			showOtherMonths: true,
			monthsToShow: MPHBAdmin.Plugin.myThis.data.settings.numberOfMonthDatepicker,
			pickerClass: MPHBAdmin.Plugin.myThis.data.settings.datepickerClass,
			useMouseWheel: false
		});
	},
	'#mphb-bookings-calendar-filter-period change': function (el, e) {
		var period = $(el).val();
		if (period === 'custom') {
			this.customPeriodWrapper.removeClass('mphb-hide');
			this.btnPeriodNext.addClass('mphb-hide');
			this.btnPeriodPrev.addClass('mphb-hide');
		} else {
			this.customPeriodWrapper.addClass('mphb-hide');
			this.btnPeriodNext.removeClass('mphb-hide');
			this.btnPeriodPrev.removeClass('mphb-hide');
		}
	},
	'#mphb-booking-calendar-search-room-availability-status change': function (el, e) {
		var status = $(el).val();
		if (status === '') {
			this.searchDateFromEl.addClass('mphb-hide');
			this.searchDateToEl.addClass('mphb-hide');
		} else {
			this.searchDateFromEl.removeClass('mphb-hide');
			this.searchDateToEl.removeClass('mphb-hide');
		}
	}

});

MPHBAdmin.CalendarPopup = can.Control.extend(
	{},
	{
		$title: null,
		$status: null,
		$preloader: null,
		$content: null,
		$editLink: null,

		titleText: '',
		errorText: '',
		statuses: {},

		ajaxUrl: '',
		ajaxAction: 'mphb_get_admin_calendar_booking_info',
		ajaxNonce: '',

		init: function (element, args) {
			if (!element || element.length == 0 || args == undefined || args.clickTargets == undefined) {
				return;
			}

			var pluginData = MPHBAdmin.Plugin.myThis.data;
			var translations = pluginData.translations;

			this.titleText = translations.bookingId;
			this.errorText = translations.errorHasOccured;
			this.statuses = translations.bookingStatuses;

			this.ajaxUrl = pluginData.ajaxUrl;
			this.ajaxNonce = pluginData.nonces[this.ajaxAction];

			this.$title = element.find('.mphb-title');
			this.$status = element.find('.mphb-status');
			this.$preloader = element.find('.mphb-preloader');
			this.$content = element.find('.mphb-content');
			this.$editLink = element.find('.mphb-edit-button');

			args.clickTargets.on('click', this.onClick.bind(this));
		},

		onClick: function (event) {
			event.preventDefault();

			// Get booking ID
			var element = $(event.target);
			var bookingId = parseInt(element.data('booking-id'));

			if (isNaN(bookingId)) {
				return;
			}

			// Update booking ID and edit URL
			var editUrl = element.attr('href');

			this.$title.text(this.titleText.replace('%s', bookingId));
			this.$editLink.attr('href', editUrl);

			// Update status
			var bookingStatus = element.data('booking-status');

			if (this.statuses.hasOwnProperty(bookingStatus)) {
				this.$status.text(this.statuses[bookingStatus]);
				this.$status.attr('class', 'mphb-status mphb-status-' + bookingStatus);
				this.show(this.$status);
			} else {
				this.hide(this.$status);
			}

			// Show the popup
			this.show();

			this.loadBookingDetails(bookingId);
		},

		'.mphb-close-popup-button, .mphb-popup-backdrop click': function () {
			this.hide();
		},

		loadBookingDetails: function (bookingId) {

			this.beforeLoad();

			var self = this;

			$.ajax({
				url: this.ajaxUrl,
				type: 'GET',
				dataType: 'json',
				data: {
					action: this.ajaxAction,
					mphb_nonce: this.ajaxNonce,
					booking_id: bookingId
				},
				success: function (response) {
					self.updateContent(response.data);
				},
				error: function (response) {

					console.error(response);

					self.setMessage(self.errorText);

					if (undefined !== response.responseJSON.data.errorMessage) {
						console.error(response.responseJSON.data.errorMessage);
					} else {
						console.error(response);
					}
				},
				complete: function () {
					self.afterLoad();
				}
			});
		},

		updateContent: function (htmlContent) {
			this.$content.html(htmlContent);
		},

		setMessage: function (message) {
			this.updateContent('<p>' + message + '</p>');
		},

		beforeLoad: function () {
			this.$content.empty();
			this.show(this.$preloader);
		},

		afterLoad: function () {
			this.hide(this.$preloader);
		},

		show: function (element) {
			element = element || this.element;
			element.removeClass('mphb-hide');
		},

		hide: function (element) {
			element = element || this.element;
			element.addClass('mphb-hide');
		}
	}
);

/**
 * @since 3.5.0
 */
MPHBAdmin.ExportBookings = can.Control.extend(
	{},
	{
		rooms: null, // select[name="room"]
		statuses: null, // select[name="status"]
		startDate: null, // input.mphb-export-start-date
		endDate: null, // input.mphb-export-end-date
		searchBy: null, // select[name="search_by"]
		columnsFieldset: null, // fieldset.mphb-export-columns
		exportColumns: null, // input[type="checkbox"]
		submitButton: null, // button.submit-button
		preloader: null, // span.mphb-preloader
		progressBar: null, // div.mphb-progress
		progressBack: null, // .mphb-progress__bar
		progressText: null, // .mphb-progress__text
		cancelButton: null, // button.cancel-button
		errorsWrapper: null, // div.mphb-errors-wrapper

		timeoutInterval: 1000,
		shortInterval: 500,

		checkTimer: null,
		cancelTimer: null,

		messages: {
			error: '',
			processing: '',
			cancelling: ''
		},

		init: function (element, args) {
			if (element.length == 0) {
				return;
			}

			this.initMessages();
			this.initElements(element);
			this.initDatepickers();

			// Did the previous process finished it's work?
			if (MPHBAdmin.Plugin.myThis.data.settings.isExportingBookings) {
				this.alreadyStarted();
				this.checkTimer = setTimeout(this.checkExport.bind(this), 0);
			}
		},

		initMessages: function () {
			var translations = MPHBAdmin.Plugin.myThis.data.translations;

			this.messages.error = translations.errorHasOccured;
			this.messages.processing = translations.processing;
			this.messages.cancelling = translations.cancelling;
		},

		initElements: function (root) {
			this.rooms = root.find('select[name="room"]');
			this.statuses = root.find('select[name="status"]');
			this.startDate = root.find('.mphb-export-start-date');
			this.endDate = root.find('.mphb-export-end-date');
			this.searchBy = root.find('select[name="search_by"]');
			this.columnsFieldset = root.find('.mphb-export-columns');
			this.exportColumns = this.columnsFieldset.find('input[type="checkbox"]');
			this.submitButton = root.find('.submit-button');
			this.preloader = root.find('.mphb-preloader');
			this.progressBar = root.find('.mphb-progress');
			this.progressBack = this.progressBar.children('.mphb-progress__bar');
			this.progressText = this.progressBar.children('.mphb-progress__text');
			this.cancelButton = root.find('.cancel-button');
			this.errorsWrapper = root.find('.mphb-errors-wrapper');
		},

		initDatepickers: function () {
			var pluginSettings = MPHBAdmin.Plugin.myThis.data.settings;

			var settings = {
				dateFormat: pluginSettings.dateFormat,
				firstDay: pluginSettings.firstDay,
				showSpeed: 0,
				showOtherMonths: true,
				monthsToShow: pluginSettings.numberOfMonthDatepicker,
				pickerClass: pluginSettings.datepickerClass,
				useMouseWheel: false
			};

			this.startDate.datepick(settings);
			this.endDate.datepick(settings);
		},

		".submit-button click": function (element, event) {
			event.preventDefault();

			this.beforeStart();
			this.startExport();
		},

		".cancel-button click": function (element, event) {
			event.preventDefault();

			clearTimeout(this.checkTimer);

			this.afterCancel();
			this.cancelExport();
		},

		startExport: function () {
			var action = 'mphb_export_bookings_csv',
				data = MPHBAdmin.Plugin.myThis.data,
				self = this;

			$.ajax({
				url: data.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: action,
					mphb_nonce: data.nonces[action],
					args: this.getValues()
				},
				success: function (response) {
					if (response.hasOwnProperty('success') && response.success) {
						self.afterStart();
						self.checkTimer = setTimeout(self.checkExport.bind(self), self.shortInterval);
					} else {
						self.badResponse(response.success ? null : response.data.message);
					}
				},
				error: this.badResponse.bind(this)
			});
		},

		checkExport: function () {
			var action = 'mphb_check_bookings_csv',
				data = MPHBAdmin.Plugin.myThis.data,
				self = this;

			$.ajax({
				url: data.ajaxUrl,
				type: 'GET',
				dataType: 'json',
				data: {
					action: action,
					mphb_nonce: data.nonces[action]
				},
				success: function (response) {
					if (response.hasOwnProperty('success') && response.success) {
						if (response.data.finished) {
							self.setProgress(100);
							if (response.data.file) {
								self.downloadFile(response.data.file);
							} else {
								self.setMessage(self.messages.error);
							}
							self.afterEnd();
						} else {
							self.setProgress(Math.floor(response.data.progress));
							self.checkTimer = setTimeout(self.checkExport.bind(self), self.timeoutInterval);
						}
					} else {
						self.badResponse();
					}
				},
				error: this.badResponse.bind(this)
			});
		},

		cancelExport: function () {
			var action = 'mphb_cancel_bookings_csv',
				data = MPHBAdmin.Plugin.myThis.data,
				self = this;

			$.ajax({
				url: data.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: action,
					mphb_nonce: data.nonces[action]
				},
				success: function (response) {
					if (response.hasOwnProperty('success') && response.success) {
						if (!response.data.cancelled) {
							self.cancelTimer = setTimeout(self.cancelExport.bind(self), self.timeoutInterval);
						} else {
							self.afterEnd();
						}
					} else {
						self.badResponse();
					}
				},
				error: this.badResponse.bind(this)
			});
		},

		badResponse: function (message) {
			if (message == undefined) {
				message = this.messages.error;
			}

			if (message != '') {
				this.setMessage(message);
			}

			this.afterEnd();
		},

		downloadFile: function (file) {
			window.location = file;
		},

		setProgress: function (progress, text) {
			if (text == undefined) {
				text = progress == 0 ? this.messages.processing : progress + '%';
			}

			this.progressBack.css('width', progress + '%');
			this.progressText.text(text);

			if (progress < 100) {
				if (progress == 0) {
					this.progressBar.addClass('mphb-wait');
				} else {
					this.progressBar.removeClass('mphb-wait');
				}
			}
		},

		setMessage: function (message) {
			this.errorsWrapper.text(message).removeClass('mphb-hide');
		},

		beforeStart: function () {
			this.submitButton.prop('disabled', true);
			this.preloader.removeClass('mphb-hide');
			this.errorsWrapper.addClass('mphb-hide').empty();
			this.setProgress(0);
			this.progressBar.removeClass('mphb-hide');
			this.cancelButton.removeClass('mphb-hide');
		},

		afterStart: function () {
			this.cancelButton.prop('disabled', false);
		},

		alreadyStarted: function () {
			this.submitButton.prop('disabled', true);
			this.preloader.removeClass('mphb-hide');
			this.progressBar.removeClass('mphb-hide');
			this.cancelButton.removeClass('mphb-hide').prop('disabled', false);
		},

		afterCancel: function () {
			this.cancelButton.addClass('mphb-hide').prop('disabled', true);
			this.progressBar.addClass('mphb-wait');
			this.setProgress(100, this.messages.cancelling);
		},

		afterEnd: function () {
			this.submitButton.prop('disabled', false);
			this.preloader.addClass('mphb-hide');
			this.progressBar.addClass('mphb-hide');
			this.cancelButton.addClass('mphb-hide').prop('disabled', true);
		},

		getValues: function () {
			var values = {};

			values['room'] = this.rooms.val();
			values['status'] = this.statuses.val();
			values['start_date'] = this.startDate.val();
			values['end_date'] = this.endDate.val();
			values['search_by'] = this.searchBy.val();
			values['columns'] = $.map(this.exportColumns.filter(':checked'), function (element) { return element.value; });

			if (values.columns.length == 0) {
				values.columns = 'none'; // Otherwise $.ajax() will lose the field
			}

			return values;
		},

		".mphb-toggle-export-columns click": function (element, event) {
			event.preventDefault();
			this.columnsFieldset.toggleClass('mphb-hide');
		},

		".mphb-checkbox-select-all click": function (element, event) {
			event.preventDefault();
			this.exportColumns.filter(':not(:checked)').prop('checked', true);
		},

		".mphb-checkbox-unselect-all click": function (element, event) {
			event.preventDefault();
			this.exportColumns.filter(':checked').prop('checked', false);
		}
	}
);

/**
 * @see MPHBAdmin.format_price() in public/mphb.js
 */
MPHBAdmin.format_price = function (price, atts) {
	atts = atts || {};

	var defaultAtts = MPHBAdmin.Plugin.myThis.data.settings.currency;
	atts = $.extend({ 'trim_zeros': false }, defaultAtts, atts);

	price = MPHBAdmin.number_format(price, atts['decimals'], atts['decimal_separator'], atts['thousand_separator']);
	var formattedPrice = atts['price_format'].replace('%s', price);

	if (atts['trim_zeros']) {
		var regex = new RegExp('\\' + atts['decimal_separator'] + '0+$|(\\' + atts['decimal_separator'] + '\\d*[1-9])0+$');
		formattedPrice = formattedPrice.replace(regex, '$1');
	}

	var priceHtml = '<span class="mphb-price">' + formattedPrice + '</span>';

	return priceHtml;
};

MPHBAdmin.format_percentage = function (price, atts) {
	atts = atts || {};

	var defaultAtts = MPHBAdmin.Plugin.myThis.data.settings.currency;
	atts = $.extend({}, defaultAtts, atts);

	price = MPHBAdmin.number_format(price, atts['decimals'], atts['decimal_separator'], atts['thousand_separator']);
	var formattedPrice = price + '%';
	var priceHtml = '<span class="mphb-percentage">' + formattedPrice + '</span>';

	return priceHtml;
};

/**
 * @see MPHBAdmin.number_format() in public/mphb.js
 */
MPHBAdmin.number_format = function (number, decimals, dec_point, thousands_sep) {
	// + Original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// + Improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   Bugfix by: Michael White (http://crestidg.com)
	var sign = '', i, j, kw, kd, km;

	// Input sanitation & defaults
	decimals = decimals || 0
	dec_point = dec_point || '.'
	thousands_sep = thousands_sep || ','

	if (number < 0) {
		sign = '-';
		number *= -1;
	}

	i = parseInt(number = (+number || 0).toFixed(decimals)) + '';

	if ((j = i.length) > 3) {
		j = j % 3;
	} else {
		j = 0;
	}

	km = (j ? i.substr(0, j) + thousands_sep : '');
	kw = i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + thousands_sep);
	kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : '');

	return sign + km + kw + kd;
};

/**
 * @param {String} action Action name (without prefix "mphb_").
 * @param {Object} data
 * @param {Object} callbacks "success", "error", "complete".
 * @returns {Object} The jQuery XMLHttpRequest object.
 *
 * @since 3.7.0
 */
MPHBAdmin.post = function (action, data, callbacks) {
	var pluginData = MPHBAdmin.Plugin.myThis.data;

	if (action.substr(0, 4) !== 'mphb') {
		action = 'mphb_' + action;
	}

	data = $.extend(
		{
			action: action,
			mphb_nonce: pluginData.nonces[action]
		},
		data
	);

	var ajaxArgs = $.extend(
		{
			url: pluginData.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: data
		},
		callbacks
	);

	return $.ajax(ajaxArgs);
}

MPHBAdmin.Plugin = can.Construct.extend({
	myThis: null
}, {
	data: null,
	init: function (el, args) {
		MPHBAdmin.Plugin.myThis = this;
		this.data = MPHBAdmin._data;
		delete MPHBAdmin._data;
		var ctrls = $('.mphb-ctrl:not([data-inited])');
		this.setControls(ctrls);
	},
	getVersion: function () {
		return this.data.version;
	},
	getPrefix: function () {
		return this.data.prefix;
	},
	addPrefix: function (str, separator) {
		separator = (typeof separator !== 'undefined') ? separator : '-';
		return this.getPrefix() + separator + str;
	},
	setControls: function (ctrls) {
		var ctrl, type;
		$.each(ctrls, function () {
			type = $(this).attr('data-type');
			switch (type) {
				case 'text':
					break;
				case 'number':
					ctrl = new MPHBAdmin.NumberCtrl($(this));
					break;
				case 'total-price':
					ctrl = new MPHBAdmin.TotalPriceCtrl($(this));
					break;
				case 'price-breakdown':
					ctrl = new MPHBAdmin.PriceBreakdownCtrl($(this));
					break;
				case 'media':
					ctrl = new MPHBAdmin.MediaCtrl($(this));
					break;
				case 'datepicker':
					ctrl = new MPHBAdmin.DatePickerCtrl($(this));
					break;
				case 'color-picker':
					ctrl = new MPHBAdmin.ColorPickerCtrl($(this));
					break;
				case 'complex':
					ctrl = new MPHBAdmin.ComplexCtrl($(this));
					break;
				case 'complex-vertical':
					ctrl = new MPHBAdmin.ComplexVerticalCtrl($(this));
					break;
				case 'dynamic-select':
					ctrl = new MPHBAdmin.DynamicSelectCtrl($(this));
					break;
				case 'multiple-checkbox':
					ctrl = new MPHBAdmin.MultipleCheckboxCtrl($(this));
					break;
				case 'amount':
					ctrl = new MPHBAdmin.AmountCtrl($(this));
					break;
				case 'rules-list':
					ctrl = new MPHBAdmin.RulesListCtrl($(this));
					break;
				case 'notes-list':
					ctrl = new MPHBAdmin.NotesListCtrl($(this));
					break;
				case 'variable-pricing':
					ctrl = new MPHBAdmin.VariablePricingCtrl($(this));
					break;
				case 'action-button':
					ctrl = new MPHBAdmin.ActionButtonCtrl($(this));
					break;
				case 'install-plugin':
					ctrl = new MPHBAdmin.InstallButtonCtrl($(this));
					break;
			}
			$(this).attr('data-inited', true);
		});
	}
});

/**
 * @since 3.8
 */
MPHBAdmin.PopupForm = can.Control.extend(
	{}, // Static
	{
		$popup: null,
		$submitButton: null,

		// States
		isVisible: false,
		isSubmitable: true, // Leave sentence "canSubmit" for the method

		// Promise callbacks
		resolve: null,
		reject: null,

		lastInput: {}, // Last input data from show()

		init: function ($element, args) {
			this.$popup = $element;
			this.$submitButton = $element.find('.mphb-submit-popup-button');
		},

		/**
		 * @param {Object} inputData
		 *
		 * @private
		 */
		reset: function (inputData) {
			this.canSubmit(true);
		},

		/**
		 * @param {Object} data Optional.
		 * @returns {Promise}
		 *
		 * @public
		 */
		show: function (data) {
			if (this.isVisible) {
				return;
			}

			this.lastInput = data || {};

			// Make final preparations
			this.reset(this.lastInput);

			// Show popup
			this.$popup.removeClass('mphb-hide');
			this.isVisible = true;

			// Create promise
			var self = this;

			return new Promise(function (resolve, reject) {
				self.resolve = resolve;
				self.reject = reject;
			});
		},

		/**
		 * @private
		 */
		close: function () {
			if (!this.isVisible) {
				return;
			}

			// Hide popup
			this.$popup.addClass('mphb-hide');
			this.isVisible = false;

			// Reject the promise
			this.reject(new Error('Closed.'));

			this.resolve = this.reject = null;
		},

		/**
		 * @private
		 */
		submit: function () {
			if (!this.isVisible) {
				return;
			}

			// Hide popup
			this.$popup.addClass('mphb-hide');
			this.isVisible = false;

			// Fullfill the promise
			if (this.canSubmit()) {
				this.resolve(this.getData());
			} else {
				this.reject(new Error("Can't submit."));
			}

			this.resolve = this.reject = null;
		},

		/**
		 * @param {Boolean} isSubmitable Optional. New state to set.
		 * @returns {Boolean} The current state.
		 *
		 * @public
		 */
		canSubmit: function (isSubmitable) {
			if (isSubmitable != undefined) {
				this.isSubmitable = isSubmitable;
				this.$submitButton.prop('disabled', !this.isSubmitable);
			}

			return this.isSubmitable;
		},

		/**
		 * @returns {Object}
		 *
		 * @public
		 */
		getData: function () {
			return $.extend({}, this.lastInput);
		},

		".mphb-submit-popup-button click": function (element, event) {
			this.submit();
		},

		".mphb-close-popup-button, .mphb-popup-backdrop click": function (element, event) {
			this.close();
		}
	}
);

(function ($) {
	$('.mphb-remove-customer').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();

		var pluginData = MPHBAdmin.Plugin.myThis.data;
		var translations = pluginData.translations;
		var textOnDelete = translations.deleteConfirmation;

		var nonce = pluginData.nonces.mphb_remove_customer;

		var confirmDeleting = confirm(textOnDelete);

		if (confirmDeleting) {

			var customer = $(this);

			$.ajax(
				ajaxurl,
				{
					type: 'post',
					data: {
						action: 'mphb_remove_customer',
						itemId: customer.attr("data-item-key"),
						mphb_nonce: nonce
					},
					success: function (responce) {
						window.location.href = window.location.href;
					}
				}
			);
		}
	});

})(jQuery);

// table.wp-list-table
MPHBAdmin.AttributesCustomOrder = can.Control.extend(
	{},
	{
		ITEM_SELECTOR: 'tbody tr:not(.inline-edit-row)',

		TERM_ID_SELECTOR: '.check-column input',
		TERM_ID_CLASS: '.check-column',

		COLUMN_HANDLE: '<td class="column-handle"></td>',
		COLUMN_HANDLE_CLASS: '.column-handle',

		/**
		 * @param {jQuery} element table.wp-list-table
		 * @param {Array} args
		 */
		init: function (element, args) {
			this._super(element, args);

			// Add sortable handle to each item
			element.find('tr:not(.inline-edit-row)').append(this.COLUMN_HANDLE);
			element.find(this.COLUMN_HANDLE_CLASS).show();

			$(document).ajaxComplete(this.onAjaxComplete.bind(this));

			element.sortable({
				items: this.ITEM_SELECTOR,
				cursor: 'move',
				handle: this.COLUMN_HANDLE_CLASS,
				axis: 'y',
				opacity: 0.65,
				scrollSensitivity: 40,
				update: this.onUpdate.bind(this)
			});
		},

		onAjaxComplete: function (event, request, options) {
			if (request && request.readyState === 4 && request.status === 200 && options.data && (options.data.indexOf('_inline_edit') >= 0 || options.data.indexOf('add-tag') >= 0)) {
				this.addMissingSortHandles();
				$(document.body).trigger('init_tooltips');
			}
		},

		onUpdate: function (event, ui) {
			var termId = ui.item.find(this.TERM_ID_SELECTOR).val(); // This post ID
			var nextTermId = ui.item.next().find(this.TERM_ID_SELECTOR).val();

			// Show spinner
			ui.item.find(this.TERM_ID_SELECTOR).hide();
			ui.item.find(this.TERM_ID_CLASS).append('<span class="mphb-preloader"></span>');

			var self = this;

			$.ajax({
				url: MPHBAdmin.Plugin.myThis.data.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mphb_attributes_custom_ordering',
					mphb_nonce: MPHBAdmin.Plugin.myThis.data.nonces.mphb_attributes_custom_ordering,
					term_id: termId,
					next_term_id: nextTermId,
					taxonomy_name: MPHBAdmin.Plugin.myThis.data.settings.editTaxonomyName
				},
				complete: function (jqXHR, textStatus) {
					ui.item.find(self.TERM_ID_CLASS).find('.mphb-preloader').remove();
					ui.item.find(self.TERM_ID_SELECTOR).show();
				}
			});

			// Fix cell colors
			this.element.find('tbody tr').each(function (index, element) {
				if (index % 2 == 0) {
					$(this).addClass('alternate');
				} else {
					$(this).removeClass('alternate');
				}
			});
		},

		addMissingSortHandles: function () {
			var allRows = this.element.find('tbody > tr');
			var handleRows = this.element.find('tbody > tr > td' + this.COLUMN_HANDLE_CLASS).parent();

			if (allRows.length == handleRows.length) {
				return;
			}

			var self = this;

			allRows.each(function (index, element) {
				if (!handleRows.is(element)) {
					$(element).append(self.COLUMN_HANDLE);
				}
			});

			this.element.find(this.COLUMN_HANDLE_CLASS).show();
		}
	}
);

MPHBAdmin.ServiceQuantity = can.Control.extend(
	{},
	{
		$periodSelect: null,
		$quantityRows: null,
		$autoLimitCheckbox: null,
		$maxQuantityInput: null,

		init: function (element, args) {
			if (element.length == 0) {
				return;
			}

			this.$periodSelect = element.find('select[name="mphb_price_periodicity"]');
			this.$quantityRows = element.find('.form-table tr:nth-of-type(n + 3):not(:last-of-type)');
			this.$autoLimitCheckbox = element.find('input[name="mphb_is_auto_limit"][type="checkbox"]');
			this.$maxQuantityInput = element.find('input[name="mphb_max_quantity"]');

			// Add event listeners
			var self = this;

			this.$periodSelect.on('change', function (event) {
				var newPeriod = self.$periodSelect.val();
				self.onPeriodChange(newPeriod);
			});

			this.$autoLimitCheckbox.on('change', function (event) {
				var isAutoLimit = self.$autoLimitCheckbox.prop('checked');
				self.onAutoLimitChange(isAutoLimit);
			});

			// Init default state
			this.onPeriodChange(this.$periodSelect.val());
			this.onAutoLimitChange(this.$autoLimitCheckbox.prop('checked'));
		},

		onPeriodChange: function (newPeriod) {
			if (newPeriod != 'flexible') {
				this.$quantityRows.addClass('mphb-hide');
			} else {
				this.$quantityRows.removeClass('mphb-hide');
			}
		},

		onAutoLimitChange: function (isAutoLimit) {
			this.$maxQuantityInput.prop('readonly', isAutoLimit);
		}
	}
);

(function ($) {
	$(document.body).on('init_tooltips', function () {

		$('.mphb-help-tip').tipTip({
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200,
			'keepAlive': true
		});
	});

	// Tooltips
	$(document.body).trigger('init_tooltips');

})(jQuery);

MPHBAdmin.WPGallery = can.Construct.extend({
	myThis: null,
	getInstance: function () {
		if (MPHBAdmin.WPGallery.myThis === null) {
			MPHBAdmin.WPGallery.myThis = new MPHBAdmin.WPGallery();
		}
		return MPHBAdmin.WPGallery.myThis;
	}
},
	{
		frame: null,
		ctrl: null,
		init: function () {
			var self = this;
			MPHBAdmin.WPGallery.myThis = this;
			Attachment = wp.media.model.Attachment;

			wp.media.controller.MPHBGallery = wp.media.controller.FeaturedImage.extend({
				defaults: parent._.defaults({
					id: 'mphb-media-library-gallery',
					title: MPHBAdmin.Plugin.myThis.data.translations.roomTypeGalleryTitle,
					toolbar: 'main-insert',
					filterable: 'uploaded',
					library: wp.media.query({ type: 'image' }),
					multiple: 'add',
					editable: true,
					priority: 60,
					syncSelection: false
				}, wp.media.controller.Library.prototype.defaults),
				updateSelection: function () {
					var selection = this.get('selection'),
						ids = MPHBAdmin.WPGallery.myThis.ctrl.getValue(),
						attachments;
					if ('' !== ids && -1 !== ids) {
						attachments = parent._.map(ids.split(/,/), function (id) {
							return Attachment.get(id);
						});
					}
					selection.reset(attachments);
				}
			});

			wp.media.view.MediaFrame.MPHBGallery = wp.media.view.MediaFrame.Post.extend({
				// Define insert - MPHB state
				createStates: function () {
					var options = this.options;

					// Add the default states
					this.states.add([
						// Main states
						new wp.media.controller.MPHBGallery()
					]);
				},
				// Removing let menu from manager
				bindHandlers: function () {
					wp.media.view.MediaFrame.Select.prototype.bindHandlers.apply(this, arguments);
					this.on('toolbar:create:main-insert', this.createToolbar, this);

					var handlers = {
						content: {
							'embed': 'embedContent',
							'edit-selection': 'editSelectionContent'
						},
						toolbar: {
							'main-insert': 'mainInsertToolbar'
						}
					};

					parent._.each(handlers, function (regionHandlers, region) {
						parent._.each(regionHandlers, function (callback, handler) {
							this.on(region + ':render:' + handler, this[callback], this);
						}, this);
					}, this);
				},
				// Changing main button title
				mainInsertToolbar: function (view) {
					var controller = this;

					this.selectionStatusToolbar(view);

					view.set('insert', {
						style: 'primary',
						priority: 80,
						text: MPHBAdmin.Plugin.myThis.data.translations.addGalleryToRoomType,
						requires: { selection: true },
						click: function () {
							var state = controller.state(),
								selection = state.get('selection');

							controller.close();
							state.trigger('insert', selection).reset();
						}
					});
				}
			});

			this.frame = new wp.media.view.MediaFrame.MPHBGallery(parent._.defaults({}, {
				state: 'mphb-media-library-gallery',
				library: { type: 'image' },
				multiple: true
			}));

			this.frame.on('open', this.proxy('onOpen'));
			this.frame.on('insert', this.proxy('setImage'));
		},
		open: function (ctrl) {
			this.ctrl = ctrl;
			this.frame.open();
		},
		onOpen: function () {
			var frame = this.frame;
			frame.reset();
			var ids = this.ctrl.getIds();
			if (ids.length) {
				var attachment = null;
				ids.forEach(function (id) {
					attachment = wp.media.attachment(id);
					attachment.fetch();
					frame.state().get('selection').add(attachment);
				});
			}
		},
		setImage: function () {
			var ids = [];
			var models = this.frame.state().get('selection').models;
			$.each(models, function (key, model) {
				var attributes = model.attributes;
				ids.push(attributes.id);
			});
			this.ctrl.setValue(ids.join(','));
		}
	});
	
MPHBAdmin.Ctrl = can.Control.extend({
	renderValue: function (control) {
		var type = control.attr('data-type');
		return control.find('input[type="' + type + '"]').val();
	}
}, {
	parentForm: null,
	init: function (el, args) {
		this.parentForm = this.element.closest('form');
	}
});

/**
 * @requires ./ctrl.js
 *
 * @since 3.7.0
 */
MPHBAdmin.ActionButtonCtrl = MPHBAdmin.Ctrl.extend(
	{},
	{
		// Elements
		button: null,
		preloader: null,
		statusText: null,

		// Input parameters
		checkInterval: 1000,
		reloadAfter: false,
		redirectAfter: '',

		// Request & progress parameters
		actionName: '',
		inProgress: false,
		timeoutObject: null,
		iteration: 1, // Current iteration number
		undefinedError: '',

		init: function (element, args) {
			this._super(element, args);

			this.button = element.find('button');
			this.preloader = element.find('.mphb-preloader');
			this.statusText = element.find('.status-text');

			this.checkInterval = parseInt(this.button.data('check-interval'));
			this.reloadAfter = this.button.data('reload-after') === 'yes';
			this.redirectAfter = this.button.data('redirect-after');
			this.actionName = this.button.attr('name');
			this.undefinedError = MPHBAdmin.Plugin.myThis.data.translations.errorHasOccured;

			if (this.button.prop('disabled')) {
				// "Lock" the button if it was disabled from the beginning
				this.inProgress = true;
			}

			if (this.button.data('is-in-progress') === 'yes') {
				this.beforeStart();

				// Some requests may use 1 as init stage and 2+ as "getStatus" stages
				this.iteration = 2;

				this.makeRequest();
			}

			this.button.on('click', this.onClick.bind(this));
		},

		onClick: function (event) {
			event.preventDefault();

			if (!this.inProgress) {
				this.beforeStart();
				this.makeRequest();
			}
		},

		makeRequest: function () {
			var self = this;

			MPHBAdmin.post(
				this.actionName,
				{
					iteration: this.iteration // Starts from 1
				},
				{
					success: function (response) {
						// Show message
						if (response.data && response.data.message) {
							self.setStatus(response.data.message, !response.success);
						} else {
							self.setStatus('', false);
						}

						// Wait for next iteration or end right now
						if (response.success && response.data && response.data.inProgress) {
							self.iteration++;
							self.timeoutObject = setTimeout(self.makeRequest.bind(self), self.checkInterval);
						} else {
							self.afterEnd(response.success);
						}
					},
					error: function (jqXHR) {
						self.setStatus(self.undefinedError, true);
						self.afterEnd(false);
					}
				}
			);
		},

		beforeStart: function () {
			this.inProgress = true;
			this.iteration = 1;

			this.button.prop('disabled', true);
			this.preloader.removeClass('mphb-hide');
			this.statusText.text('').removeClass('error');
		},

		/**
		 * @param {Boolean} succeeded
		 */
		afterEnd: function (succeeded) {
			this.inProgress = false;
			this.preloader.addClass('mphb-hide');

			var enableButton = true;

			if (succeeded) {
				if (this.redirectAfter || this.reloadAfter) {
					enableButton = false;
				}

				if (this.redirectAfter) {
					window.location.href = this.redirectAfter;
				} else if (this.reloadAfter) {
					document.location.reload(true); // true - load new page from server
				}
			}

			if (enableButton) {
				this.button.prop('disabled', false);
			}
		},

		/**
		 * @param {String} text
		 * @param {Boolean} isError
		 */
		setStatus: function (text, isError) {
			this.statusText.text(text);
			this.statusText.toggleClass('error', isError);
		}
	}
);

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.AmountCtrl = MPHBAdmin.Ctrl.extend({
	renderValue: function (control) {
		var inputs = control.find('input[type="number"]:not(:disabled)');
		var renderType = control.children('.mphb-amount-inputs').attr('data-render-type');
		var formatFunction = renderType == 'price' ? MPHBAdmin.format_price : MPHBAdmin.format_percentage;

		if (inputs.length == 1) {
			return formatFunction(inputs.val(), { decimals: 4 });
		} else {
			var result = MPHBAdmin.Plugin.myThis.data.translations.adults;
			result += formatFunction($(inputs[0]).val(), { decimals: 4 });
			result += '<br />' + MPHBAdmin.Plugin.myThis.data.translations.children;
			result += formatFunction($(inputs[1]).val(), { decimals: 4 });
			return result;
		}
	}
}, {
	mainWrapper: null,
	singleInputGroup: null,
	multipleInputsGroup: null,
	commonAmountInput: null,
	adultsAmountInput: null,
	childrenAmountInput: null,

	dependencyCtrl: null,
	singleInputTriggers: [],
	multipleInputsTriggers: [],

	init: function (element, args) {
		this._super(element, args);

		this.mainWrapper = this.element.children('.mphb-amount-inputs');
		this.singleInputGroup = this.mainWrapper.children('.mphb-amount-single-input-group');
		this.multipleInputsGroup = this.mainWrapper.children('.mphb-amount-multiple-inputs-group');
		this.commonAmountInput = this.singleInputGroup.find('input.mphb-amount-common-input');
		this.adultsAmountInput = this.multipleInputsGroup.find('input.mphb-amount-adults-input');
		this.childrenAmountInput = this.multipleInputsGroup.find('input.mphb-amount-children-input');

		// Init dependency control
		var dependencyName = this.mainWrapper.attr('data-dependency');
		if (dependencyName) {
			var self = this;
			this.dependencyCtrl = this.element.closest('form').find('[name="' + dependencyName + '"]');
			this.dependencyCtrl.on('change', function (event) {
				var value = $(this).val();
				self.onTrigger(value);
			});
		}

		// Init triggers and flags
		this.singleInputTriggers = this.mainWrapper.attr('data-single-triggers').split(',');
		this.multipleInputsTriggers = this.mainWrapper.attr('data-multiple-triggers').split(',');
	},
	onTrigger: function (value) {
		var switchToSingle = this.singleInputTriggers.indexOf(value) != -1;
		var switchToMultiple = this.multipleInputsTriggers.indexOf(value) != -1;

		if (value.indexOf('percent') != -1) {
			this.mainWrapper.attr('data-render-type', 'percentage');
		} else {
			this.mainWrapper.attr('data-render-type', 'price');
		}

		if (switchToSingle) {
			this.switchToSingleInput();
		} else if (switchToMultiple) {
			this.switchToMultipleInputs();
		}
	},
	switchToSingleInput: function () {
		this.adultsAmountInput.prop('disabled', true);
		this.childrenAmountInput.prop('disabled', true);
		this.commonAmountInput.prop('disabled', false);
		this.multipleInputsGroup.addClass('mphb-hide');
		this.singleInputGroup.removeClass('mphb-hide');
	},
	switchToMultipleInputs: function () {
		this.commonAmountInput.prop('disabled', true);
		this.adultsAmountInput.prop('disabled', false);
		this.childrenAmountInput.prop('disabled', false);
		this.singleInputGroup.addClass('mphb-hide');
		this.multipleInputsGroup.removeClass('mphb-hide');
	}
});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.ColorPickerCtrl = MPHBAdmin.Ctrl.extend({}, {
	input: null,
	init: function (el, args) {

		this._super(el, args);

		this.input = this.element.find('input')

		this.input.spectrum({
			allowEmpty: true,
			preferredFormat: "hex",
			showInput: true,
			showInitial: true,
			showAlpha: false
		});

	}

});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.ComplexCtrl = MPHBAdmin.Ctrl.extend({}, {
	prototypeItem: null,
	itemsHolder: null,
	lastIndex: null,
	uniqid: null,
	itemSelector: 'tr',
	metaName: null,
	init: function (el, args) {
		this._super(el, args);
		this.uniqid = this.element.children('table').attr('data-uniqid');
		this.metaName = this.element.children('input[type="hidden"]:first-of-type').attr('name');
		this.initItemsHolder();
		this.initAddBtn();
		this.initDeleteBtns();
		this.preparePrototypeItem();
		this.initLastIndex();
		this.setKeys(this.itemsHolder.children(this.itemSelector));
	},
	makeItemsHolderSortable: function () {
		if (this.itemsHolder.parent().hasClass('mphb-separate-sortable-table')) {
			this.itemsHolder.sortable({
				handle: '.mphb-sortable-handle',
				cursor: 'move'
			});
		} else {
			this.itemsHolder.sortable();
		}
	},
	initLastIndex: function () {
		this.lastIndex = 0;
		var self = this;
		this.itemsHolder.children(this.itemSelector).each(function (index, item) {
			self.lastIndex = Math.max(self.lastIndex, parseInt($(item).attr('data-id')));
		});
	},
	initItemsHolder: function () {
		this.itemsHolder = this.element.children('table').children('tbody');
		if (this.itemsHolder.hasClass('mphb-sortable')) {
			this.makeItemsHolderSortable();
		}
	},
	initAddBtn: function () {
		var self = this;
		this.element.on('click', '.mphb-complex-add-item[data-id="' + this.uniqid + '"]', function (e) {
			e.preventDefault();
			self.addItem();
		})
	},
	initDeleteBtns: function () {
		var self = this;
		this.itemsHolder.on('click', '.mphb-complex-delete-item[data-id="' + this.uniqid + '"]', function (e) {
			e.preventDefault();
			self.deleteItem($(this).closest(self.itemSelector));
		});
	},
	preparePrototypeItem: function () {
		var item = this.itemsHolder.children('.mphb-complex-item-prototype');
		this.prototypeItem = item.clone();
		this.prototypeItem.removeClass('mphb-hide mphb-complex-item-prototype').find('[name]:not(.mphb-keep-disabled)').each(function () {
			$(this).removeAttr('disabled');
		});

		item.remove();
	},
	getIncIndex: function () {
		return ++this.lastIndex;
	},
	setKeys: function (wrappers) {
		var self = this;
		var name, id, forAttr, key, dependency, $wrapper;
		var keyRegEx = new RegExp('%key_' + this.uniqid + '%', 'g');
		var keyPlaceholder = '%key_' + this.uniqid + '%';
		wrappers.each(function (index, wrapper) {
			$wrapper = $(wrapper);
			key = $wrapper.attr('data-id');

			if (key === keyPlaceholder) {
				key = self.getIncIndex();
				$wrapper.attr('data-id', key);
			}
			$wrapper.find('[name*="[%key_' + self.uniqid + '%]"]').each(function () {
				name = $(this).attr('name').replace(keyRegEx, key);
				$(this).attr('name', name)
				if ($(this).attr('id')) {
					id = $(this).attr('id').replace(keyRegEx, key).replace(/\[|\]/g, '__');
					$(this).attr('name', name).attr('id', id);
				}
			});
			$wrapper.find('[for*="[%key_' + self.uniqid + '%]"]').each(function () {
				forAttr = $(this).attr('for').replace(keyRegEx, key).replace(/\[|\]/g, '__');
				$(this).attr('for', forAttr);
			});
			$wrapper.find('[data-dependency*="%key_' + self.uniqid + '%"]').each(function () {
				dependency = $(this).attr('data-dependency').replace(keyRegEx, key);
				$(this).attr('data-dependency', dependency);
			});
		});
	},
	clonePrototypeItem: function () {
		var clonedItem = this.prototypeItem.clone();
		this.setKeys(clonedItem);
		return clonedItem;
	},
	addItemToHolder: function (item) {
		this.itemsHolder.append(item);
	},
	deleteItem: function (item) {
		item.remove();
	},
	addItem: function () {
		var item = this.clonePrototypeItem();
		this.addItemToHolder(item);
		var ctrls = item.find('.mphb-ctrl:not([data-inited])');
		MPHBAdmin.Plugin.myThis.setControls(ctrls);
	}

});

/**
 *
 * @requires ./complex-ctrl.js
 */
MPHBAdmin.ComplexVerticalCtrl = MPHBAdmin.ComplexCtrl.extend({}, {
	itemSelector: 'tbody',
	lastIndexInput: null,
	minItemsCount: 0,
	init: function (el, args) {
		this._super(el, args);
		this.minItemsCount = this.itemsHolder.attr('data-min-items-count');
	},
	initLastIndex: function () {
		this.lastIndexInput = this.itemsHolder.find('>tfoot .mphb-complex-last-index');
		this.lastIndex = this.lastIndexInput.val();
	},
	getIncIndex: function () {
		var index = this._super();
		this.lastIndexInput.val(index);
		return index;
	},
	initItemsHolder: function () {
		this.itemsHolder = this.element.children('table');
	},
	addItemToHolder: function (item) {
		this.itemsHolder.children('tfoot').before(item);
	},
	disableDeleteButtons: function () {
		var deleteButtons = this.itemsHolder.children(this.itemSelector).children('.mphb-complex-item-actions-holder').find('.mphb-complex-delete-item');
		deleteButtons.attr('disabled', 'disabled').addClass('mphb-hide');
	},
	enableDeleteButtons: function () {
		var deleteButtons = this.itemsHolder.children(this.itemSelector).children('.mphb-complex-item-actions-holder').find('.mphb-complex-delete-item');
		deleteButtons.removeAttr('disabled').removeClass('mphb-hide');
	},
	updateItemActions: function () {
		var itemCount = this.itemsHolder.children(this.itemSelector).length;
		if (itemCount <= this.minItemsCount) {
			this.disableDeleteButtons();
		} else {
			this.enableDeleteButtons();
		}
	},
	updateDefaultItem: function () {
		var defaultRadio = this.itemsHolder.children(this.itemSelector).find('>.mphb-complex-item-actions-holder [name="' + this.metaName + '[default]"]');
		if (!defaultRadio.filter(':checked').length) {
			defaultRadio.first().attr('checked', 'checked');
		}
	},
	deleteItem: function (item) {
		this._super(item);
		this.updateItemActions();
		this.updateDefaultItem();
	},
	addItem: function () {
		this._super();
		this.updateItemActions();
		this.updateDefaultItem();
	}

});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.DatePickerCtrl = MPHBAdmin.Ctrl.extend({
	renderValue: function (control) {
		return control.find('input[type="text"]').val();
	}
}, {
	input: null,
	hiddenInput: null,
	init: function (el, args) {
		this._super(el, args);
		this.input = this.element.find('input[type="text"]');
		this.hiddenInput = this.element.find('input[type="hidden"]');

		this.fixDate();

		var self = this;

		if ($(this.input).data('dateMin')) {
			this.minDate = new Date($(this.input).data('dateMin'));
		}

		if ($(this.input).data('dateMax')) {
			this.maxDate = new Date($(this.input).data('dateMax'));
		}

		if ($(this.input).data('dependentAsMin')) {
			var dependentAsMin = $(this.input).data('dependentAsMin');
			this.dependentAsMinInput = $('input[name="' + dependentAsMin + '"]');
			this.setDependentMinDate = true;

			$(this.dependentAsMinInput).each(function () {
				$(this).data('dateMin', self.hiddenInput.val());
			});
		}

		if ($(this.input).data('dependentAsMax')) {
			var dependentAsMax = $(this.input).data('dependentAsMax');
			this.dependentAsMaxInput = $('input[name="' + dependentAsMax + '"]');
			this.setDependentMaxDate = true;

			$(this.dependentAsMaxInput).each(function () {
				$(this).data('dateMax', self.hiddenInput.val());
			});
		}

		if (!this.input.attr('readonly')) {
			this.input.datepick({
				dateFormat: MPHBAdmin.Plugin.myThis.data.settings.dateFormat,
				firstDay: MPHBAdmin.Plugin.myThis.data.settings.firstDay,
				altField: this.hiddenInput,
				altFormat: MPHBAdmin.Plugin.myThis.data.settings.dateTransferFormat,
				showSpeed: 0,
				showOtherMonths: false,
				monthsToShow: MPHBAdmin.Plugin.myThis.data.settings.numberOfMonthDatepicker,
				pickerClass: MPHBAdmin.Plugin.myThis.data.settings.datepickerClass,
				useMouseWheel: false,
				minDate: this.minDate,
				maxDate: this.maxDate,
				onSelect: function (dates) {
					if (self.setDependentMinDate) {
						$.each(self.dependentAsMinInput, function (i, el) {
							$(el).datepick('option', 'minDate', dates[0]);
						});
					}

					if (self.setDependentMaxDate) {
						$.each(self.dependentAsMaxInput, function (i, el) {
							$(el).datepick('option', 'maxDate', dates[0]);
						});
					}
				}
			});
		}

		if (!this.input.attr('required')) {
			var hiddenInput = this.hiddenInput;

			this.input.on('change', function () {
				var input = $(this);
				var value = input.val();

				if (value == '') {
					hiddenInput.val('');
				}
			});
		}
	},
	/**
	 * Fix date in customer date format
	 *
	 * @returns {undefined}
	 */
	fixDate: function () {
		if (!this.hiddenInput.val()) {
			//			this.input.val( '' );
		} else {
			var date = $.datepick.parseDate(MPHBAdmin.Plugin.myThis.data.settings.dateTransferFormat, this.hiddenInput.val());
			var fixedValue = $.datepick.formatDate(MPHBAdmin.Plugin.myThis.data.settings.dateFormat, date);
			this.input.val(fixedValue);
		}
	}
});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.DynamicSelectCtrl = MPHBAdmin.Ctrl.extend({}, {
	input: null,
	dependencyCtrl: null,
	ajaxAction: null,
	ajaxNonce: null,
	errorsWrapper: null,
	preloader: null,
	defaultValue: null,
	defaultOption: null,
	complexId: null,
	group: '',
	init: function (el, args) {
		this._super(el, args);
		this.input = this.element.find('select');
		this.defaultValue = this.input.attr('data-default');
		this.defaultOption = this.input.find('option[value="' + this.defaultValue + '"]').clone();
		this.errorsWrapper = this.element.find('.mphb-errors-wrapper');
		this.preloader = this.element.find('.mphb-preloader');
		this.ajaxAction = this.input.attr('data-ajax-action');
		this.ajaxNonce = this.input.attr('data-ajax-nonce');

		this.initDependencyCtrl();
		this.initComplexId();
		this.initGroup();
	},
	initDependencyCtrl: function () {
		var dependencyName = this.input.attr('data-dependency');
		this.dependencyCtrl = this.element.closest('form').find('[name="' + dependencyName + '"]');
		var self = this;
		this.dependencyCtrl.on('change', function (e) {
			self.updateList();
		}).on('focus', function (e) {
			self.hideErrors();
		});
	},
	initComplexId: function () {
		var complexRow = this.element.parents('tr[data-id]');
		if (complexRow.length > 0) {
			this.complexId = parseInt(complexRow.attr('data-id'));
		}
	},
	initGroup: function () {
		var complexWrapper = this.element.parents('.mphb-ctrl-rules-list');
		if (complexWrapper.length > 0) {
			this.group = complexWrapper.attr('data-group');
		}
	},
	setOptions: function (source) {
		var self = this;
		this.input.html(this.defaultOption.clone());
		$.each(source, function (value, label) {
			self.input.append($('<option />', {
				'value': value,
				'html': label
			}));
		});
	},
	updateList: function () {
		var self = this;
		this.hideErrors();
		this.showPreloader();
		this.input.html(this.defaultOption.clone());
		var data = {
			action: this.ajaxAction,
			mphb_nonce: this.ajaxNonce,
			formValues: this.parseFormToJSON()
		};
		$.ajax({
			url: MPHBAdmin.Plugin.myThis.data.ajaxUrl,
			type: 'GET',
			dataType: 'json',
			"data": data,
			success: function (response) {
				if (response.hasOwnProperty('success')) {
					if (response.success) {
						self.setOptions(response.data.options);
					} else {
						self.showError(response.data.message);
					}
				} else {
					self.showError(MPHBAdmin.Plugin.myThis.data.translations.errorHasOccured);
				}
			},
			error: function (jqXHR) {
				self.showError(MPHBAdmin.Plugin.myThis.data.translations.errorHasOccured);
			},
			complete: function (jqXHR) {
				self.hidePreloader();
			}
		});
	},
	parseFormToJSON: function () {
		var data = this.parentForm.serializeJSON();
		if (this.group != '') {
			data = data[this.group];
		}
		if (this.complexId != null) {
			data = data[this.complexId];
		}
		return data;
	},
	showPreloader: function () {
		this.preloader.removeClass('mphb-hide');
	},
	hidePreloader: function () {
		this.preloader.addClass('mphb-hide');
	},
	hideErrors: function () {
		this.errorsWrapper.empty().addClass('mphb-hide');
	},
	showError: function (message) {
		this.errorsWrapper.html(message).removeClass('mphb-hide');
	}

});

/**
 * @requires ./ctrl.js
 *
 * @since 3.8.1
 */
MPHBAdmin.InstallButtonCtrl = MPHBAdmin.Ctrl.extend(
	{}, // Static
	{
		$button: null,
		$preloader: null,
		$statusText: null,

		pluginSlug: '',
		pluginZipLink: '#',

		redirect: '',

		ajax: {
			url: '',
			action: 'mphb_install_plugin',
			nonce: ''
		},

		i18n: {
			unknownError: ''
		},

		init: function ($element, args) {
			this._super($element, args);

			// Find elements
			this.$button = $element.find('.button-row > button');
			this.$preloader = $element.find('.mphb-preloader');
			this.$statusText = $element.find('.status-text');

			// Ger parameters
			this.pluginSlug = this.$button.data('plugin-slug');
			this.pluginZipLink = this.$button.data('plugin-zip');
			this.redirect = this.$button.data('redirect');

			if (this.redirect === 'no') {
				this.redirect = false;
			} else if (this.redirect == undefined) {
				this.redirect = '';
			}

			// Other settings
			var pluginData = MPHBAdmin.Plugin.myThis.data;
			this.ajax.url = pluginData.ajaxUrl;
			this.ajax.nonce = pluginData.nonces[this.ajax.action];
			this.i18n.unknownError = pluginData.translations.errorHasOccured;

			// Add listeners
			this.$button.on('click', this.triggerInstall.bind(this));
		},

		triggerInstall: function (event) {
			event.preventDefault(); // Don't accidently submit the form
			this.beforeRequest(); // Reset classes and elements visibility

			var self = this;

			$.ajax({
				url: this.ajax.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: this.ajax.action,
					mphb_nonce: this.ajax.nonce,
					plugin_slug: this.pluginSlug,
					plugin_zip: this.pluginZipLink
				},
				success: function (response) {
					if (response.success) {
						// Success
						self.afterSuccess();

						if (response.data && response.data.message) {
							self.setMessage(response.data.message);
						}

						self.reloadOrRedirect();

					} else {
						// Failure
						self.afterFailure();

						if (response.data && response.data.message) {
							self.setError(response.data.message);
						} else {
							self.setError(self.i18n.unknownError);
						}
					}
				},
				/**
				 * @param {Object} jqXHR
				 *
				 * TODO Somehow recognize the success response.
				 */
				error: function (jqXHR) {
					// Here we got the logs like:
					//     ...
					//     <p>Unpacking the package...</p>
					//     <p>Installing the plugin...</p>
					//     <p>Plugin installed successfully.</p>
					self.afterSuccess();
					self.reloadOrRedirect();
				}
			});
		},

		beforeRequest: function () {
			this.$button.prop('disabled', true);
			this.$preloader.removeClass('mphb-hide');
			this.$statusText.addClass('mphb-hide').removeClass('error').html('');
		},

		afterSuccess: function () {
			this.$preloader.addClass('mphb-hide');
		},

		afterFailure: function () {
			this.$button.prop('disabled', false);
			this.$preloader.addClass('mphb-hide');
		},

		setMessage: function (message) {
			this.$statusText.html(message).removeClass('error mphb-hide');
		},

		setError: function (message) {
			this.$statusText.html(message).addClass('error').removeClass('mphb-hide');
		},

		reloadOrRedirect: function () {
			// Reload current page or redirect to a new one
			if (this.redirect !== false) {
				if (this.redirect == '') {
					document.location.reload(true); // TRUE - load new page from server
				} else {
					window.location.href = this.redirect;
				}
			}
		}
	}
);

/**
 * @requires ./ctrl.js
 *
 * @since 3.8.6
 */
MPHBAdmin.MediaCtrl = MPHBAdmin.Ctrl.extend(
	{},
	{
		$input: null,
		$preview: null,
		$addButton: null,
		$removeButton: null,

		isSingle: false,
		thumbSize: 'full',

		init: function ($element, args) {
			this._super($element, args);

			this.$input = this.element.find('input[type=hidden]');
			this.$preview = this.element.find('img').on('click', this.proxy('addMedia'));
			this.$addButton = this.element.find('.mphb-admin-organize-image-add').on('click', this.proxy('addMedia'));
			this.$removeButton = this.element.find('.mphb-admin-organize-image-remove').on('click', this.proxy('removeMedia'));

			this.isSingle = !!this.$input.attr('is-single');
			this.thumbSize = this.$input.attr('image-size');
		},

		/**
		 * @return {String}
		 *
		 * @since 3.8.6
		 */
		getValue: function () {
			return this.$input.val();
		},

		/**
		 * @return {Number[]}
		 *
		 * @since 3.8.6
		 */
		getIds: function () {
			var value = this.getValue();

			if (value === '') {
				return [];
			} else {
				var ids = value.split(',');
				return $.map(ids, function (id) { return parseInt(id); });
			}
		},

		/**
		 * @param {String} value
		 *
		 * @since 3.8.6
		 */
		setValue: function (value) {
			this.$input.val(value);

			this.updatePreview();
			this.updateButtons();
		},

		/**
		 * @since 3.8.6
		 */
		updateButtons: function () {
			var value = this.getValue();

			if (value === '') {
				this.$addButton.removeClass('mphb-hide');
				this.$removeButton.addClass('mphb-hide');
			} else {
				this.$addButton.addClass('mphb-hide');
				this.$removeButton.removeClass('mphb-hide');
			}
		},

		updatePreview: function () {
			var value = this.getValue();

			if (value === '') {
				this.$preview.addClass('mphb-hide').attr('src', '');
			} else {
				var attachment = null;

				if (this.isSingle) {
					attachment = wp.media.attachment(value);
				} else {
					var previewId = parseInt(this.getIds().shift());
					attachment = wp.media.attachment(previewId);
				}

				var previewSrc = attachment.attributes.sizes[this.thumbSize].url;

				this.$preview.removeClass('mphb-hide').attr('src', previewSrc);
			}
		},

		/**
		 * @param {Event} event
		 *
		 * @since 3.8.6
		 *
		 * TODO Select current attachment in isSingle=true mode.
		 */
		addMedia: function (event) {
			event.preventDefault();

			if (!this.isSingle) {
				MPHBAdmin.WPGallery.getInstance().open(this);
			} else {
				var image = wp.media({
					multiple: false
				});

				var self = this;

				image.open().on('select', function (event) {
					var uploadedImage = image.state().get('selection').first();
					var imageId = uploadedImage.toJSON().id;

					$('#image_url').val(imageId);

					self.setValue(imageId);
				});
			}
		},

		/**
		 * @param {Event} event
		 *
		 * @since 3.8.6
		 */
		removeMedia: function (event) {
			event.preventDefault();

			this.setValue('');
		}
	}
);

/**
 * @requires ./ctrl.js
 */
MPHBAdmin.MultipleCheckboxCtrl = MPHBAdmin.Ctrl.extend({
	renderValue: function (control) {
		var inputs = control.find('input[type="checkbox"]');
		var variantAll = inputs.filter('.mphb-checkbox-all:checked');
		var checked = inputs.filter(':checked');

		if (variantAll.length > 0 || checked.length == inputs.length) {
			return MPHBAdmin.Plugin.myThis.data.translations.all;
		} else {
			var labels = [];

			checked.each(function () {
				var label = $(this).parent().text();
				labels.push(label);
			});

			if (labels.length > 0) {
				return labels.join(', ');
			} else {
				return MPHBAdmin.Plugin.myThis.data.translations.none;
			}
		}
	}
}, {
	/**
	 * @type {Object[]} An array of checkbox input elements. "All" element is
	 * not included (see selectAllCheckbox).
	 */
	checkboxes: null,
	/**
	 * @type {Object} "All" checkbox element (if exists).
	 */
	selectAllCheckbox: null,
	init: function (element, args) {
		this._super(element, args);

		this.checkboxes = this.element.find('input[type="checkbox"]:not(.mphb-checkbox-all)');
		this.selectAllCheckbox = this.element.find('input[type="checkbox"].mphb-checkbox-all');

		// Fix for complex inputs that removes all 'disabled="disabled"'
		if (this.selectAllCheckbox.prop('checked')) {
			this.disableCheckboxes();
		}
	},
	".mphb-checkbox-all click": function () {
		if (this.selectAllCheckbox.prop('checked')) {
			this.disableCheckboxes();
			this.selectCheckboxes();
		} else {
			this.enableCheckboxes();
		}
	},
	/**
	 * @param {Object} element "Select all" button element.
	 * @param {Object} event
	 */
	".mphb-checkbox-select-all click": function (element, event) {
		event.preventDefault();

		this.selectCheckboxes();
	},
	/**
	 * @param {Object} element "Unselect all" button element.
	 * @param {Object} event
	 */
	".mphb-checkbox-unselect-all click": function (element, event) {
		event.preventDefault();

		this.unselectAll();
		this.enableCheckboxes();
	},
	disableCheckboxes: function () {
		this.checkboxes.prop('disabled', true);
	},
	enableCheckboxes: function () {
		this.checkboxes.prop('disabled', false);
	},
	selectCheckboxes: function () {
		this.checkboxes.prop('checked', true);
	},
	unselectCheckboxes: function () {
		this.checkboxes.prop('checked', false);
	},
	unselectAll: function () {
		this.selectAllCheckbox.prop('checked', false);
		this.unselectCheckboxes();
	}
});

/**
 * @requires ./ctrl.js
 */
MPHBAdmin.RulesListCtrl = MPHBAdmin.Ctrl.extend({}, {
	editClass: 'mphb-rules-list-editing',
	editText: '',
	doneText: '',
	lastIndex: -1,
	rulesCount: 0,
	table: null,
	tbody: null,
	rulePrototype: null,
	editingRule: null,
	noRulesMessage: null,
	prependNewItems: false, // ... instead of append
	init: function (element, args) {
		this._super(element, args);

		this.editText = MPHBAdmin.Plugin.myThis.data.translations.edit;
		this.doneText = MPHBAdmin.Plugin.myThis.data.translations.done;

		this.noRulesMessage = element.find('.mphb-rules-list-empty-message');

		this.table = element.children('table');
		this.tbody = this.table.children('tbody');

		if (this.tbody.hasClass('mphb-sortable')) {
			this.tbody.sortable();
			this.prependNewItems = true;
		}

		// Prepare rule prototype
		var prototypeElement = this.tbody.children('.mphb-rules-list-prototype');
		var rulePrototype = prototypeElement.clone();

		prototypeElement.remove();

		rulePrototype.removeClass('mphb-rules-list-prototype mphb-hide');
		rulePrototype.find('.mphb-ctrl:not(.mphb-keep-disabled) [name]:not(.mphb-keep-disabled)').each(function () {
			// Enable all controls
			$(this).prop('disabled', false);
		});

		this.rulePrototype = rulePrototype;

		// Find max (last) index
		var rules = this.tbody.children('tr');
		var maxIndex = this.lastIndex; // -1

		rules.each(function () {
			var ruleIndex = parseInt($(this).attr('data-id'));
			maxIndex = Math.max(maxIndex, ruleIndex);
		});

		this.lastIndex = maxIndex;
		this.rulesCount = rules.length;
	},
	".mphb-rules-list-add-button click": function () {
		this.addRule();
	},
	".mphb-rules-list-edit-button click": function (button, event) {
		var rule = this.getRuleByButton(button);
		this.toggleEdit(rule);
	},
	".mphb-rules-list-delete-button click": function (button, event) {
		var rule = this.getRuleByButton(button);
		this.deleteRule(rule);
	},
	addRule: function () {
		var rule = this.rulePrototype.clone();
		var nextIndex = this.nextIndex();

		// Set ID for new rule
		rule.attr('data-id', nextIndex);

		// Change ID in all names
		rule.find('[name*="[$index$]"]').each(function () {
			var element = $(this);

			var name = element.attr('name');
			name = name.replace('$index$', nextIndex);
			element.attr('name', name);

			var id = element.attr('id');
			if (id) {
				id = id.replace('$index$', nextIndex);
				element.attr('id', id);
			}
		});

		rule.find('[for*="[$index$]"]').each(function () {
			var element = $(this);

			var forProp = element.attr('for');
			forProp = forProp.replace('$index$', nextIndex);
			element.attr('for', forProp);
		});

		// Change ID in dependencies
		rule.find('[data-dependency*="[$index$]"]').each(function () {
			var element = $(this);

			var dependency = element.attr('data-dependency');
			dependency = dependency.replace('$index$', nextIndex);

			element.attr('data-dependency', dependency);
		});

		if (this.prependNewItems) {
			this.tbody.prepend(rule);
		} else {
			this.tbody.append(rule);
		}

		this.increaseRulesCount();

		// Init new controls
		var newControls = rule.find('.mphb-ctrl:not([data-inited])');
		MPHBAdmin.Plugin.myThis.setControls(newControls);

		this.toggleEdit(rule);
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	toggleEdit: function (rule) {
		if (this.editingRule != null) {
			// Toggle with active editing rule means maximize new rule or
			// minimize the current one. In both cases current rule will be
			// minimized
			this.renderValues(this.editingRule);
			this.editingRule.removeClass(this.editClass);

			// Change text from "Done" to "Edit"
			this.editingRule.find('.mphb-rules-list-edit-button').text(this.editText);
		}

		if (this.isEditingRule(rule)) {
			this.editingRule = null; // Already removed the class
		} else {
			this.editingRule = rule;
			rule.addClass(this.editClass);

			// Change text from "Edit" to "Done"
			rule.find('.mphb-rules-list-edit-button').text(this.doneText);
		}
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	deleteRule: function (rule) {
		if (this.isEditingRule(rule)) {
			this.editingRule = null;
		}

		rule.remove();

		this.decreaseRulesCount();
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	isEditingRule: function (rule) {
		// rule.hasClass( this.editClass ) - at this time, the class can be
		// removed, see toggleEdit()
		return (this.editingRule != null && rule[0] === this.editingRule[0]);;
	},
	/**
	 * @param {Object} button "Edit" or "Delete" button.
	 */
	getRuleByButton: function (button) {
		return button.closest('tr');
	},
	increaseRulesCount: function () {
		if (this.rulesCount == 0) {
			this.noRulesMessage.addClass('mphb-hide');
			this.table.removeClass('mphb-hide');
		}

		this.rulesCount++;
	},
	decreaseRulesCount: function () {
		this.rulesCount--;

		if (this.rulesCount == 0) {
			this.table.addClass('mphb-hide');
			this.noRulesMessage.removeClass('mphb-hide');
		}
	},
	nextIndex: function () {
		this.lastIndex++;
		return this.lastIndex;
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	renderValues: function (rule) {
		var self = this;

		rule.children('td').each(function () {
			var td = $(this);
			var control = td.children('.mphb-ctrl');

			if (control.length == 0) {
				return;
			}

			var result = self.renderValue(control);
			td.children('.mphb-rules-list-rendered-value').html(result);
		});
	},
	/**
	 * @param {Object} control .mphb-ctrl element.
	 *
	 * @returns {String}
	 */
	renderValue: function (control) {
		var type = control.attr('data-type');
		var result = '';

		switch (type) {
			case 'text':
				result = MPHBAdmin.Ctrl.renderValue(control);
				break;

			case 'datepicker':
				result = MPHBAdmin.DatePickerCtrl.renderValue(control);
				break;

			case 'textarea':
				result = control.find('textarea').val();
				break;

			case 'number':
				result = MPHBAdmin.NumberCtrl.renderValue(control);
				break;

			case 'select':
			case 'dynamic-select':
				var select = control.children('select');
				var value = select.val();

				if (value != undefined) {
					var option = select.children('option[value="' + value + '"]');
					result = option.text();
				} else {
					result = MPHBAdmin.Plugin.myThis.data.translations.none;
				}

				break;

			case 'single-checkbox':
				result = MPHBAdmin.SingleCheckboxCtrl.renderValue(control);
				break;

			case 'multiple-checkbox':
				result = MPHBAdmin.MultipleCheckboxCtrl.renderValue(control);
				break;

			case 'amount':
				result = MPHBAdmin.AmountCtrl.renderValue(control);
				break;

			case 'placeholder':
				result = '-';
				break;
		}

		return result;
	}
});

/**
 * @requires ./rules-list.js
 */
MPHBAdmin.NotesListCtrl = MPHBAdmin.RulesListCtrl.extend({}, {
	editClass: 'mphb-notes-list-editing',
	editText: '',
	doneText: '',
	rulesCount: 0,
	table: null,
	tbody: null,
	rulePrototype: null,
	editingRule: null,
	noRulesMessage: null,
	prependNewItems: false, // ... instead of append
	init: function (element, args) {
		this._super(element, args);
		this.lastIndex = -1;
		this.editText = MPHBAdmin.Plugin.myThis.data.translations.edit;
		this.doneText = MPHBAdmin.Plugin.myThis.data.translations.done;

		this.noRulesMessage = element.find('.mphb-notes-list-empty-message');

		this.table = element.children('table');
		this.tbody = this.table.children('tbody');

		if (this.tbody.hasClass('mphb-sortable')) {
			this.tbody.sortable();
			this.prependNewItems = true;
		}

		// Prepare rule prototype
		var prototypeElement = this.tbody.children('.mphb-notes-list-prototype');
		var rulePrototype = prototypeElement.clone();

		prototypeElement.remove();

		rulePrototype.removeClass('mphb-notes-list-prototype mphb-hide');
		rulePrototype.find('.mphb-ctrl:not(.mphb-keep-disabled) [name]:not(.mphb-keep-disabled)').each(function () {
			// Enable all controls
			$(this).prop('disabled', false);
		});

		this.rulePrototype = rulePrototype;

		// Find max (last) index
		var rules = this.tbody.children('tr');
		var maxIndex = this.lastIndex; // -1

		rules.each(function () {
			var ruleIndex = parseInt($(this).attr('data-id'));
			maxIndex = Math.max(maxIndex, ruleIndex);
		});

		this.lastIndex = maxIndex;
		this.rulesCount = rules.length;
	},
	".mphb-notes-list-add-button click": function () {
		this.addRule();
	},
	".mphb-notes-list-edit-button click": function (button, event) {
		var rule = this.getRuleByButton(button);
		this.toggleEdit(rule);
	},
	".mphb-notes-list-delete-button click": function (button, event) {
		var rule = this.getRuleByButton(button);
		this.deleteRule(rule);
	},
	addRule: function () {
		var rule = this.rulePrototype.clone();
		var nextIndex = this.nextIndex();

		// Set ID for new rule
		rule.attr('data-id', nextIndex);

		// Change ID in all names
		rule.find('[name*="[$index$]"]').each(function () {
			var element = $(this);

			var name = element.attr('name');
			name = name.replace('$index$', nextIndex);
			element.attr('name', name);

			var id = element.attr('id');
			if (id) {
				id = id.replace('$index$', nextIndex);
				element.attr('id', id);
			}
		});

		// Change ID in dependencies
		rule.find('[data-dependency*="[$index$]"]').each(function () {
			var element = $(this);

			var dependency = element.attr('data-dependency');
			dependency = dependency.replace('$index$', nextIndex);

			element.attr('data-dependency', dependency);
		});

		if (this.prependNewItems) {
			this.tbody.prepend(rule);
		} else {
			this.tbody.append(rule);
		}

		this.increaseRulesCount();

		// Init new controls
		var newControls = rule.find('.mphb-ctrl:not([data-inited])');
		MPHBAdmin.Plugin.myThis.setControls(newControls);

		this.toggleEdit(rule);
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	toggleEdit: function (rule) {
		if (this.editingRule != null) {
			// Toggle with active editing rule means maximize new rule or
			// minimize the current one. In both cases current rule will be
			// minimized
			this.renderValues(this.editingRule);
			this.editingRule.removeClass(this.editClass);

			// Change text from "Done" to "Edit"
			this.editingRule.find('.mphb-notes-list-edit-button').text(this.editText);
		}

		if (this.isEditingRule(rule)) {
			this.editingRule = null; // Already removed the class
		} else {
			this.editingRule = rule;
			rule.addClass(this.editClass);

			// Change text from "Edit" to "Done"
			rule.find('.mphb-notes-list-edit-button').text(this.doneText);
		}
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	deleteRule: function (rule) {
		if (this.isEditingRule(rule)) {
			this.editingRule = null;
		}

		rule.remove();

		this.decreaseRulesCount();
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	isEditingRule: function (rule) {
		// rule.hasClass( this.editClass ) - at this time, the class can be
		// removed, see toggleEdit()
		return (this.editingRule != null && rule[0] === this.editingRule[0]);;
	},
	/**
	 * @param {Object} button "Edit" or "Delete" button.
	 */
	getRuleByButton: function (button) {
		return button.closest('tr');
	},
	increaseRulesCount: function () {
		if (this.rulesCount == 0) {
			this.noRulesMessage.addClass('mphb-hide');
			this.table.removeClass('mphb-hide');
		}

		this.rulesCount++;
	},
	decreaseRulesCount: function () {
		this.rulesCount--;

		if (this.rulesCount == 0) {
			this.table.addClass('mphb-hide');
			this.noRulesMessage.removeClass('mphb-hide');
		}
	},
	nextIndex: function () {
		this.lastIndex++;

		return this.lastIndex;
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	renderValues: function (rule) {
		var self = this;

		rule.children('td').each(function () {
			var td = $(this);
			var control = td.children('.mphb-ctrl');

			if (control.length == 0) {
				return;
			}

			var result = self.renderValue(control);
			td.children('.mphb-notes-list-rendered-value').html(result);
		});
	},

	renderValue: function (control) {
		var type = control.attr('data-type');
		var result = '';

		switch (type) {
			case 'text':
				result = MPHBAdmin.Ctrl.renderValue(control);
				break;

			case 'username':
				result = control.find('.mphb-ctrl-user-name').text();
				break;

			case 'timestamp':
				result = control.find('.mphb-ctrl-date-val').text();
				break;

			case 'datepicker':
				result = MPHBAdmin.DatePickerCtrl.renderValue(control);
				break;

			case 'textarea':
				result = control.find('textarea').val();
				break;

			case 'number':
				result = MPHBAdmin.NumberCtrl.renderValue(control);
				break;

			case 'select':
			case 'dynamic-select':
				var select = control.children('select');
				var value = select.val();

				if (value != undefined) {
					var option = select.children('option[value="' + value + '"]');
					result = option.text();
				} else {
					result = MPHBAdmin.Plugin.myThis.data.translations.none;
				}

				break;

			case 'multiple-checkbox':
				result = MPHBAdmin.MultipleCheckboxCtrl.renderValue(control);
				break;

			case 'amount':
				result = MPHBAdmin.AmountCtrl.renderValue(control);
				break;

			case 'placeholder':
				result = '-';
				break;
		}

		return result;
	}
});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.NumberCtrl = MPHBAdmin.Ctrl.extend({
	renderValue: function (control) {
		var input = control.children('input[type="number"]');
		return input.val() + input.parent().text(); // value + "&nbsp;inner label"
	}
}, {
	input: null,
	disableOn: [],
	init: function (element, args) {
		this._super(element, args);

		this.input = this.element.children('[name]');

		// Init dependency control
		var dependencyName = this.input.attr('data-dependency');
		var disableOn = this.input.attr('data-disable-on');

		if (dependencyName && disableOn) {
			this.disableOn = disableOn.split(',');

			var self = this;
			var dependencyCtrl = this.element.closest('form').find('[name="' + dependencyName + '"]');
			dependencyCtrl.on('change', function (event) {
				var value = $(this).val();
				self.onDependencyChange(value);
			});
		}
	},
	onDependencyChange: function (dependencyValue) {
		if (this.disableOn.indexOf(dependencyValue) != -1) {
			this.input.prop('disabled', true);
		} else {
			this.input.prop('disabled', false);
		}
	}
});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.PriceBreakdownCtrl = MPHBAdmin.Ctrl.extend({}, {
	// See also assets/js/public/dev/checkout/checkout-form.js
	".mphb-price-breakdown-expand click": function (element, event) {
		event.preventDefault();

		$(element).blur(); // Don't save a:focus style on last clicked item

		var tr = $(element).parents('tr.mphb-price-breakdown-group');
		tr.find('.mphb-price-breakdown-rate').toggleClass('mphb-hide');
		tr.nextUntil('tr.mphb-price-breakdown-group').toggleClass('mphb-hide');

		$(element).children('.mphb-inner-icon').toggleClass('mphb-hide');
	}
});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.SingleCheckboxCtrl = MPHBAdmin.Ctrl.extend({
	renderValue: function (control) {
		var input = control.children('input[type="checkbox"]');
		return input.prop('checked') ? $.trim(input.parent().text()) : '';
	}
}, {
	input: null,
	disableOn: [],
	init: function (element, args) {
		this._super(element, args);

		this.input = this.element.children('[name]');
	}
});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.TotalPriceCtrl = MPHBAdmin.Ctrl.extend({}, {
	preloader: null,
	input: null,
	init: function (el, args) {
		this._super(el, args);
		this.input = this.element.find('input');
		this.recalculateBtn = this.element.find('#mphb-recalculate-total-price');
		this.errorsWrapper = this.element.find('.mphb-errors-wrapper');
		this.preloader = this.element.find('.mphb-preloader');
	},
	set: function (value) {
		this.input.val(value);
	},
	hideErrors: function () {
		this.errorsWrapper.empty().addClass('mphb-hide');
	},
	'input focus': function () {
		this.hideErrors();
	},
	showError: function (message) {
		this.errorsWrapper.html(message).removeClass('mphb-hide');
	},
	'#mphb-recalculate-total-price click': function (el, e) {
		var self = this;
		this.hideErrors();
		this.showPreloader();
		var data = this.parseFormToJSON();

		$.ajax({
			url: MPHBAdmin.Plugin.myThis.data.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'mphb_recalculate_total',
				mphb_nonce: MPHBAdmin.Plugin.myThis.data.nonces.mphb_recalculate_total,
				formValues: data
			},
			success: function (response) {
				if (response.hasOwnProperty('success')) {
					if (response.success) {
						self.set(response.data.total);
						// Also update price breakdown
						var breakdownInput = self.element.closest('form').find('[name="_mphb_booking_price_breakdown"]');
						var breakdownWrapper = breakdownInput.siblings('.mphb-price-breakdown-wrapper');
						breakdownInput.val(response.data.price_breakdown);
						breakdownInput.prop('disabled', false);
						breakdownWrapper.html(response.data.price_breakdown_html);
					} else {
						self.showError(response.data.message);
					}
				} else {
					self.showError(MPHBAdmin.Plugin.myThis.data.translations.errorHasOccured);
				}
			},
			error: function (jqXHR) {
				self.showError(MPHBAdmin.Plugin.myThis.data.translations.errorHasOccured);
			},
			complete: function (jqXHR) {
				self.hidePreloader();
			}
		});
	},
	showPreloader: function () {
		this.recalculateBtn.attr('disabled', 'disabled');
		this.preloader.removeClass('mphb-hide');
	},
	hidePreloader: function () {
		this.recalculateBtn.removeAttr('disabled');
		this.preloader.addClass('mphb-hide');
	},
	parseFormToJSON: function () {
		return this.parentForm.serializeJSON();
	}

});

/**
 *
 * @requires ./ctrl.js
 */
MPHBAdmin.VariablePricingCtrl = MPHBAdmin.Ctrl.extend({}, {
	MIN_PERIOD: 2, // See also \MPHB\Admin\Fields\VariablePricingField::MIN_PERIOD

	name: '',

	periodsTable: null, // Top table
	variationsTable: null, // Bottom table
	variationsTableBody: null,
	variationsTableFooter: null,

	afterPeriods: null, // Place for new period inputs
	afterPrices: null, // Place for new price inputs (in top table)

	pricesHeaders: null, // .mphb-pricing-price-per-night

	template: null, // Template/prototype of variation
	templateActions: null, // Place for new price inputs

	lastIndex: -1,
	lastPeriodIndex: -1,
	periodsCount: 0,

	removePeriodText: '',
	periodDescription: '',

	init: function (element, args) {
		this._super(element, args);

		this.name = element.children('.mphb-pricing-name-holder').attr('name');

		this.removePeriodText = MPHBAdmin.Plugin.myThis.data.translations.removePeriod;
		this.periodDescription = MPHBAdmin.Plugin.myThis.data.translations.periodDescription;

		this.periodsTable = element.children('.mphb-pricing-periods-table');
		this.variationsTable = element.children('.mphb-pricing-variations-table');
		this.variationsTableBody = this.variationsTable.children('tbody');
		this.variationsTableFooter = this.variationsTable.find('tfoot > tr > td');

		this.afterPeriods = this.periodsTable.find('> tbody > tr:first-child > td:last-child');
		this.afterPrices = this.periodsTable.find('> tbody > tr:last-child > td:last-child');

		this.pricesHeaders = element.find('.mphb-pricing-price-per-night');

		// Prepare template
		this.template = this.loadTemplate();
		this.templateActions = this.template.children('td:last-child');

		// Find last/max indexes
		this.lastIndex = this.findLastIndex();
		this.lastPeriodIndex = this.findLastPeriodIndex();
		this.periodsCount = this.findPeriodsCount();

		// Watch checkbox "Enable variable pricing" to show/hide variable prices table
		this.watchCheckbox();
	},
	loadTemplate: function () {
		var templateElement = this.variationsTable.find('.mphb-pricing-variation-template');
		var template = templateElement.clone();

		templateElement.remove();

		template.removeClass('mphb-pricing-variation-prototype mphb-hide');
		// Enable all controls
		template.find('[name]').each(function () {
			$(this).prop('disabled', false);
		});

		return template;
	},
	addVariation: function () {
		var variation = this.template.clone();
		var index = this.nextIndex();

		variation.attr('data-index', index);

		// Change indexes in all names
		variation.find('[name*="[$index$]"]').each(function () {
			var element = $(this);

			var name = element.attr('name');
			name = name.replace('$index$', index);
			element.attr('name', name);
		});

		this.variationsTableBody.append(variation);
	},
	removeVariation: function (element) {
		element.remove();
	},
	addPeriod: function () {
		var index = this.nextPeriodIndex();

		var periodInput = '<input type="number" name="' + this.name + '[periods][]" class="small-text" value="' + this.MIN_PERIOD + '" min="' + this.MIN_PERIOD + '" step="1" />';
		periodInput += '<span class="mphb-pricing-period-description">' + this.periodDescription + '</span><span class="dashicons dashicons-trash mphb-pricing-action mphb-pricing-remove-period" title="' + this.removePeriodText + '"></span>';
		periodInput = '<td data-period-index="' + index + '">' + periodInput + '</td>';

		var pricesAtts = '';
		var afterPrices = '';

		var priceInput = '<td data-period-index="' + index + '"><input type="text" name="' + this.name + '[prices][]" class="mphb-price-text" value=""' + pricesAtts + ' />' + afterPrices + '</td>';

		var templateInput = '<td data-period-index="' + index + '"><input type="text" name="' + this.name + '[variations][$index$][prices][]" class="mphb-price-text" value=""' + pricesAtts + ' />' + afterPrices + '</td>';

		this.afterPeriods.before(periodInput);
		this.afterPrices.before(priceInput);
		this.templateActions.before(templateInput);

		this.variationsTableBody.children('tr').each(function (index, element) {
			var tr = $(element);
			var index = parseInt(tr.attr('data-index'));
			var afterPrices = tr.children('td:last-child');
			afterPrices.before(templateInput.replace('$index$', index));
		});

		this.increasePeriodsCount();
	},
	removePeriod: function (index) {
		this.template.find('[data-period-index="' + index + '"]').remove();
		this.element.find('[data-period-index="' + index + '"]').remove();
		this.decreasePeriodsCount();
	},
	nextIndex: function () {
		this.lastIndex++;
		return this.lastIndex;
	},
	nextPeriodIndex: function () {
		this.lastPeriodIndex++;
		return this.lastPeriodIndex;
	},
	findLastIndex: function () {
		var variations = this.variationsTableBody.children('tr');
		var maxIndex = -1;

		variations.each(function () {
			var index = parseInt($(this).attr('data-index'));
			maxIndex = Math.max(maxIndex, index);
		});

		return maxIndex;
	},
	findLastPeriodIndex: function () {
		var periods = this.periodsTable.find('> tbody > tr:first-child > td[data-period-index]');
		var maxIndex = -1;

		periods.each(function () {
			var index = parseInt($(this).attr('data-period-index'));
			maxIndex = Math.max(maxIndex, index);
		});

		return maxIndex;
	},
	findPeriodsCount: function () {
		var periods = this.periodsTable.find('> tbody > tr:first-child > td[data-period-index]');
		return periods.length;
	},
	increasePeriodsCount: function () {
		this.periodsCount++;
		this.updateColspans();
	},
	decreasePeriodsCount: function () {
		this.periodsCount--;
		this.updateColspans();
	},
	updateColspans: function () {
		this.pricesHeaders.attr('colspan', this.periodsCount);
		this.variationsTableFooter.attr('colspan', this.periodsCount + 3); // 3 - adults, children and .mphb-pricing-remove-variation
	},
	watchCheckbox: function () {
		var self = this;
		this.element.find('.mphb-pricing-enable-variations').on('change', function (event) {
			self.variationsTable.toggleClass('mphb-hide');
		});
	},
	".mphb-pricing-add-variation click": function (target, event) {
		this.addVariation();
	},
	".mphb-pricing-remove-variation click": function (target, event) {
		var row = target.closest('tr');
		this.removeVariation(row);
	},
	".mphb-pricing-add-period click": function (target, event) {
		this.addPeriod();
	},
	".mphb-pricing-remove-period click": function (target, event) {
		var cell = target.closest('td');
		var periodIndex = cell.attr('data-period-index');
		this.removePeriod(periodIndex);
	}
});

/**
 * @since 3.8
 */
MPHBAdmin.AddRoomPopup = MPHBAdmin.PopupForm.extend(
	{}, // Static
	{
		$roomTypes: null,
		$rooms: null,

		availableRooms: {}, // {Room type ID: [Room IDs]}

		reservedRooms: [],

		selectedRoom: 0,
		selectedRoomType: 0,

		init: function ($element, args) {
			this._super($element, args);

			this.$roomTypes = $element.find('.mphb-room-type-select');
			this.$rooms = $element.find('.mphb-room-select');

			this.availableRooms = this.parseAvailableRooms(this.$rooms);
		},

		parseAvailableRooms: function ($select) {
			var availableRooms = {};

			$select.children().each(function (index, element) {
				var roomId = parseInt(element.value);

				if (!isNaN(roomId)) {
					var roomTypeId = parseInt(element.getAttribute('data-room-type-id'));

					if (!availableRooms[roomTypeId]) {
						availableRooms[roomTypeId] = [];
					}

					availableRooms[roomTypeId].push(roomId);
				}
			});

			return availableRooms;
		},

		reset: function (inputData) {
			this._super(inputData);

			// Disable submit button
			this.canSubmit(false);

			this.reservedRooms = inputData.reserved_rooms;

			// Get presets
			var selectRoom = inputData.room_id || '';
			var selectRoomType = inputData.room_type_id || '';

			if (this.availableRooms.hasOwnProperty(selectRoomType)) {
				var roomIndex = this.reservedRooms.indexOf(selectRoom);
				if (roomIndex >= 0) {
					// Remove this room from reservedRooms and allow to use it
					// again as a replacement
					this.reservedRooms.splice(roomIndex, 1);
				} else {
					selectRoom = '';
				}
			} else {
				selectRoom = selectRoomType = '';
			}

			// Reset selects
			this.$rooms.val(selectRoom);
			this.$roomTypes.val(selectRoomType);

			this.selectedRoom = selectRoom || 0;
			this.selectedRoomType = selectRoomType || 0;

			this.filterRoomTypes(); // Once on reset()
			this.filterRooms();
			this.checkIfCanSubmit();
		},

		filterRoomTypes: function () {
			var $options = this.$roomTypes.children('[value!=""]');

			var availableRooms = this.availableRooms;
			var reservedRooms = this.reservedRooms;

			$options.show();

			$options.each(function (index, option) {
				var roomTypeId = parseInt(option.value);
				var roomsList = availableRooms[roomTypeId];
				var freeCount = 0;

				for (var i = 0; i < roomsList.length; i++) {
					if (reservedRooms.indexOf(roomsList[i]) == -1) {
						freeCount++;
					}
				}

				// Hide room type if no rooms left to select
				if (freeCount == 0) {
					$(option).hide();

					// The room and the room type are not available for current dates
					if (roomTypeId == this.selectedRoomType) {
						this.selectedRoomType = this.selectedRoom = 0;
					}
				}
			});
		},

		filterRooms: function () {
			var roomTypeId = this.selectedRoomType;
			var $options = this.$rooms.children('[value!=""]');

			if (roomTypeId != 0) {
				$options.show();

				// Leave only options of the current room type
				var reservedRooms = this.reservedRooms;

				$options.each(function (index, option) {
					var optionRoom = parseInt(option.value);
					var optionRoomType = parseInt(option.getAttribute('data-room-type-id'));

					if (optionRoomType != roomTypeId || reservedRooms.indexOf(optionRoom) >= 0) {
						$(option).hide();
					}
				});

			} else {
				// Hide all room options (except for "- Select -")
				$options.hide();
			}
		},

		getData: function () {
			var roomTypeId = this.selectedRoomType;
			var roomTypeTitle = this.$roomTypes.children('option:selected').text().trim();

			var roomId = this.selectedRoom;
			var roomTitle = this.$rooms.children('option:selected').text().trim();

			return $.extend({}, this._super(), {
				room_type: { id: roomTypeId, title: roomTypeTitle },
				room: { id: roomId, title: roomTitle }
			});
		},

		checkIfCanSubmit: function () {
			this.canSubmit(this.selectedRoom != 0 && this.selectedRoomType != 0);
		},

		".mphb-room-type-select change": function (element, event) {
			var selectedRoomType = parseInt(this.$roomTypes.val()) || 0;
			var roomTypeChanged = (this.selectedRoomType != selectedRoomType);

			this.selectedRoomType = selectedRoomType;

			// Reset the selected room if we changed the room type
			if (roomTypeChanged) {
				this.$rooms.val('');
				this.selectedRoom = 0;

				this.filterRooms();
				this.checkIfCanSubmit();
			}
		},

		".mphb-room-select change": function (element, event) {
			this.selectedRoom = parseInt(this.$rooms.val()) || 0;

			this.checkIfCanSubmit();
		}
	}
);

/**
 * @since 3.8
 */
MPHBAdmin.BookingEditor = can.Control.extend(
	{}, // Static
	{
		$editor: null,
		$roomsTable: null, // .mphb-reserve-rooms-table > tbody (wrapper of the <tr>s)
		$submitButton: null, // .mphb-reserve-rooms .mphb-submit-button-wrapper > .button

		settings: {}, // MPHBAdmin.Plugin.myThis.data.settings
		i18n: {}, // MPHBAdmin.Plugin.myThis.data.translations

		rooms: {}, // {Room ID: {isAvailable, $element: Room jQuery element}}

		popup: null,

		init: function ($element, args) {
			this.$editor = $element;
			this.$roomsTable = $element.find('.mphb-reserve-rooms-table > tbody');
			this.$submitButton = $element.find('.mphb-reserve-rooms .mphb-submit-button-wrapper > .button');

			this.settings = MPHBAdmin.Plugin.myThis.data.settings;
			this.i18n = MPHBAdmin.Plugin.myThis.data.translations;

			this.popup = new MPHBAdmin.AddRoomPopup($element.find('.mphb-popup'));

			this.initDatepickers();
			this.initRooms();

			this.toggleSubmit();
		},

		initDatepickers: function () {
			this.$editor.find('.mphb-datepick').datepick({
				dateFormat: this.settings.dateFormat,
				firstDay: this.settings.firstDay,
				showSpeed: 0,
				showOtherMonths: true,
				monthsToShow: this.settings.numberOfMonthDatepicker,
				pickerClass: this.settings.datepickerClass + ' mphb-datepick-popup mphb-check-in-datepick',
				useMouseWheel: false
			});
		},

		initRooms: function () {
			var $rooms = this.$roomsTable.children();
			var self = this;

			$rooms.each(function (index, node) {
				var $room = $(node);
				var roomId = parseInt($room.data('room-id'));
				var roomTypeId = parseInt($room.data('room-type-id'));

				if (!isNaN(roomId) && !isNaN(roomTypeId)) {
					self.rooms[roomId] = {
						$element: $room,
						roomTypeId: roomTypeId,
						isAvailable: $room.hasClass('mphb-available-room')
					};
				}
			});
		},

		toggleSubmit: function () {
			if (!this.hasRooms() || this.hasUnavailableRooms()) {
				this.$submitButton.prop('disabled', true);
			} else {
				this.$submitButton.prop('disabled', false);
			}
		},

		hasRooms: function () {
			return !$.isEmptyObject(this.rooms);
		},

		hasUnavailableRooms: function () {
			var hasUnavailable = false;

			$.each(this.rooms, function (index, room) {
				if (!room.isAvailable) {
					hasUnavailable = true;
					return false;
				}
			});

			return hasUnavailable;
		},

		addRoom: function (room, roomType) {
			var row = '<tr class="mphb-reserve-room mphb-available-room" data-room-id="' + room.id + '">'
				+ '<td class="column-room-type">' + roomType.title + '</td>'
				+ '<td class="column-room">' + room.title + '</td>'
				+ '<td class="column-status">'
				+ '<input type="hidden" name="add_rooms[]" value="' + room.id + '">'
				+ '<span>' + this.i18n.available + '</span>'
				+ '</td>'
				+ '<td class="column-actions">'
				+ '<button class="button mphb-remove-room-button">' + this.i18n.remove + '</button>'
				+ ' <button class="button mphb-replace-room-button">' + this.i18n.replace + '</button>'
				+ '</td>'
				+ '</tr>';

			this.$roomsTable.append(row);

			this.rooms[room.id] = {
				$element: this.$roomsTable.children('[data-room-id="' + room.id + '"]').first(),
				roomTypeId: roomType.id,
				isAvailable: true
			};

			this.toggleSubmit();
		},

		replaceRoom: function (replaceId, room, roomType) {
			if (!this.rooms[replaceId]) {
				return;
			}

			var $roomElement = this.rooms[replaceId]['$element'];
			var $statusColumn = $roomElement.children('.column-status');
			var $statusInput = $statusColumn.children('input').first();

			// Update titles
			$roomElement.children('.column-room-type').text(roomType.title);
			$roomElement.children('.column-room').text(room.title);

			// Updat inputs
			$roomElement.data('room-id', room.id);

			if ($statusInput.length == 1) {
				$statusInput.val(room.id)
			} else {
				$roomElement.children('.column-status').prepend('<input type="hidden" name="replace_rooms[' + replaceId + ']" value="' + room.id + '">');
			}

			// Update status text
			$statusColumn.children('span').text(this.i18n.available);
			$roomElement.removeClass('mphb-unavailable-room').addClass('mphb-available-room');

			this.rooms[room.id] = this.rooms[replaceId];
			this.rooms[room.id]['roomTypeId'] = roomType.id;
			this.rooms[room.id]['isAvailable'] = true;

			delete (this.rooms[replaceId]);

			// Enable submit button if all rooms are available
			this.toggleSubmit();
		},

		getRoomIds: function () {
			return Object.keys(this.rooms).map(function (roomId) { return parseInt(roomId); });
		},

		"#mphb-add-room-button click": function (element, event) {
			event.preventDefault();

			var self = this;

			this.popup.show({ reserved_rooms: this.getRoomIds() }).then(
				function (data) {
					self.addRoom(data.room, data.room_type);
				},
				function (error) { } // Catch errors. Don't trigger "Uncaught error"
			);
		},

		".mphb-replace-room-button click": function (element, event) {
			event.preventDefault();

			var $room = element.parents('.mphb-reserve-room');
			var roomId = parseInt($room.data('room-id'));
			var self = this;

			if (!isNaN(roomId)) {
				var popupData = {
					reserved_rooms: this.getRoomIds(),
					room_id: roomId,
					room_type_id: this.rooms[roomId]['roomTypeId']
				};

				this.popup.show(popupData).then(
					function (data) {
						self.replaceRoom(data.room_id, data.room, data.room_type);
					},
					function (error) { } // Catch errors. Don't trigger "Uncaught error"
				);
			}
		},

		".mphb-remove-room-button click": function (element, event) {
			event.preventDefault();

			var $room = element.parents('.mphb-reserve-room');
			var roomId = parseInt($room.data('room-id'));

			if (!isNaN(roomId) && this.rooms[roomId] != undefined) {
				delete (this.rooms[roomId]);
			}

			$room.remove();

			this.toggleSubmit();
		},

		".mphb-reserve-rooms .mphb-submit-button-wrapper > .button click": function (element, event) {
			if (!this.hasRooms() || this.hasUnavailableRooms()) {
				// Don't go to the next step
				event.preventDefault();
			}
		}
	}
);

new MPHBAdmin.Plugin();

$(function () {
	if ($('.mphb-bookings-calendar-wrapper')) {
		new MPHBAdmin.BookingsCalendar($('.mphb-bookings-calendar-wrapper'));
	}

	if ($('.mphb-edit-booking.edit').length > 0) {
		new MPHBAdmin.BookingEditor($('.mphb-edit-booking'));
	}

	if (MPHBAdmin.Plugin.myThis.data.settings.isAttributesCustomOrder) {
		new MPHBAdmin.AttributesCustomOrder($('table.wp-list-table'));
	}



	new MPHBAdmin.ServiceQuantity('.post-type-mphb_room_service #mphb_price');

	new MPHBAdmin.ExportBookings('#mphb-export-bookings-form');

	// Add checkbox "Display imported bookings in this table" on bookings page
	var pluginData = MPHBAdmin.Plugin.myThis.data;

	if (pluginData.settings.displayImportCheckbox) {
		var listTable = $('#posts-filter .wp-list-table');

		if (listTable.length > 0) {
			var checkboxLabel = pluginData.translations.displayImport;
			var checkedAttr = pluginData.settings.displayImport ? 'checked="checked"' : '';

			var appendHtml = '<p id="mphb-display-import-control"><label>'
				+ '<input type="checkbox" id="mphb-display-imported-bookings" ' + checkedAttr + ' /> ' + checkboxLabel
				+ '<span class="mphb-preloader mphb-hide"></span>'
				+ '</label></p>';

			$(appendHtml).insertBefore(listTable);

			$('#mphb-display-imported-bookings').change(function () {
				var self = $(this);
				var value = self.prop('checked');

				self.siblings('.mphb-preloader').removeClass('mphb-hide');
				self.prop('disabled', true);

				$.ajax({
					url: pluginData.ajaxUrl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'mphb_display_imported_bookings',
						mphb_nonce: pluginData.nonces.mphb_display_imported_bookings,
						new_value: value,
						user_id: pluginData.settings.userId
					},
					complete: function () {
						location.reload(true);
					}
				});
			}); // On change
		} // if (listTable.length > 0)
	} // if (displayImportCheckbox)

});

	});
})(jQuery);