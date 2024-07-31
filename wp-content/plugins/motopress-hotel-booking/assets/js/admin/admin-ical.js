(function ($, iCal) {
	"use strict";
	$(function () {
		/**
 *
 * @param {string} action
 * @param {function} callback
 * @param {Object} data Optional
 * @param {Object} atts Optional. $.ajax atts
 * @returns {jqXHR}
 */
function ajax(action, callback, data, atts) {

	atts = (typeof atts !== 'undefined') ? atts : {};
	data = (typeof data !== 'undefined') ? data : {};

	data['action'] = action;
	data['mphb_nonce'] = iCal.nonces.hasOwnProperty(action) ? iCal.nonces[action] : '';

	atts = $.extend(atts, {
		url: iCal.ajaxUrl,
		dataType: 'json',
		data: data,
		success: function (response, textStatus, jqXHR) {
			var success = true === response.success;
			var responseData = response.data || {};

			callback(success, responseData);
		},
	});
	return $.ajax(atts);
}

iCal.ControlButton = can.Control.extend({}, {
	inSuspended: false, // Temporarily make unavailable, but later restore
	// previous state (disabled or not)
	wasDisabled: false, // Was it disabled before suspense
	defaultText: '',
	actionText: '',
	ajaxAction: '',
	importer: null,
	init: function (el, args) {
		this.defaultText = args.defaultText;
		this.actionText = args.actionText;
		this.ajaxAction = args.ajaxAction;
		this.importer = args.importer;
	},
	click: function (el, ev) {
		if (this.inSuspended) {
			return false;
		} else {
			this.doAction();
		}
	},
	doAction: function () { },
	/**
	 * Button clicked and waiting for response.
	 */
	activate: function () {
		this.inSuspended = true;
		this.element.prop('disabled', true);
		this.element.text(this.actionText);
	},
	/**
	 * Deactivate button and enable it.
	 */
	enable: function () {
		this.isSuspended = false;
		this.element.prop('disabled', false);
		this.element.text(this.defaultText);
	},
	/**
	 * Deactivate button and disable it.
	 */
	disable: function () {
		this.isSuspended = false;
		this.element.prop('disabled', true);
		this.element.text(this.defaultText);
	},
	/**
	 * Some other process need this button to be disabled for a moment
	 * ("Clear All" for example).
	 */
	suspend: function () {
		this.inSuspended = true;
		this.wasDisabled = !!this.element.prop('disabled');
		this.element.prop('disabled', true);
	},
	/**
	 * Some other process failed and we need to restor previos state of the
	 * button.
	 */
	restore: function () {
		this.isSuspended = false;
		this.element.prop('disabled', this.wasDisabled);
	}
});

/**
 * @requires ./control-button.js
 */
iCal.AbortButton = iCal.ControlButton.extend({}, {
	doAction: function () {
		this.activate();
		this.importer.stop();

		var self = this;

		ajax(self.ajaxAction, function (success, data) {
			self.importer.start();
			if (!success) {
				self.enable();
			}
		}, {}, { method: 'POST' });
	}
});

/**
 * @requires ./control-button.js
 */
iCal.ClearAllButton = iCal.ControlButton.extend({}, {
	doAction: function () {
		this.activate();
		this.importer.stop();
		this.importer.trigger('mphb:clear_all:before');

		var self = this;

		ajax(this.ajaxAction, function (success, data) {
			if (success) {
				self.importer.trigger('mphb:clear_all');
				self.disable();
			} else {
				self.importer.trigger('mphb:clear_all:failed');
				self.enable();
			}
			self.importer.start();
		}, {}, { method: 'POST' });
	}
});

iCal.DetailsTableRoom = can.Control.extend({}, {
	key: '', // "564218900_38"
	status: '', // "wait"|"in-progress"|"done"

	statusEl: null,

	totalEl: null,
	succeedEl: null,
	failedEl: null,
	skippedEl: null,
	removedEl: null,

	oldStatusClass: '',

	emptyValuePlaceholder: '&#8212;',

	init: function (el, args) {
		this.key = el.attr('data-item-key');
		this.status = el.attr('data-sync-status');

		this.statusEl = el.find('.column-status > span');

		this.totalEl = el.find('.column-total');
		this.succeedEl = el.find('.column-succeed');
		this.failedEl = el.find('.column-failed');
		this.skippedEl = el.find('.column-skipped');
		this.removedEl = el.find('.column-removed');

		this.oldStatusClass = this.statusEl.attr('class');
	},
	changeContent: function (data) {
		// Update status
		this.status = data.status.code;
		this.element.attr('data-sync-status', this.status);

		// Update status class
		this.statusEl.removeClass(this.oldStatusClass);
		this.statusEl.addClass(data.status.class);

		this.oldStatusClass = data.status.class;

		// Update status text
		this.statusEl.text(data.status.text);

		// Update numbers
		data.stats.total != 0 ? this.totalEl.text(data.stats.total) : this.totalEl.html(this.emptyValuePlaceholder);
		data.stats.succeed != 0 ? this.succeedEl.text(data.stats.succeed) : this.succeedEl.html(this.emptyValuePlaceholder);
		data.stats.failed != 0 ? this.failedEl.text(data.stats.failed) : this.failedEl.html(this.emptyValuePlaceholder);
		data.stats.skipped != 0 ? this.skippedEl.text(data.stats.skipped) : this.skippedEl.html(this.emptyValuePlaceholder);
		data.stats.removed != 0 ? this.removedEl.text(data.stats.removed) : this.removedEl.html(this.emptyValuePlaceholder);

		// Add "have errors" class
		if (data.stats.failed > 0) {
			this.element.addClass('mphb-have-errors');
		}
	},
	clear: function () {
		this.element.remove();
	},
	getKey: function () {
		return this.key;
	},
	getStatus: function () {
		return this.status;
	}
});

iCal.DetailsTable = can.Control.extend({}, {
	itemsSingularText: '%d item',
	itemsPluralText: '%d items',

	ajaxAction: '',

	importer: null, // iCal.SyncImporter in sync-importer.js

	rooms: {}, // Array of iCal.DetailsTableRoom (see details-table-room.js)
	roomsCount: 0, // Count only on current page
	roomsCountEl: null,

	init: function (el, args) {
		this.itemsSingularText = args.itemsSingularText;
		this.itemsPluralText = args.itemsPluralText;

		this.ajaxAction = args.ajaxAction;

		this.importer = args.importer;

		this.initRooms();
		this.initRemoveButtons();

		this.roomsCountEl = el.parent().find('.displaying-num');
	},
	initRooms: function () {
		var roomElements = this.element.find('tbody > tr:not(.mphb-logs-wrapper):not(.no-items)');
		var rooms = {};
		var roomsCount = 0;

		// Fetch rooms
		roomElements.each(function (index, el) {
			var room = new iCal.DetailsTableRoom($(el));

			var key = room.getKey();
			rooms[key] = room;

			roomsCount++;
		});

		this.rooms = rooms;
		this.roomsCount = roomsCount;
	},
	initRemoveButtons: function () {
		var self = this;

		this.element.find('.mphb-remove-item')
			.addClass('mphb-inited')
			.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();

				// Get room key
				var roomEl = $(this).parents('tr');
				var key = roomEl.attr('data-item-key');

				// Remove by key
				if (key && self.rooms[key]) {
					$(this).prop('disabled', true);
					self.removeRoomByKey(key);
				}
			});
	},
	removeRoomByKey: function (roomKey) {
		this.importer.stop();

		var self = this;

		ajax(this.ajaxAction, function (success) {
			// Remove the room or enable the button
			if (success) {
				self.removeRoom(self.rooms[roomKey]);
			} else {
				self.element.find('.mphb-remove-item:disabled').prop('disabled', false);
			}
			self.importer.start();
		}, {
			mphb_room_key: roomKey
		}, {
			method: 'POST'
		});

	},
	removeRoom: function (room) {
		room.clear();

		delete this.rooms[room.getKey()];
		this.roomsCount--;

		this.decreaseCountInView();

		if (this.isEmpty()) {
			// Reload the page (to show items from other pages)
			location.reload(true); // true - load from server, not from the cache
		}
	},
	clear: function () {
		var self = this;
		$.each(this.rooms, function (index, room) {
			self.removeRoom(room);
		});
		this.roomsCount = 0;
	},
	isEmpty: function () {
		return this.roomsCount == 0;
	},
	changeContent: function (rooms) {
		var self = this;

		$.each(rooms, function (key, roomData) {
			if (self.rooms[key] != undefined) {
				self.rooms[key].changeContent(roomData);
			}
		});
	},
	decreaseCountInView: function () {
		var totalText = this.roomsCountEl.text(); // "24 items"

		var match = totalText.match(/\d+/); // [0: "24", index: 0, input: "24 items"] or null (if failed)

		if (match != null) {
			var totalCount = parseInt(match[0]);
			totalCount--;

			var textTemplate = (totalCount == 1) ? this.itemsSingularText : this.itemsPluralText;
			var text = textTemplate.replace('%d', totalCount);

			this.roomsCountEl.text(text);
		}
	},
	getKeysOfUndone: function () {
		var keys = [];

		// TODO Don't search for undone rooms every time, make a list with such rooms
		$.each(this.rooms, function (key, room) {
			// TODO Use value "done" from constant Queue::STATUS_DONE
			if (room.getStatus() != 'done') {
				keys.push(key);
			}
		});

		return keys;
	}
});

iCal.ImportStats = can.Control.extend({}, {
	totalEl: null,
	succeedEl: null,
	skippedEl: null,
	failedEl: null,
	removedEl: null,
	init: function (el, args) {
		this.totalEl = this.element.find('.mphb-total');
		this.succeedEl = this.element.find('.mphb-succeed');
		this.skippedEl = this.element.find('.mphb-skipped');
		this.failedEl = this.element.find('.mphb-failed');
		this.removedEl = this.element.find('.mphb-removed');
	},
	updateStats: function (data) {
		// Update process info
		this.totalEl.text(data.total);
		this.succeedEl.text(data.succeed);
		this.skippedEl.text(data.skipped);
		this.failedEl.text(data.failed);
		this.removedEl.text(data.removed);
	}
});

iCal.Importer = can.Control.extend({}, {
	tickInterval: 2000,
	shortTickInterval: 500, // Show response faster for rooms with no sync URLs
	retriesCount: 1,
	retriesLeft: 0,
	inProgress: false,
	updateTimeout: null,
	preventUpdates: false,
	init: function (el, args) {
		this.inProgress = args.inProgress;
		this.resetRetries();
		if (this.inProgress) {
			this.start();
		}
	},
	start: function () {
		this.preventUpdates = false;
		this.updateTimeout = setTimeout(this.tick.bind(this), this.shortTickInterval);
	},
	requestUpdate: function () {
		this.preventUpdates = false;
		this.updateTimeout = setTimeout(this.tick.bind(this), this.tickInterval);
	},
	stop: function () {
		clearTimeout(this.updateTimeout);
		this.preventUpdates = true;
	},
	resetRetries: function () {
		this.retriesLeft = this.retriesCount;
	},
	markInProgress: function () {
		this.inProgress = true;
	},
	markStopped: function () {
		this.inProgress = false;
	},
	trigger: function (event) {
		this.element.trigger(event);
	}
});

iCal.LogsHandler = can.Control.extend({}, {
	shown: 0,
	insertLogs: function (logs) {
		this.element.append(logs);
	},
	/**
	 *
	 * @param {int} count
	 * @returns {undefined}
	 */
	setShown: function (count) {
		this.shown = count;
	}
});

iCal.ProgressBar = can.Control.extend({}, {
	barEl: null,
	textEl: null,
	init: function (el, args) {
		this.barEl = this.element.find('.mphb-progress__bar');
		this.textEl = this.element.find('.mphb-progress__text');
	},
	updateProgress: function (newProgress) {
		this.barEl.css('width', newProgress + '%');
		this.textEl.text(newProgress + '%');
	}
});

/**
 *
 * @requires ./importer.js
 */
iCal.SyncImporter = iCal.Importer.extend({}, {
	abortButton: null,
	clearAllButton: null,

	detailsTable: null,

	init: function (el, args) {
		this._super(el, args);

		this.detailsTable = new iCal.DetailsTable(this.element.find('.mphb-ical-sync-table'), {
			itemsSingularText: iCal.i18n.items_singular,
			itemsPluralText: iCal.i18n.items_plural,
			ajaxAction: iCal.actions.sync.remove_item,
			importer: this
		});

		this.abortButton = new iCal.AbortButton(this.element.find('.mphb-abort-process'), {
			defaultText: iCal.i18n.abort,
			actionText: iCal.i18n.aborting,
			ajaxAction: iCal.actions.sync.abort,
			importer: this
		});

		this.clearAllButton = new iCal.ClearAllButton(this.element.find('.mphb-clear-all'), {
			defaultText: iCal.i18n.clear,
			actionText: iCal.i18n.clearing,
			ajaxAction: iCal.actions.sync.clear_all,
			importer: this
		});
	},
	tick: function () {
		var self = this;

		var undoneRoomKeys = this.detailsTable.getKeysOfUndone();

		ajax(iCal.actions.sync.progress, function (success, data) {
			if (self.preventUpdates) {
				return;
			}

			// Request failed?
			if (!success) {
				if (self.retriesLeft > 0) {
					self.retriesLeft--;
					self.requestUpdate();
				} else {
					self.abortButton.disable();
				}
				return;
			} else {
				self.resetRetries();
			}

			data.inProgress ? self.markInProgress() : self.markStopped();

			self.detailsTable.changeContent(data.items);

			if (self.inProgress) {
				self.requestUpdate();
			} else {
				self.abortButton.disable();
			}

		}, {
			focus: undoneRoomKeys
		}, {
			method: 'POST'
		});
	},
	// "Clear All" clicked or removed last element in detailsTable (by button "Remove")
	"mphb:clear_all": function () {
		this.abortButton.disable();
		this.clearAllButton.disable();
		this.detailsTable.clear();
	},
	// "Clear All" button was clicked but there is no AJAX response yet
	"mphb:clear_all:before": function () {
		this.abortButton.suspend();
	},
	// "Clear All" button was clicked but AJAX failed
	"mphb:clear_all:failed": function () {
		this.abortButton.restore();
	}
});

/**
 *
 * @requires ./importer.js
 */
iCal.UploadImporter = iCal.Importer.extend({}, {
	progressBar: null,
	logsHandler: null,
	importStats: null,
	init: function (el, args) {
		this._super(el, args);
		this.progressBar = new iCal.ProgressBar(this.element.find('.mphb-progress'));
		this.logsHandler = new iCal.LogsHandler(this.element.find('.mphb-logs'));
		this.importStats = new iCal.ImportStats(this.element.find('.mphb-import-stats'));
		this.abortButton = new iCal.AbortButton(this.element.find('.mphb-abort-process'), {
			defaultText: iCal.i18n.abort,
			actionText: iCal.i18n.aborting,
			ajaxAction: iCal.actions.upload.abort,
			importer: this
		});
	},
	tick: function () {
		var self = this;
		ajax(iCal.actions.upload.progress, function (success, data) {

			// Request failed?
			if (!success) {
				if (self.retriesLeft > 0) {
					self.retriesLeft--;
					self.requestUpdate();
				} else {
					self.abortButton.disable();
				}
				return;
			} else {
				self.resetRetries();
			}

			data.isFinished ? self.markStopped() : self.markInProgress();

			self.importStats.updateStats(data);
			self.progressBar.updateProgress(data.progress);

			self.logsHandler.setShown(data.logsShown);
			self.logsHandler.insertLogs(data.logs);

			// Insert notice when finished
			$(data.notice).insertAfter('.wp-heading-inline');

			if (self.inProgress) {
				self.requestUpdate();
			} else {
				self.abortButton.disable();
			}

		}, { "logsShown": self.logsHandler.shown });
	}
});

var syncDetailsWrapper = $('.mphb-sync-details-wrapper');
if (syncDetailsWrapper.length) {
	new iCal.SyncImporter(syncDetailsWrapper, {
		inProgress: iCal.inProgress
	});
}

var uploadImportWrapper = $('.mphb-upload-import-details-wrapper');
if (uploadImportWrapper.length) {
	new iCal.UploadImporter(uploadImportWrapper, {
		inProgress: iCal.inProgress
	});
}

	});

})(jQuery, MPHB_iCal);