"use strict";

(function ($) {
  $(function () {
    MPHB.ajaxApiHelper = {
      _activeAjaxRequests: {},
      _roomTypeCalendarsData: {},
      getLoadedRoomTypeCalendarData: function getLoadedRoomTypeCalendarData(roomTypeId) {
        var isShowPrices = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
        var isTruncatePrices = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
        var isShowPricesCurrency = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
        var dataKey = JSON.stringify([roomTypeId, isShowPrices, isTruncatePrices, isShowPricesCurrency]);

        if (undefined === this._roomTypeCalendarsData[dataKey]) {
          this._roomTypeCalendarsData[dataKey] = {};
        }

        return this._roomTypeCalendarsData[dataKey];
      },

      /**
       * @param {Date} startDate 
       * @param {int} monthsCount how many months dates to load
       * @param {int} roomTypeId if 0 then search availability data for all room types
       */
      loadRoomTypeCalendarData: function loadRoomTypeCalendarData(startDate, monthsCount, roomTypeId, isShowPrices, isTruncatePrices, isShowPricesCurrency) {
        var _this = this;

        var runBeforeDataLoading = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : function () {};
        var runAfterDataLoaded = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : function () {};
        var minLoadingMonthsCount = arguments.length > 8 && arguments[8] !== undefined ? arguments[8] : 0;
        var startLoadingDate = new Date(startDate.getTime()); // we start loading a day before because we need it later
        // for calendar date availability calculations

        startLoadingDate.setDate(startLoadingDate.getDate() - 1);
        var formattedStartLoadingDate = $.datepick.formatDate('yyyy-mm-dd', startLoadingDate);
        var endLoadingDate = new Date(startDate.getFullYear(), startDate.getMonth() + monthsCount, 1 // we need first day of next month to calculate availability correctly
        );
        var formattedEndLoadingDate = $.datepick.formatDate('yyyy-mm-dd', endLoadingDate);
        var roomTypeCalendarData = this.getLoadedRoomTypeCalendarData(roomTypeId, isShowPrices, isTruncatePrices, isShowPricesCurrency);
        var startLoadingDateRoomTypeData = roomTypeCalendarData[formattedStartLoadingDate]; // data is already loaded

        if (undefined !== roomTypeCalendarData[formattedStartLoadingDate] && roomTypeCalendarData[formattedStartLoadingDate].hasOwnProperty('roomTypeStatus') && undefined !== roomTypeCalendarData[formattedEndLoadingDate] && roomTypeCalendarData[formattedEndLoadingDate].hasOwnProperty('roomTypeStatus')) {
          return roomTypeCalendarData;
        }

        while (startLoadingDate.getTime() < endLoadingDate.getTime() && undefined !== startLoadingDateRoomTypeData && startLoadingDateRoomTypeData.hasOwnProperty('roomTypeStatus')) {
          startLoadingDate = $.datepick.add(startLoadingDate, 1, 'd');
          formattedStartLoadingDate = $.datepick.formatDate('yyyy-mm-dd', startLoadingDate);
          startLoadingDateRoomTypeData = roomTypeCalendarData[formattedStartLoadingDate];
        }

        if (startLoadingDate.getTime() < endLoadingDate.getTime()) {
          if (0 < minLoadingMonthsCount) {
            var minEndLoadingDate = new Date(startLoadingDate.getFullYear(), startLoadingDate.getMonth() + minLoadingMonthsCount + 1, 1);

            if (endLoadingDate.getTime() < minEndLoadingDate.getTime()) {
              endLoadingDate = minEndLoadingDate;
              formattedEndLoadingDate = $.datepick.formatDate('yyyy-mm-dd', endLoadingDate);
            }
          }

          runBeforeDataLoading();
          var ajaxRequestKey = JSON.stringify([roomTypeId, isShowPrices, isTruncatePrices, isShowPricesCurrency, formattedStartLoadingDate, formattedEndLoadingDate]);

          if (!this._activeAjaxRequests[ajaxRequestKey]) {
            var start = performance.now();
            var requestData = {
              action: 'mphb_get_room_type_calendar_data',
              mphb_nonce: MPHB._data.nonces['mphb_get_room_type_calendar_data'],
              mphb_is_admin: MPHB._data.isAdmin,
              mphb_locale: MPHB._data.settings.currentLanguage,
              start_date: formattedStartLoadingDate,
              end_date: formattedEndLoadingDate,
              room_type_id: roomTypeId,
              is_show_prices: isShowPrices,
              is_truncate_prices: isTruncatePrices,
              is_show_prices_currency: isShowPricesCurrency
            };
            console.log('START LOADING: ' + JSON.stringify(requestData));
            var ajaxRequestPromise = $.ajax({
              url: MPHB._data.ajaxUrl,
              type: 'GET',
              dataType: 'json',
              data: requestData,
              success: function success(response) {
                var end = performance.now();
                var executionTime = end - start;
                console.log('DATA LOADED: ' + JSON.stringify(requestData) + ' TIME: ' + (executionTime / 1000).toFixed(2) + ' sec', response.data);
                Object.assign(roomTypeCalendarData, response.data);
              },
              error: function error(response) {
                if (undefined !== response.responseJSON.data.errorMessage) {
                  console.error(response.responseJSON.data.errorMessage);
                } else {
                  console.error(response);
                }
              },
              complete: function complete() {
                delete _this._activeAjaxRequests[ajaxRequestKey];
              }
            });
            this._activeAjaxRequests[ajaxRequestKey] = ajaxRequestPromise;
          }

          this._activeAjaxRequests[ajaxRequestKey].then(function () {
            runAfterDataLoaded();
          });
        }
      }
    };
    MPHB.calendarHelper = {
      ROOM_STATUS_AVAILABLE: 'available',
      ROOM_STATUS_NOT_AVAILABLE: 'not-available',
      ROOM_STATUS_BOOKED: 'booked',
      ROOM_STATUS_PAST: 'past',
      ROOM_STATUS_EARLIER_MIN_ADVANCE: 'earlier-min-advance',
      ROOM_STATUS_LATER_MAX_ADVANCE: 'later-max-advance',
      ROOM_STATUS_BOOKING_BUFFER: 'booking-buffer',

      /**
       * Return object with date attributes for jQuery Datepicker
       * http://keith-wood.name/datepick.html
       * based on room types availability date data
       * @param {int} calendarMode 1 - availability calendar, 2 - check-in calendar, 3 - check-out calendar
       * @param {Date} date processing date
       * @param {boolean} isCurrentMonth
       */
      getCalendarDateAttributesFromAvailability: function getCalendarDateAttributesFromAvailability(calendarMode, date, isCurrentMonth, roomTypeCalendarData) {
        var isShowPrices = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : false;
        var calendarDateAttributes = {
          selectable: false,
          dateClass: 'mphb-date-cell',
          title: ''
        };

        if (!isCurrentMonth) {
          calendarDateAttributes.dateClass += ' mphb-extra-date';
          return calendarDateAttributes;
        }

        var formattedDate = $.datepick.formatDate('yyyy-mm-dd', date);
        var roomTypeData = roomTypeCalendarData[formattedDate];

        if (undefined === roomTypeData || 0 === Object.keys(roomTypeData).length || !roomTypeData.hasOwnProperty('roomTypeStatus')) {
          return calendarDateAttributes;
        }

        var dateBefore = new Date(date.getTime());
        dateBefore.setDate(dateBefore.getDate() - 1);
        var formattedDateBefore = $.datepick.formatDate('yyyy-mm-dd', dateBefore),
            roomTypeDataBefore = roomTypeCalendarData[formattedDateBefore],
            isDateBeforeAvailable = undefined !== roomTypeDataBefore && roomTypeDataBefore.hasOwnProperty('roomTypeStatus') && MPHB.calendarHelper.ROOM_STATUS_AVAILABLE === roomTypeDataBefore.roomTypeStatus,
            isDateBeforePast = undefined !== roomTypeDataBefore && roomTypeDataBefore.hasOwnProperty('roomTypeStatus') && MPHB.calendarHelper.ROOM_STATUS_PAST === roomTypeDataBefore.roomTypeStatus,
            isDateBeforeNotAvailable = undefined !== roomTypeDataBefore && roomTypeDataBefore.hasOwnProperty('roomTypeStatus') && MPHB.calendarHelper.ROOM_STATUS_NOT_AVAILABLE === roomTypeDataBefore.roomTypeStatus,
            isDateBeforeCheckInNotAllowed = undefined !== roomTypeDataBefore && roomTypeDataBefore.hasOwnProperty('isCheckInNotAllowed') && roomTypeDataBefore.isCheckInNotAllowed,
            isDateBeforeStayInNotAllowed = undefined !== roomTypeDataBefore && roomTypeDataBefore.hasOwnProperty('isStayInNotAllowed') && roomTypeDataBefore.isStayInNotAllowed,
            isDateBeforeOutOfSeasons = isDateBeforeNotAvailable && !isDateBeforeStayInNotAllowed && undefined !== roomTypeDataBefore && roomTypeDataBefore.hasOwnProperty('availableRoomsCount') && 0 < roomTypeDataBefore.availableRoomsCount;
        var dateAfter = new Date(date.getTime());
        dateAfter.setDate(date.getDate() + 1);
        var formattedDateAfter = $.datepick.formatDate('yyyy-mm-dd', dateAfter),
            roomTypeDataAfter = roomTypeCalendarData[formattedDateAfter],
            isDateAfterStayInNotAllowed = undefined !== roomTypeDataAfter && roomTypeDataAfter.hasOwnProperty('isStayInNotAllowed') && roomTypeDataAfter.isStayInNotAllowed,
            isDateAfterCheckOutNotAllowed = undefined !== roomTypeDataAfter && roomTypeDataAfter.hasOwnProperty('isCheckOutNotAllowed') && roomTypeDataAfter.isCheckOutNotAllowed,
            isDateAfterNotAvailable = undefined !== roomTypeDataAfter && roomTypeDataAfter.hasOwnProperty('roomTypeStatus') && MPHB.calendarHelper.ROOM_STATUS_NOT_AVAILABLE === roomTypeDataAfter.roomTypeStatus,
            isDateAfterOutOfSeasons = isDateAfterNotAvailable && !isDateAfterStayInNotAllowed && undefined !== roomTypeDataAfter && roomTypeDataAfter.hasOwnProperty('availableRoomsCount') && 0 < roomTypeDataAfter.availableRoomsCount;
        var isDateNotAvailable = MPHB.calendarHelper.ROOM_STATUS_NOT_AVAILABLE === roomTypeData.roomTypeStatus,
            isDateFullyBooked = MPHB.calendarHelper.ROOM_STATUS_BOOKED === roomTypeData.roomTypeStatus && (!roomTypeData.hasOwnProperty('isCheckInDate') || !roomTypeData.isCheckInDate) && (!roomTypeData.hasOwnProperty('isCheckOutDate') || !roomTypeData.isCheckOutDate),
            isCheckInDate = roomTypeData.hasOwnProperty('isCheckInDate') && roomTypeData.isCheckInDate,
            isCheckOutDate = roomTypeData.hasOwnProperty('isCheckOutDate') && roomTypeData.isCheckOutDate,
            isStayInNotAllowed = roomTypeData.hasOwnProperty('isStayInNotAllowed') && roomTypeData.isStayInNotAllowed,
            isCheckInNotAllowed = roomTypeData.hasOwnProperty('isCheckInNotAllowed') && roomTypeData.isCheckInNotAllowed,
            isCheckOutNotAllowed = roomTypeData.hasOwnProperty('isCheckOutNotAllowed') && roomTypeData.isCheckOutNotAllowed,
            isEarlierThanMinAdvanceDate = roomTypeData.hasOwnProperty('isEarlierThanMinAdvanceDate') && roomTypeData.isEarlierThanMinAdvanceDate,
            isLaterThanMaxAdvanceDate = roomTypeData.hasOwnProperty('isLaterThanMaxAdvanceDate') && roomTypeData.isLaterThanMaxAdvanceDate,
            isDateOutOfSeasons = isDateNotAvailable && !isStayInNotAllowed && roomTypeData.hasOwnProperty('availableRoomsCount') && 0 < roomTypeData.availableRoomsCount; // fill calendar date data by booking status of the processing date

        switch (roomTypeData.roomTypeStatus) {
          case MPHB.calendarHelper.ROOM_STATUS_PAST:
            calendarDateAttributes.dateClass += ' mphb-past-date';
            calendarDateAttributes.title += MPHB._data.translations.past; // custom attribute for later processing

            calendarDateAttributes.isPastDate = true;
            break;

          case MPHB.calendarHelper.ROOM_STATUS_AVAILABLE:
            calendarDateAttributes.dateClass += ' mphb-available-date';

            if (isCheckOutDate) {
              calendarDateAttributes.dateClass += ' mphb-date-check-out';
            }

            var availableRoomsCount = 'undefined';

            if (roomTypeData.hasOwnProperty('availableRoomsCount')) {
              availableRoomsCount = roomTypeData.availableRoomsCount;
            }

            calendarDateAttributes.title += MPHB._data.translations.available + ' (' + availableRoomsCount + ')';
            break;

          case MPHB.calendarHelper.ROOM_STATUS_NOT_AVAILABLE:
            calendarDateAttributes.dateClass += ' mphb-not-available-date';

            if (isCheckOutDate) {
              calendarDateAttributes.dateClass += ' mphb-date-check-out';
            }

            calendarDateAttributes.title += MPHB._data.translations.notAvailable;
            break;

          case MPHB.calendarHelper.ROOM_STATUS_BOOKED:
            calendarDateAttributes.dateClass += ' mphb-booked-date';

            if (isCheckInDate) {
              calendarDateAttributes.dateClass += ' mphb-date-check-in';
            } else if (isCheckOutDate) {
              calendarDateAttributes.dateClass += ' mphb-date-check-out';
            }

            calendarDateAttributes.title += MPHB._data.translations.booked;
            break;

          case MPHB.calendarHelper.ROOM_STATUS_EARLIER_MIN_ADVANCE:
          case MPHB.calendarHelper.ROOM_STATUS_LATER_MAX_ADVANCE:
            if (isCheckOutDate) {
              calendarDateAttributes.dateClass += ' mphb-booked-date mphb-date-check-out mphb-available-date';
            }

            calendarDateAttributes.title += MPHB._data.translations.notAvailable;
            break;
        }

        var rulesTitles = []; // complete calendar date data by booking rules for the processing date

        if (isStayInNotAllowed) {
          rulesTitles.push(MPHB._data.translations.notStayIn);
          calendarDateAttributes.dateClass += ' mphb-not-stay-in-date';
        }

        if (isCheckInNotAllowed) {
          rulesTitles.push(MPHB._data.translations.notCheckIn);
        }

        if (isCheckOutNotAllowed) {
          rulesTitles.push(MPHB._data.translations.notCheckOut);
        }

        if (isEarlierThanMinAdvanceDate) {
          rulesTitles.push(MPHB._data.translations.earlierMinAdvance);
        }

        if (isLaterThanMaxAdvanceDate) {
          rulesTitles.push(MPHB._data.translations.laterMaxAdvance);
        }

        if (rulesTitles.length) {
          calendarDateAttributes.title += '\n' + MPHB._data.translations.rules + ' ' + rulesTitles.join(', ');
        }

        if (isShowPrices && roomTypeData.hasOwnProperty('price')) {
          calendarDateAttributes.content = date.getDate() + '<span class="mphb-date-cell__price">' + roomTypeData.price + '</span>';
        }

        if (isDateOutOfSeasons && isCheckOutNotAllowed) {
          if (1 === calendarMode) {
            calendarDateAttributes.dateClass += ' mphb-out-of-season-date';
          } else if (2 === calendarMode) {
            calendarDateAttributes.dateClass += ' mphb-not-check-in-date';
          } else if (3 === calendarMode) {
            calendarDateAttributes.dateClass += ' mphb-not-check-out-date';
          } // custom attribute for later processing


          calendarDateAttributes.isUnavailable = true;
        } else {
          if (isDateBeforeAvailable && isDateOutOfSeasons && !isCheckOutNotAllowed || isCheckOutDate && isDateOutOfSeasons || isDateAfterOutOfSeasons && isDateAfterCheckOutNotAllowed && isCheckInNotAllowed) {
            if (1 === calendarMode) {
              calendarDateAttributes.dateClass += ' mphb-out-of-season-date--check-in';
            } else if (2 === calendarMode) {
              calendarDateAttributes.dateClass += ' mphb-not-check-in-date';
            } // custom attribute for later processing


            calendarDateAttributes.isUnavailableCheckIn = true;
          }

          if (isDateBeforeOutOfSeasons && isCheckOutNotAllowed) {
            if (1 === calendarMode) {
              calendarDateAttributes.dateClass += ' mphb-out-of-season-date--check-out';
            } else if (3 === calendarMode) {
              calendarDateAttributes.dateClass += ' mphb-not-check-out-date';
            } // custom attribute for later processing


            calendarDateAttributes.isUnavailableCheckOut = true;
          }
        }

        if (isDateNotAvailable && !isStayInNotAllowed && !isDateOutOfSeasons && (!isDateBeforeAvailable || isDateBeforeCheckInNotAllowed) || isDateFullyBooked || isDateBeforeStayInNotAllowed && isCheckInDate || isCheckOutDate && isStayInNotAllowed || isStayInNotAllowed && isCheckOutNotAllowed) {
          if (1 === calendarMode) {
            calendarDateAttributes.dateClass += ' mphb-mark-as-unavailable';
          } else if (2 === calendarMode) {
            calendarDateAttributes.dateClass += ' mphb-not-check-in-date';
          } else if (3 === calendarMode) {
            calendarDateAttributes.dateClass += ' mphb-not-check-out-date';
          } // custom attribute for later processing


          calendarDateAttributes.isUnavailable = true;
        } else {
          if (isStayInNotAllowed || isCheckInDate || isEarlierThanMinAdvanceDate && 1 !== calendarMode || isLaterThanMaxAdvanceDate && 1 !== calendarMode || isCheckInNotAllowed && 2 === calendarMode || isCheckInNotAllowed && isDateAfterStayInNotAllowed && isDateAfterCheckOutNotAllowed || isCheckInNotAllowed && isDateAfterNotAvailable && !isDateOutOfSeasons && !isDateAfterOutOfSeasons && !isDateAfterStayInNotAllowed || isDateNotAvailable && isDateBeforeAvailable && !isDateOutOfSeasons && !isDateBeforeCheckInNotAllowed) {
            if (1 === calendarMode) {
              calendarDateAttributes.dateClass += ' mphb-mark-as-unavailable--check-in';
            } else if (2 === calendarMode) {
              calendarDateAttributes.dateClass += ' mphb-not-check-in-date';
            } // custom attribute for later processing


            calendarDateAttributes.isUnavailableCheckIn = true;
          }

          if (isCheckOutDate || isCheckOutNotAllowed && 3 === calendarMode || isDateBeforeStayInNotAllowed && isCheckOutNotAllowed || isDateBeforePast && isCheckOutNotAllowed || isDateBeforeNotAvailable && isCheckOutNotAllowed && !isDateBeforeOutOfSeasons) {
            if (1 === calendarMode) {
              calendarDateAttributes.dateClass += ' mphb-mark-as-unavailable--check-out';
            } else if (3 === calendarMode) {
              calendarDateAttributes.dateClass += ' mphb-not-check-out-date';
            } // custom attribute for later processing


            calendarDateAttributes.isUnavailableCheckOut = true;
          }
        }

        return calendarDateAttributes;
      },

      /**
       * @param {Date} checkInDate
       */
      calculateMinMaxCheckOutDateForSelection: function calculateMinMaxCheckOutDateForSelection(checkInDate, roomTypeCalendarData) {
        var processingDate = MPHB.Utils.cloneDate(checkInDate),
            formattedProcessingDate = $.datepick.formatDate('yyyy-mm-dd', processingDate),
            roomTypeData = null,
            isStayInAllowedInProcessingDate = false; // normalise date to avoide days border fluctuations

        processingDate.setHours(12, 0, 0, 0);
        var minStayDateAfterCheckIn = null;
        var maxStayDateAfterCheckIn = null;
        var minCheckOutDateForSelection = null;
        var maxCheckOutDateForSelection = null;
        roomTypeData = roomTypeCalendarData[formattedProcessingDate];

        if (undefined === roomTypeData || 0 === Object.keys(roomTypeData).length || !roomTypeData.hasOwnProperty('roomTypeStatus')) {
          return {
            minStayDateAfterCheckIn: minStayDateAfterCheckIn,
            maxStayDateAfterCheckIn: maxStayDateAfterCheckIn,
            minCheckOutDateForSelection: minCheckOutDateForSelection,
            maxCheckOutDateForSelection: maxCheckOutDateForSelection
          };
        }

        if (roomTypeData.hasOwnProperty('minStayNights')) {
          processingDate.setDate(processingDate.getDate() + roomTypeData.minStayNights);
          processingDate.setHours(23, 59, 59, 999);
          formattedProcessingDate = $.datepick.formatDate('yyyy-mm-dd', processingDate);
          minStayDateAfterCheckIn = MPHB.Utils.cloneDate(processingDate);
          minStayDateAfterCheckIn.setHours(0, 0, 0, 1);
        }

        if (roomTypeData.hasOwnProperty('maxStayNights')) {
          maxStayDateAfterCheckIn = MPHB.Utils.cloneDate(checkInDate);
          maxStayDateAfterCheckIn.setDate(maxStayDateAfterCheckIn.getDate() + roomTypeData.maxStayNights);
          maxStayDateAfterCheckIn.setHours(23, 59, 59, 999);
        }

        do {
          roomTypeData = roomTypeCalendarData[formattedProcessingDate];

          if (undefined === roomTypeData || 0 === Object.keys(roomTypeData).length || !roomTypeData.hasOwnProperty('roomTypeStatus')) {
            break;
          }

          if (MPHB.calendarHelper.ROOM_STATUS_PAST !== roomTypeData.roomTypeStatus && MPHB.calendarHelper.ROOM_STATUS_EARLIER_MIN_ADVANCE !== roomTypeData.roomTypeStatus && (!roomTypeData.hasOwnProperty('isCheckOutNotAllowed') || !roomTypeData.isCheckOutNotAllowed)) {
            if (null === minCheckOutDateForSelection) {
              minCheckOutDateForSelection = MPHB.Utils.cloneDate(processingDate);
            }

            maxCheckOutDateForSelection = MPHB.Utils.cloneDate(processingDate);
          }

          isStayInAllowedInProcessingDate = (!roomTypeData.hasOwnProperty('isStayInNotAllowed') || !roomTypeData.isStayInNotAllowed) && (null === maxStayDateAfterCheckIn || maxStayDateAfterCheckIn.getTime() > processingDate.getTime()) && MPHB.calendarHelper.ROOM_STATUS_BOOKED !== roomTypeData.roomTypeStatus && MPHB.calendarHelper.ROOM_STATUS_NOT_AVAILABLE !== roomTypeData.roomTypeStatus;
          processingDate.setDate(processingDate.getDate() + 1);
          formattedProcessingDate = $.datepick.formatDate('yyyy-mm-dd', processingDate);
        } while (isStayInAllowedInProcessingDate);

        if (null !== minCheckOutDateForSelection) {
          minCheckOutDateForSelection.setHours(0, 0, 0, 1);
        }

        if (null !== maxCheckOutDateForSelection) {
          maxCheckOutDateForSelection.setHours(23, 59, 59, 999);
        }

        return {
          minStayDateAfterCheckIn: minStayDateAfterCheckIn,
          maxStayDateAfterCheckIn: maxStayDateAfterCheckIn,
          minCheckOutDateForSelection: minCheckOutDateForSelection,
          maxCheckOutDateForSelection: maxCheckOutDateForSelection
        };
      }
    };
    /**
     * @class MPHB.Datepicker
     */

    can.Control('MPHB.Datepicker', {}, {
      $datepickerInputElement: null,
      form: null,
      hiddenElement: null,
      roomTypeId: null,
      firstAvailableCheckInDate: null,
      init: function init($datepickerInputElement, args) {
        this.$datepickerInputElement = $datepickerInputElement;
        this.form = args.form;
        this.roomTypeId = args.roomTypeId;
        this.firstAvailableCheckInDate = new Date(args.firstAvailableCheckInDateYmd); // setup Hidden Element

        var hiddenElementId = this.element.attr('id') + '-hidden';
        this.hiddenElement = $('#' + hiddenElementId); // fix date

        if (this.hiddenElement.val()) {
          var date = $.datepick.parseDate(MPHB._data.settings.dateTransferFormat, this.hiddenElement.val());
          var fixedValue = $.datepick.formatDate(MPHB._data.settings.dateFormat, date);
          this.element.val(fixedValue);
        }

        this.initDatepick();
      },
      initDatepick: function initDatepick() {
        var defaultSettings = {
          dateFormat: MPHB._data.settings.dateFormat,
          altFormat: MPHB._data.settings.dateTransferFormat,
          altField: this.hiddenElement,
          minDate: $.datepick.parseDate(MPHB._data.settings.dateTransferFormat, MPHB._data.today),
          monthsToShow: MPHB._data.settings.numberOfMonthDatepicker,
          firstDay: MPHB._data.settings.firstDay,
          pickerClass: MPHB._data.settings.datepickerClass,
          useMouseWheel: false,
          showSpeed: 0
        };
        var datepickSettings = $.extend(defaultSettings, this.getDatepickSettings());
        this.element.datepick(datepickSettings);
      },

      /**
       *
       * @returns {Object}
       */
      getDatepickSettings: function getDatepickSettings() {
        return {};
      },

      /**
       * @return {Date|null}
       */
      getDate: function getDate() {
        var dateStr = this.element.val();
        var date = null;

        try {
          date = $.datepick.parseDate(MPHB._data.settings.dateFormat, dateStr);
        } catch (e) {
          date = null;
        }

        return date;
      },

      /**
       *
       * @param {string} format Optional. Datepicker format by default.
       * @returns {String} Date string or empty string.
       */
      getFormattedDate: function getFormattedDate(format) {
        if (typeof format === 'undefined') {
          format = MPHB._data.settings.dateFormat;
        }

        var date = this.getDate();
        return date ? $.datepick.formatDate(format, date) : '';
      },

      /**
       * @param {Date} date
       */
      setDate: function setDate(date) {
        this.element.datepick('setDate', date);
      },

      /**
       * @param {string} option
       */
      getOption: function getOption(option) {
        return this.element.datepick('option', option);
      },

      /**
       * @param {string} option
       * @param {mixed} value
       */
      setOption: function setOption(option, value) {
        this.element.datepick('option', option, value);
      },

      /**
       *
       * @returns {Date|null}
       */
      getMinDate: function getMinDate() {
        var minDate = this.getOption('minDate');
        return minDate !== null && minDate !== '' ? MPHB.Utils.cloneDate(minDate) : null;
      },

      /**
       *
       * @returns {Date|null}
       */
      getMaxDate: function getMaxDate() {
        var maxDate = this.getOption('maxDate');
        return maxDate !== null && maxDate !== '' ? MPHB.Utils.cloneDate(maxDate) : null;
      },

      /**
       *
       * @returns {Date|null}
       */
      getMaxAdvanceDate: function getMaxAdvanceDate() {
        var maxAdvanceDate = this.getOption('maxAdvanceDate');
        return maxAdvanceDate ? MPHB.Utils.cloneDate(maxAdvanceDate) : null;
      },

      /**
       *
       * @returns {undefined}
       */
      clear: function clear() {
        this.element.datepick('clear');
      },

      /**
       * @param {Date} date
       * @param {string} format Optional. Default 'yyyy-mm-dd'.
       */
      formatDate: function formatDate(date, format) {
        format = typeof format !== 'undefined' ? format : 'yyyy-mm-dd';
        return $.datepick.formatDate(format, date);
      },
      lock: function lock() {
        $('.datepick-popup').addClass('mphb-loading');
      },
      unlock: function unlock() {
        $('.datepick-popup').removeClass('mphb-loading');
      },

      /**
       * 
       * @param {bool} fullRefresh if true then refresh calendar input as well
       */
      refresh: function refresh() {
        var fullRefresh = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

        $.datepick._update(this.element[0], true);

        if (fullRefresh) {
          $.datepick._updateInput(this.element[0], false);
        }
      }
    });
    MPHB.FlexsliderGallery = can.Control.extend({}, {
      sliderEl: null,
      navSliderEl: null,
      groupId: null,
      init: function init(sliderEl, args) {
        this.sliderEl = sliderEl;
        this.groupId = sliderEl.data('group');
        var navSliderEl = $('.mphb-gallery-thumbnail-slider[data-group="' + this.groupId + '"]');

        if (navSliderEl.length) {
          this.navSliderEl = navSliderEl;
        }

        var self = this;
        $(window).on('load', function () {
          self.initSliders();
        }); // Load immediately is the window already loaded

        if (document.readyState == 'complete') {
          this.initSliders();
        }
      },
      initSliders: function initSliders() {
        if (this.slidersLoaded) {
          return;
        }

        var sliderAtts = this.sliderEl.data('flexslider-atts');

        if (this.navSliderEl) {
          var navSliderAtts = this.navSliderEl.data('flexslider-atts');
          navSliderAtts['asNavFor'] = '.mphb-flexslider-gallery-wrapper[data-group="' + this.groupId + '"]';
          navSliderAtts['itemWidth'] = this.navSliderEl.find('ul > li img').width();
          sliderAtts['sync'] = '.mphb-gallery-thumbnail-slider[data-group="' + this.groupId + '"]'; // The slider being synced must be initialized first

          this.navSliderEl.addClass('flexslider mphb-flexslider mphb-gallery-thumbnails-slider').flexslider(navSliderAtts);
        }

        this.sliderEl.addClass('flexslider mphb-flexslider mphb-gallery-slider').flexslider(sliderAtts);
        this.slidersLoaded = true;
      }
    });
    /**
     * @see MPHB.format_price() in admin/admin.js
     */

    MPHB.format_price = function (price, atts) {
      atts = atts || {};
      var defaultAtts = MPHB._data.settings.currency;
      atts = $.extend({
        'trim_zeros': false
      }, defaultAtts, atts);
      price = MPHB.number_format(price, atts['decimals'], atts['decimal_separator'], atts['thousand_separator']);
      var formattedPrice = atts['price_format'].replace('%s', price);

      if (atts['trim_zeros']) {
        var regex = new RegExp('\\' + atts['decimal_separator'] + '0+$|(\\' + atts['decimal_separator'] + '\\d*[1-9])0+$');
        formattedPrice = formattedPrice.replace(regex, '$1');
      }

      var priceHtml = '<span class="mphb-price">' + formattedPrice + '</span>';
      return priceHtml;
    };
    /**
     * @see MPHB.number_format() in admin/admin.js
     */


    MPHB.number_format = function (number, decimals, dec_point, thousands_sep) {
      // + Original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
      // + Improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
      // +   Bugfix by: Michael White (http://crestidg.com)
      var sign = '',
          i,
          j,
          kw,
          kd,
          km; // Input sanitation & defaults

      decimals = decimals || 0;
      dec_point = dec_point || '.';
      thousands_sep = thousands_sep || ',';

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

      km = j ? i.substr(0, j) + thousands_sep : '';
      kw = i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + thousands_sep);
      kd = decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : '';
      return sign + km + kw + kd;
    };
    /**
     * @param {String} action Action name (without prefix "mphb_").
     * @param {Object} data
     * @param {Object} callbacks "success", "error", "complete".
     * @returns {Object} The jQuery XMLHttpRequest object.
     *
     * @since 3.6.0
     */


    MPHB.post = function (action, data, callbacks) {
      action = 'mphb_' + action;
      data = $.extend({
        action: action,
        mphb_nonce: MPHB._data.nonces[action],
        lang: MPHB._data.settings.currentLanguage
      }, data);
      var ajaxArgs = $.extend({
        url: MPHB._data.ajaxUrl,
        type: 'POST',
        dataType: 'json',
        data: data
      }, callbacks);
      return $.ajax(ajaxArgs);
    };

    MPHB.TermsSwitcher = can.Construct.extend({}, {
      /**
       * @param {Object} element .mphb-checkout-terms-wrapper
       */
      init: function init(element, args) {
        var terms = element.children('.mphb-terms-and-conditions');

        if (terms.length > 0) {
          element.find('.mphb-terms-and-conditions-link').on('click', function (event) {
            event.preventDefault();
            terms.toggleClass('mphb-active');
          });
        }
      }
    });
    MPHB.Utils = can.Construct.extend({
      /**
       *
       * @param {Date} date
       * @returns {String}
       */
      formatDateToCompare: function formatDateToCompare(date) {
        return $.datepick.formatDate('yyyymmdd', date);
      },

      /**
       * @param {Date} date1
       * @param {Date} date2
       * @param {String|Null} operator Optional.
       * @returns {Number|Boolean}
       *
       * @since 3.8.7
       */
      compareDates: function compareDates(date1, date2, operator) {
        var date1 = MPHB.Utils.formatDateToCompare(date1);
        var date2 = MPHB.Utils.formatDateToCompare(date2);

        if (operator != null) {
          switch (operator) {
            case '>':
              return date1 > date2;
              break;

            case '>=':
              return date1 >= date2;
              break;

            case '<':
              return date1 < date2;
              break;

            case '<=':
              return date1 <= date2;
              break;

            case '=':
            case '==':
              return date1 == date2;
              break;

            case '!=':
              return date1 != date2;
              break;

            default:
              return false;
              break;
          }
        } else {
          if (date1 > date2) {
            return 1;
          } else if (date1 < date2) {
            return -1;
          } else {
            return 0;
          }
        }
      },

      /**
       *
       * @param {Date} date
       * @returns {Date}
       */
      cloneDate: function cloneDate(date) {
        return new Date(date.getTime());
      },

      /**
       *
       * @param {Array} arr
       * @returns {Array}
       */
      arrayUnique: function arrayUnique(arr) {
        return arr.filter(function (value, index, self) {
          return self.indexOf(value) === index;
        });
      },

      /**
       *
       * @param {Array} arr
       * @return {number}
       */
      arrayMin: function arrayMin(arr) {
        return Math.min.apply(null, arr);
      },

      /**
       *
       * @param {Array} arr
       * @return {number}
       */
      arrayMax: function arrayMax(arr) {
        return Math.max.apply(null, arr);
      },

      /**
       *
       * @param {Array} a
       * @param {Array} b
       * @return {Array}
       */
      arrayDiff: function arrayDiff(a, b) {
        return a.filter(function (i) {
          return b.indexOf(i) < 0;
        });
      },

      /**
       *
       * @param {mixed} value
       * @param {Array} arr
       * @return {boolean}
       */
      inArray: function inArray(value, arr) {
        return arr.indexOf(value) !== -1;
      }
    }, {});
    MPHB.Gateway = can.Construct.extend({}, {
      amount: 0,
      paymentDescription: '',
      init: function init(args) {
        this.billingSection = args.billingSection;
        this.initSettings(args.settings);
      },
      initSettings: function initSettings(settings) {
        this.amount = settings.amount;
        this.paymentDescription = settings.paymentDescription;
      },

      /**
       * @param {Number} amount The price to pay.
       * @param {Object} customer Maximum information about the customer. See
       *     MPHB.CheckoutForm.getCustomerDetails() for more details.
       * @returns {Promise}
       *
       * @since 3.6.0 added new parameter - amount.
       * @since 3.6.0 added new parameter - customer.
       * @since 3.6.0 changed the return value from Boolean to Promise.
       */
      canSubmit: function canSubmit(amount, customer) {
        return Promise.resolve(true);
      },
      updateData: function updateData(data) {
        this.amount = data.amount;
        this.paymentDescription = data.paymentDescription;
      },
      afterSelection: function afterSelection(newFieldset) {},
      cancelSelection: function cancelSelection() {},

      /**
       * @param {String} name
       * @param {String} value
       *
       * @since 3.6.0
       */
      onInput: function onInput(name, value) {}
    });
    /**
     *
     * @requires ./gateway.js
     */

    MPHB.BeanstreamGateway = MPHB.Gateway.extend({}, {
      scriptUrl: '',
      isCanSubmit: false,
      loadHandler: null,
      validityHandler: null,
      tokenRequestHandler: null,
      tokenUpdatedHandler: null,
      initSettings: function initSettings(settings) {
        this._super(settings);

        this.scriptUrl = settings.scriptUrl || 'https://payform.beanstream.com/v1.1.0/payfields/beanstream_payfields.js';
        this.validityHandler = this.validityChanged.bind(this);
        this.tokenRequestHandler = this.tokenRequested.bind(this);
        this.tokenUpdatedHandler = this.tokenUpdated.bind(this);
      },
      canSubmit: function canSubmit(amount, customer) {
        return Promise.resolve(this.isCanSubmit);
      },
      afterSelection: function afterSelection(newFieldset) {
        this._super(newFieldset);

        if (newFieldset.length > 0) {
          var script = document.createElement('script'); // <script> must have id "fields-script" or it will fail to init

          script.id = 'payfields-script';
          script.src = this.scriptUrl;
          script.dataset.submitform = 'true'; // Use async load only. Otherwise the script will wait infinitely for window.load event

          script.dataset.async = 'true'; // Create new handler for Beanstream "loaded" (inited) event

          if (this.loadHandler != null) {
            $(document).off('beanstream_payfields_loaded', this.loadHandler);
          }

          this.loadHandler = function (data) {
            $('[data-beanstream-id]').appendTo(newFieldset);
          };

          $(document).on('beanstream_payfields_loaded', this.loadHandler);
          newFieldset.append(script);
          newFieldset.removeClass('mphb-billing-fields-hidden');
        } // See all available events: https://github.com/Beanstream/checkoutfields#payfields-


        $(document).on('beanstream_payfields_inputValidityChanged', this.validityHandler).on('beanstream_payfields_tokenRequested', this.tokenRequestHandler).on('beanstream_payfields_tokenUpdated', this.tokenUpdatedHandler);
      },
      cancelSelection: function cancelSelection() {
        $(document).off('beanstream_payfields_inputValidityChanged', this.validityHandler).off('beanstream_payfields_tokenRequested', this.tokenRequestHandler).off('beanstream_payfields_tokenUpdated', this.tokenUpdatedHandler);
      },
      validityChanged: function validityChanged(event) {
        var eventDetail = event.eventDetail || event.originalEvent.eventDetail;

        if (!eventDetail.isValid) {
          this.isCanSubmit = false;
        }
      },
      tokenRequested: function tokenRequested(event) {
        this.billingSection.showPreloader();
      },
      tokenUpdated: function tokenUpdated(event) {
        var eventDetail = event.eventDetail || event.originalEvent.eventDetail;

        if (eventDetail.success) {
          this.isCanSubmit = true;
        } else {
          this.isCanSubmit = false;
          this.billingSection.showError(MPHB._data.translations.tokenizationFailure.replace('(%s)', eventDetail.message));
        }

        this.billingSection.hidePreloader();
      }
    });
    /**
     * @requires ./gateway.js
     */

    MPHB.BillingSection = can.Control.extend({}, {
      updateBillingFieldsTimeout: null,
      parentForm: null,
      billingFieldsWrapperEl: null,
      gateways: {},

      /** @since 3.6.1 */
      amounts: {},
      lastGatewayId: null,
      init: function init(el, args) {
        this.parentForm = args.form;
        this.billingFieldsWrapperEl = this.element.find('.mphb-billing-fields');
        this.initGateways(args.gateways);
      },
      initGateways: function initGateways(gateways) {
        var self = this;
        $.each(gateways, function (gatewayId, settings) {
          var gatewaySettings = {
            billingSection: self,
            settings: settings
          };
          var gateway = null;

          try {
            switch (gatewayId) {
              case 'braintree':
                gateway = new MPHB.BraintreeGateway(gatewaySettings);
                break;

              case 'beanstream':
                gateway = new MPHB.BeanstreamGateway(gatewaySettings);
                break;

              case 'stripe':
                gateway = new MPHB.StripeGateway(gatewaySettings);
                break;

              default:
                gateway = new MPHB.Gateway(gatewaySettings);
                break;
            }
          } catch (error) {
            console.error(error);
          }

          if (gateway != null) {
            self.gateways[gatewayId] = gateway;
            self.amounts[gatewayId] = settings.amount;
          }
        }); // For each gateway

        this.notifySelectedGateway();
      },
      updateBillingInfo: function updateBillingInfo(el, e) {
        var self = this;
        var gatewayId = el.val();
        this.showPreloader();
        this.billingFieldsWrapperEl.empty().addClass('mphb-billing-fields-hidden');
        clearTimeout(this.updateBillingFieldsTimeout);
        this.updateBillingFieldsTimeout = setTimeout(function () {
          var formData = self.parentForm.parseFormToJSON();
          $.ajax({
            url: MPHB._data.ajaxUrl,
            type: 'GET',
            dataType: 'json',
            data: {
              action: 'mphb_get_billing_fields',
              mphb_nonce: MPHB._data.nonces.mphb_get_billing_fields,
              mphb_gateway_id: gatewayId,
              formValues: formData,
              lang: MPHB._data.settings.currentLanguage
            },
            success: function success(response) {
              if (response.hasOwnProperty('success')) {
                if (response.success) {
                  // Disable previous selected gateway
                  if (self.lastGatewayId) {
                    self.gateways[self.lastGatewayId].cancelSelection();
                  }

                  self.billingFieldsWrapperEl.html(response.data.fields);

                  if (response.data.hasVisibleFields) {
                    self.billingFieldsWrapperEl.removeClass('mphb-billing-fields-hidden');
                  } else {
                    self.billingFieldsWrapperEl.addClass('mphb-billing-fields-hidden');
                  }

                  self.notifySelectedGateway(gatewayId);
                } else {
                  self.showError(response.data.message);
                }
              } else {
                self.showError(MPHB._data.translations.errorHasOccured);
              }
            },
            error: function error(jqXHR) {
              self.showError(MPHB._data.translations.errorHasOccured);
            },
            complete: function complete(jqXHR) {
              self.hidePreloader();
            }
          });
        }, 500);
      },
      '[name="mphb_gateway_id"] change': function nameMphb_gateway_idChange(el, e) {
        this.updateBillingInfo(el, e);
      },
      hideErrors: function hideErrors() {
        this.parentForm.hideErrors();
      },
      showError: function showError(message) {
        this.parentForm.showError(message);
      },
      showPreloader: function showPreloader() {
        this.parentForm.showPreloader();
      },
      hidePreloader: function hidePreloader() {
        this.parentForm.hidePreloader();
      },

      /**
       * @param {String} name
       * @param {String} value
       *
       * @since 3.6.0
       */
      onInput: function onInput(name, value) {
        var gateway = this.gateways[this.getSelectedGateway()];

        if (gateway) {
          gateway.onInput(name, value);
        }
      },

      /**
       * @param {Number} amount The price to pay.
       * @param {Object} customer Maximum information about the customer. See
       *     MPHB.CheckoutForm.getCustomerDetails() for more details.
       * @returns {Promise}
       *
       * @since 3.6.0 added new parameter - amount.
       * @since 3.6.0 added new parameter - customer.
       * @since 3.6.0 changed the return value from Boolean to Promise.
       */
      canSubmit: function canSubmit(amount, customer) {
        var gateway = this.gateways[this.getSelectedGateway()];

        if (gateway) {
          return gateway.canSubmit(amount, customer);
        } else {
          return Promise.resolve(true);
        }
      },
      getSelectedGateway: function getSelectedGateway() {
        var gatewayEl = this.getSelectedGatewayEl();

        if (gatewayEl && gatewayEl.length > 0) {
          return gatewayEl.val();
        }

        return '';
      },

      /**
       * @since 3.9.9
       */
      getSelectedGatewayEl: function getSelectedGatewayEl() {
        var gateways = this.element.find('[name="mphb_gateway_id"]');

        if (gateways.length == 1) {
          return gateways;
        } else {
          return gateways.filter(':checked');
        }
      },

      /** @since 3.6.1 */
      getSelectedGatewayAmount: function getSelectedGatewayAmount() {
        var gatewayId = this.getSelectedGateway();

        if (this.amounts.hasOwnProperty(gatewayId)) {
          return this.amounts[gatewayId];
        } else {
          return 0;
        }
      },
      notifySelectedGateway: function notifySelectedGateway(gatewayId) {
        gatewayId = gatewayId || this.getSelectedGateway();

        if (gatewayId && this.gateways.hasOwnProperty(gatewayId)) {
          this.gateways[gatewayId].afterSelection(this.billingFieldsWrapperEl); // Set up updated value of the country

          var selectedCountry = this.parentForm.getCountry();

          if (selectedCountry !== false) {
            this.gateways[gatewayId].onInput('country', selectedCountry);
          }
        }

        this.lastGatewayId = gatewayId;
      },
      updateGatewaysData: function updateGatewaysData(gatewaysData) {
        var self = this;
        $.each(gatewaysData, function (gatewayId, gatewayData) {
          if (self.gateways.hasOwnProperty(gatewayId)) {
            self.gateways[gatewayId].updateData(gatewayData);
          }
        });
      }
    });
    /**
     *
     * @requires ./gateway.js
     */

    MPHB.BraintreeGateway = MPHB.Gateway.extend({}, {
      clientToken: '',
      checkout: null,
      // Used to remove all fields and events of the Braintree SDK
      initSettings: function initSettings(settings) {
        this._super(settings);

        this.clientToken = settings.clientToken;
      },
      canSubmit: function canSubmit(amount, customer) {
        return Promise.resolve(this.isNonceStored());
      },

      /**
       *
       * @param {String} nonce
       * @returns {undefined}
       */
      storeNonce: function storeNonce(nonce) {
        var $nonceEl = this.billingSection.billingFieldsWrapperEl.find('[name="mphb_braintree_payment_nonce"]');
        $nonceEl.val(nonce);
      },

      /**
       *
       * @returns {Boolean}
       */
      isNonceStored: function isNonceStored() {
        var $nonceEl = this.billingSection.billingFieldsWrapperEl.find('[name="mphb_braintree_payment_nonce"]');
        return $nonceEl.length && $nonceEl.val() != '';
      },
      afterSelection: function afterSelection(newFieldset) {
        this._super(newFieldset);

        if (braintree != undefined) {
          var containerId = 'mphb-braintree-container-' + this.clientToken.substr(0, 8);
          newFieldset.append('<div id="' + containerId + '"></div>');
          var self = this;
          braintree.setup(this.clientToken, 'dropin', {
            container: containerId,
            onReady: function onReady(integration) {
              // We can use integration's teardown() method to remove all DOM elements and attached events
              self.checkout = integration;
            },
            onPaymentMethodReceived: function onPaymentMethodReceived(response) {
              self.storeNonce(response.nonce);
              self.billingSection.parentForm.element.submit();
              self.billingSection.showPreloader();
            }
          });
          newFieldset.removeClass('mphb-billing-fields-hidden');
        }
      },
      cancelSelection: function cancelSelection() {
        this._super();

        if (this.checkout != null) {
          var self = this;
          this.checkout.teardown(function () {
            self.checkout = null; // braintree.setup() can safely be run again
          });
        }
      }
    });
    MPHB.CouponSection = can.Control.extend({}, {
      applyCouponTimeout: null,
      parentForm: null,
      appliedCouponEl: null,
      couponEl: null,
      messageHolderEl: null,
      init: function init(el, args) {
        this.parentForm = args.form;
        this.couponEl = el.find('[name="mphb_coupon_code"]');
        this.appliedCouponEl = el.find('[name="mphb_applied_coupon_code"]');
        this.messageHolderEl = el.find('.mphb-coupon-message');
      },
      '.mphb-apply-coupon-code-button click': function mphbApplyCouponCodeButtonClick(el, e) {
        e.preventDefault();
        e.stopPropagation();
        this.clearMessage();
        var couponCode = this.couponEl.val();

        if (!couponCode.length) {
          this.showMessage(MPHB._data.translations.emptyCouponCode);
          return;
        }

        this.appliedCouponEl.val('');
        var self = this;
        this.showPreloader();
        clearTimeout(this.applyCouponTimeout);
        this.applyCouponTimeout = setTimeout(function () {
          var formData = self.parentForm.parseFormToJSON();
          $.ajax({
            url: MPHB._data.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
              action: 'mphb_apply_coupon',
              mphb_nonce: MPHB._data.nonces.mphb_apply_coupon,
              mphb_coupon_code: couponCode,
              formValues: formData,
              lang: MPHB._data.settings.currentLanguage
            },
            success: function success(response) {
              if (response.hasOwnProperty('success')) {
                if (response.success) {
                  self.parentForm.setCheckoutData(response.data);
                  self.couponEl.val('');
                  self.appliedCouponEl.val(response.data.coupon.applied_code);
                  self.showMessage(response.data.coupon.message);
                } else {
                  self.showMessage(response.data.message);
                }
              } else {
                self.showMessage(MPHB._data.translations.errorHasOccured);
              }
            },
            error: function error(jqXHR) {
              self.showMessage(MPHB._data.translations.errorHasOccured);
            },
            complete: function complete(jqXHR) {
              self.hidePreloader();
            }
          });
        }, 500);
      },
      removeCoupon: function removeCoupon() {
        this.appliedCouponEl.val('');
        this.clearMessage();
      },
      showPreloader: function showPreloader() {
        this.parentForm.showPreloader();
      },
      hidePreloader: function hidePreloader() {
        this.parentForm.hidePreloader();
      },
      clearMessage: function clearMessage() {
        this.messageHolderEl.html('').addClass('mphb-hide');
      },
      showMessage: function showMessage(message) {
        this.messageHolderEl.html(message).removeClass('mphb-hide');
      }
    });
    /**
     * @requires ./billing-section.js
     * @requires ./coupon-section.js
     * @required ./guests-chooser.js
     */

    MPHB.CheckoutForm = can.Control.extend({
      myThis: null
    }, {
      priceBreakdownTableEl: null,
      bookBtnEl: null,
      errorsWrapperEl: null,
      preloaderEl: null,
      billingSection: null,
      couponSection: null,
      waitResponse: false,
      updateInfoTimeout: null,
      updateRatesTimeout: null,
      freeBooking: false,
      currentInfoAjax: null,

      /** @since 3.6.0 */
      toPay: 0,
      init: function init(el, args) {
        MPHB.CheckoutForm.myThis = this;
        this.bookBtnEl = this.element.find('input[type=submit]');
        this.errorsWrapperEl = this.element.find('.mphb-errors-wrapper');
        this.preloaderEl = this.element.find('.mphb-preloader');
        this.priceBreakdownTableEl = this.element.find('table.mphb-price-breakdown');

        if (MPHB._data.settings.useBilling) {
          this.billingSection = new MPHB.BillingSection(this.element.find('#mphb-billing-details'), {
            'form': this,
            'gateways': MPHB._data.gateways
          });
        }

        if (MPHB._data.settings.useCoupons) {
          this.couponSection = new MPHB.CouponSection(this.element.find('#mphb-coupon-details'), {
            'form': this
          });
        }

        this.element.find('.mphb-room-details').each(function (i, element) {
          new MPHB.GuestsChooser($(element), {
            minAdults: MPHB._data.checkout.min_adults,
            minChildren: MPHB._data.checkout.min_children
          });
        });
        var self = this;
        $('.mphb-room-details').each(function () {
          self.updateRatePrices($(this));
        });
        this.updateCheckoutInfo();
      },

      /**
       * @param {Number} amount
       * @param {String} priceHtml
       *
       * @since 3.6.0 removed the "value" parameter.
       * @since 3.6.0 added new parameter - amount.
       * @since 3.6.0 added new parameter - priceHtml.
       */
      setTotal: function setTotal(amount, priceHtml) {
        this.toPay = amount;
        this.element.find('.mphb-total-price-field').html(priceHtml);
      },

      /**
       * @param {Number} amount
       * @param {String} priceHtml
       *
       * @since 3.6.0 removed the "value" parameter.
       * @since 3.6.0 added new parameter - amount.
       * @since 3.6.0 added new parameter - priceHtml.
       */
      setDeposit: function setDeposit(amount, priceHtml) {
        this.toPay = amount;
        this.element.find('.mphb-deposit-amount-field').html(priceHtml);
      },
      setupPriceBreakdown: function setupPriceBreakdown(priceBreakdown) {
        this.priceBreakdownTableEl.replaceWith(priceBreakdown);
        this.priceBreakdownTableEl = this.element.find('table.mphb-price-breakdown');
      },
      updateCheckoutInfo: function updateCheckoutInfo() {
        var self = this;
        self.hideErrors();
        self.showPreloader();
        clearTimeout(this.updateInfoTimeout);
        this.updateInfoTimeout = setTimeout(function () {
          var data = self.parseFormToJSON();
          self.currentInfoAjax = $.ajax({
            url: MPHB._data.ajaxUrl,
            type: 'GET',
            dataType: 'json',
            data: {
              action: 'mphb_update_checkout_info',
              mphb_nonce: MPHB._data.nonces.mphb_update_checkout_info,
              formValues: data,
              lang: MPHB._data.settings.currentLanguage
            },
            beforeSend: function beforeSend() {
              if (self.currentInfoAjax != null) {
                self.currentInfoAjax.abort();
                self.hideErrors();
              }
            },
            success: function success(response) {
              if (response.hasOwnProperty('success')) {
                if (response.success) {
                  if (response.data) {
                    self.setCheckoutData(response.data);
                  }
                } else {
                  self.showError(response.data.message);
                }
              } else {
                self.showError(MPHB._data.translations.errorHasOccured);
              }
            },
            error: function error(jqXHR) {
              self.showError(MPHB._data.translations.errorHasOccured);
            },
            complete: function complete(jqXHR) {
              self.hidePreloader();
              self.currentInfoAjax = null;
            }
          });
        }, 500);
      },
      setCheckoutData: function setCheckoutData(data) {
        this.setTotal(data.newAmount, data.priceHtml);
        this.setupPriceBreakdown(data.priceBreakdown);

        if (MPHB._data.settings.useBilling) {
          this.setDeposit(data.depositAmount, data.depositPrice);
          this.billingSection.updateGatewaysData(data.gateways);

          if (data.isFree) {
            this.setFreeMode();
          } else {
            this.unsetFreeMode();
          }
        }

        this.element[0].dispatchEvent(new Event('CheckoutDataChanged'));
      },
      setFreeMode: function setFreeMode() {
        this.freeBooking = true;
        this.billingSection.element.addClass('mphb-hide');
        this.element.append($('<input />', {
          'type': 'hidden',
          'name': 'mphb_gateway_id',
          'value': 'manual',
          'id': 'mphb-manual-payment-input'
        }));
      },
      unsetFreeMode: function unsetFreeMode() {
        this.freeBooking = false;
        this.billingSection.element.removeClass('mphb-hide');
        this.element.find('#mphb-manual-payment-input').remove();
      },
      updateRatePrices: function updateRatePrices(room) {
        if (!room || !room.length) {
          return;
        }

        var index = parseInt(room.attr('data-index')); // Get IDs of all rates for this room

        var rates = room.find('.mphb_sc_checkout-rate');
        var rateIds = $.map(rates, function (rate) {
          return parseInt(rate.value);
        });

        if (rateIds.length <= 1) {
          // Single rate does not show, nothing to update
          return;
        }

        var formData = this.parseFormToJSON();
        var details = formData['mphb_room_details'][index];
        var adults = details.adults || '';
        var children = details.children || '';
        clearTimeout(this.updateRatesTimeout);
        this.updateRatesTimeout = setTimeout(function () {
          $.ajax({
            url: MPHB._data.ajaxUrl,
            type: 'GET',
            dataType: 'json',
            data: {
              action: 'mphb_update_rate_prices',
              mphb_nonce: MPHB._data.nonces.mphb_update_rate_prices,
              rates: rateIds,
              adults: adults,
              children: children,
              check_in_date: formData['mphb_check_in_date'],
              check_out_date: formData['mphb_check_out_date'],
              lang: MPHB._data.settings.currentLanguage
            },
            success: function success(response) {
              if (!response.hasOwnProperty('success')) {
                return;
              }

              var prices = response.data; // {%Rate ID%: %Price HTML%}

              $.each(rates, function (i, rate) {
                var rateId = rate.value;

                if (prices[rateId] == undefined) {
                  return;
                }

                var parent = $(rate).parent().children('strong'); // Remove old price

                parent.children('.mphb-price').remove(); // Add new price

                parent.append(prices[rateId]);
              });
            }
          });
        }, 500);
      },
      '.mphb_checkout-guests-chooser change': function mphb_checkoutGuestsChooserChange(el, e) {
        this.updateRatePrices(el.closest('.mphb-room-details'));
        this.updateCheckoutInfo();
      },
      '.mphb_checkout-rate change': function mphb_checkoutRateChange(el, e) {
        this.updateCheckoutInfo();
      },
      '.mphb_checkout-service, .mphb_checkout-service-adults change': function mphb_checkoutServiceMphb_checkoutServiceAdultsChange(el, e) {
        this.updateCheckoutInfo();
      },
      '.mphb_checkout-service-quantity input': function mphb_checkoutServiceQuantityInput(el, e) {
        this.updateCheckoutInfo();
      },

      /**
       * @param {Object} element
       * @param {Object} event
       *
       * @since 3.6.0
       */
      'select[name="mphb_country"] change': function selectNameMphb_countryChange(element, event) {
        if (this.billingSection != null) {
          var country = $(element).val();
          this.billingSection.onInput('country', country);
        }
      },

      /**
       * @returns {String|Boolean} Country name or FALSE.
       *
       * @since 3.6.0
       */
      getCountry: function getCountry() {
        return this.getCustomerDetail('country');
      },
      // See also assets/js/admin/dev/controls/price-breakdown-ctrl.js
      '.mphb-price-breakdown-expand click': function mphbPriceBreakdownExpandClick(el, e) {
        e.preventDefault();
        $(el).blur(); // Don't save a:focus style on last clicked item

        var tr = $(el).parents('tr.mphb-price-breakdown-group');
        tr.find('.mphb-price-breakdown-rate').toggleClass('mphb-hide');
        tr.nextUntil('tr.mphb-price-breakdown-group').toggleClass('mphb-hide');
        $(el).children('.mphb-inner-icon').toggleClass('mphb-hide');
      },
      hideErrors: function hideErrors() {
        this.errorsWrapperEl.empty().addClass('mphb-hide');
      },
      showError: function showError(message) {
        this.errorsWrapperEl.html(message).removeClass('mphb-hide');
      },
      showPreloader: function showPreloader() {
        this.waitResponse = true;
        this.bookBtnEl.attr('disabled', 'disabled');
        this.preloaderEl.removeClass('mphb-hide');
      },
      hidePreloader: function hidePreloader() {
        this.waitResponse = false;
        this.bookBtnEl.removeAttr('disabled');
        this.preloaderEl.addClass('mphb-hide');
      },
      parseFormToJSON: function parseFormToJSON() {
        if (this.element && this.element.length > 0) {
          return this.element.serializeJSON();
        }

        return false;
      },

      /**
       * @param {String} fieldName
       * @returns {Object}
       *
       * @since 3.7.2
       */
      getCustomerDetail: function getCustomerDetail(fieldName) {
        var fieldElement = this.element.find('#mphb_' + fieldName);

        if (fieldElement.length > 0) {
          return fieldElement.val();
        } else {
          return false;
        }
      },

      /**
       * @returns {Object} The maximum information about the customer: name, email,
       *     full address (if required) etc.
       *
       * @since 3.6.0
       */
      getCustomerDetails: function getCustomerDetails() {
        var customer = {
          email: '',
          first_name: '',
          last_name: ''
        };
        var customerFields = ['name', 'first_name', 'last_name', 'email', 'phone', 'country', 'address1', 'city', 'state', 'zip'];
        var self = this;
        customerFields.forEach(function (fieldName) {
          var customerDetail = self.getCustomerDetail(fieldName);

          if (customerDetail !== false) {
            customer[fieldName] = customerDetail;
          }
        });

        if (!customer.name) {
          var name = customer.first_name + ' ' + customer.last_name;
          customer.name = name.trim();
        }

        return customer;
      },

      /**
       * @since 3.6.1
       */
      getToPayAmount: function getToPayAmount() {
        var toPay = this.toPay;

        if (toPay == 0) {
          toPay = this.billingSection.getSelectedGatewayAmount();
        }

        return toPay;
      },

      /**
       * @since 3.6.0 added support of promises.
       */
      'submit': function submit(el, e) {
        if (this.waitResponse) {
          return false;
        } else if (MPHB._data.settings.useBilling && !this.freeBooking) {
          var amount = this.getToPayAmount();
          var customer = this.getCustomerDetails();
          var self = this;
          this.showPreloader();
          this.billingSection.canSubmit(amount, customer).then(function (canSubmit) {
            if (canSubmit) {
              // jQuery.submit() will re-trigger the "submit" event
              // (and current function). Instead, method form.submit()
              // does not trigger the event
              self.element[0].submit();
            } else {
              self.hidePreloader();
            }
          })["catch"](function (error) {
            self.hidePreloader();
            console.error('Billing error. ' + error.message);
          }); // Wait for response from billing section

          return false;
        }
      },
      '#mphb-price-details .mphb-remove-coupon click': function mphbPriceDetailsMphbRemoveCouponClick(el, e) {
        e.preventDefault();
        e.stopPropagation();

        if (MPHB._data.settings.useCoupons) {
          this.couponSection.removeCoupon();
          this.updateCheckoutInfo();
        }
      }
    });
    /**
     * @since 3.7.2
     */

    MPHB.GuestsChooser = can.Control.extend({}, {
      $adultsChooser: null,
      $childrenChooser: null,
      minAdults: 0,
      minChildren: 0,
      maxAdults: 0,
      maxChildren: 0,
      totalCapacity: 0,
      init: function init(element, args) {
        var $selects = element.find('.mphb_checkout-guests-chooser');

        if ($selects.length < 2) {
          return;
        }

        this.$adultsChooser = $($selects[0]);
        this.$childrenChooser = $($selects[1]);
        this.minAdults = args.minAdults;
        this.minChildren = args.minChildren;
        this.maxAdults = parseInt(this.$adultsChooser.data('max-allowed'));
        this.maxChildren = parseInt(this.$childrenChooser.data('max-allowed'));
        this.totalCapacity = parseInt($selects.data('max-total'));

        if (this.maxAdults + this.maxChildren > this.totalCapacity) {
          this.$adultsChooser.on('change', this.limitChildren.bind(this));
        }
      },
      limitChildren: function limitChildren() {
        var adults = this.$adultsChooser.val();
        var maxChildren = this.findMax(adults, this.minChildren, this.maxChildren);
        this.limitOptions(this.$childrenChooser, this.minChildren, maxChildren, adults);
      },
      findMax: function findMax(oppositeValue, defaultMin, defaultMax) {
        var maxValue = this.totalCapacity;

        if (oppositeValue !== '') {
          maxValue = this.totalCapacity - oppositeValue; // Don't make less than min possible number of adults/children

          maxValue = Math.max(defaultMin, maxValue);
        } // Don't make bigger than max possible number of adults/children


        return Math.min(maxValue, defaultMax);
      },
      limitOptions: function limitOptions($select, min, max, oppositeValue) {
        var maxValue = min; // Remove all options bigger than %max%

        $select.children().each(function (i, element) {
          var value = element.value;

          if (value !== '') {
            value = parseInt(value);

            if (value > max) {
              $(element).remove();
            } else if (value > maxValue) {
              maxValue = value;
            }
          }
        }); // Fill options up to %max%

        for (var i = maxValue + 1; i <= max; i++) {
          var $option = jQuery('<option value="' + i + '">' + i + '</option>');
          $select.append($option);
        } // Reset selection (select "— Select —")


        if (oppositeValue !== '') {
          $select.children(':selected').prop('selected', false);
        }
      }
    });

    (function ($) {
      $('#mphb-render-checkout-login').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        var form = $(this).parents('.mphb-login-form-wrap').find('.mphb-login-form');

        if (form.hasClass('mphb-hide')) {
          form.removeClass('mphb-hide');
        } else {
          form.addClass('mphb-hide');
        }
      });
    })(jQuery);
    /**
     * @requires ./gateway.js
     *
     * @since 3.6.0
     */


    MPHB.StripeGateway = MPHB.Gateway.extend({}, {
      // Settings
      publicKey: '',
      locale: 'auto',
      currency: 'EUR',
      successUrl: window.location.href,
      defaultCountry: '',
      paymentDescription: 'Accommodation(s) reservation',
      statementDescriptor: 'Hotel Booking',
      fullAddressRequired: false,
      i18n: {},
      style: {},
      // API controls
      api: null,
      elements: null,
      cardControl: null,
      idealControl: null,
      ibanControl: null,
      // Own controls
      payments: null,
      customer: null,
      // See canSubmit() and setCustomer()

      /**
       * What we know about the customer at the start of the page. Generally
       * it's an empty object (on Checkout Page). But on Payment Request
       * Checkout page, when we already have the bookign and customer
       * information, this object is set with some basic information required
       * for the script.
       *
       * @see MPHB.StripeGateway.setCustomer()
       */
      defaultCustomer: null,
      // Elements
      mountWrapper: null,
      errorsWrapper: null,
      // Errors
      hasErrors: false,
      undefinedError: MPHB._data.translations.errorHasOccured,
      init: function init(args) {
        this._super(args); // initSettings()
        // Docs: https://stripe.com/docs/stripe-js/reference#stripe-elements


        this.api = Stripe(this.publicKey);
        this.elements = this.api.elements({
          locale: this.locale
        });
        this.cardControl = this.elements.create('card', {
          style: this.style,
          hidePostalCode: this.fullAddressRequired
        });
        this.idealControl = this.elements.create('idealBank', {
          style: this.style
        });
        this.ibanControl = this.elements.create('iban', {
          style: this.style,
          supportedCountries: ['SEPA']
        });
        this.payments = new MPHB.StripeGateway.PaymentMethods(args.settings.paymentMethods, this.defaultCountry);
        this.addListeners();
      },
      initSettings: function initSettings(settings) {
        this._super(settings);

        this.publicKey = settings.publicKey;
        this.locale = settings.locale;
        this.currency = settings.currency;
        this.successUrl = settings.successUrl;
        this.defaultCountry = settings.defaultCountry;
        this.paymentDescription = settings.paymentDescription;
        this.statementDescriptor = settings.statementDescriptor;
        this.fullAddressRequired = MPHB._data.settings.fullAddressRequired; // See StripeGateway::getCheckoutData()

        this.defaultCustomer = settings.customer;
        this.i18n = settings.i18n;
        this.style = settings.style;
        this.idempotencyKey = $('.mphb_sc_checkout-form').find('input[name="' + settings.idempotencyKeyFieldName + '"]').val();
      },
      addListeners: function addListeners() {
        var onChange = this.onChange.bind(this);
        this.cardControl.on('change', onChange);
        this.ibanControl.on('change', onChange);
      },
      onChange: function onChange(event) {
        if (event.error) {
          this.showError(event.error.message);
          this.hasErrors = true;
        } else {
          this.hideErrors();
          this.hasErrors = false;
        }
      },
      onInput: function onInput(name, value) {
        if (name == 'country') {
          this.payments.selectCountry(value);
        }
      },
      afterSelection: function afterSelection(mountWrapper) {
        this._super(mountWrapper);

        mountWrapper.append(this.mountHtml());
        this.mountWrapper = mountWrapper;
        this.errorsWrapper = mountWrapper.find('#mphb-stripe-errors'); // Mount all controls

        this.cardControl.mount('#mphb-stripe-card-element');

        if (this.payments.isEnabled('ideal')) {
          this.idealControl.mount('#mphb-stripe-ideal-element');
        }

        if (this.payments.isEnabled('sepa_debit')) {
          this.ibanControl.mount('#mphb-stripe-iban-element');
        } // Mount payments control


        this.payments.mount(mountWrapper);
        var self = this;
        this.payments.inputs.on('change', function () {
          // Clear previous control
          switch (self.payments.currentPayment) {
            case 'card':
              self.cardControl.clear();
              break;

            case 'ideal':
              self.idealControl.clear();
              break;

            case 'sepa_debit':
              self.ibanControl.clear();
              break;
          } // Select new control


          self.payments.selectPayment(this.value);
        }); // Unhide elements

        mountWrapper.removeClass('mphb-billing-fields-hidden');
      },
      cancelSelection: function cancelSelection() {
        this._super();

        this.mountWrapper = null;
        this.errorsWrapper = null; // Unmount all controls

        this.cardControl.unmount();

        if (this.payments.isEnabled('ideal')) {
          this.idealControl.unmount();
        }

        if (this.payments.isEnabled('sepa_debit')) {
          this.ibanControl.unmount();
        } // Unmount payments control


        this.payments.unmount();
      },
      canSubmit: function canSubmit(amount, customer) {
        if (this.hasErrors) {
          return Promise.resolve(false);
        }

        this.setCustomer(customer);

        if (this.payments.currentPayment == 'card') {
          return this.createPaymentMethod().then(this.createPaymentIntent.bind(this, amount)).then(this.confirmCardPayment.bind(this)).then(this.handleStripeErrors.bind(this)).then(this.completeCardPayment.bind(this));
        } else {
          return this.createSource(amount).then(this.handleStripeErrors.bind(this)).then(this.completeSourcePayment.bind(this));
        }
      },
      setCustomer: function setCustomer(customerData) {
        var customer = $.extend({}, customerData); // Clone object
        // Init default fields (use data from StripeGateway::getCheckoutData())

        if (!customer.email) {
          customer.email = this.defaultCustomer.email;
        }

        if (!customer.name) {
          customer.name = this.defaultCustomer.name;
          customer.first_name = this.defaultCustomer.first_name;
          customer.last_name = this.defaultCustomer.last_name;
        } // Add field "country" if not exists


        if (!customer.hasOwnProperty('country')) {
          customer.country = this.payments.currentCountry;
        }

        this.customer = customer;
      },
      createPaymentIntent: function createPaymentIntent(amount, paymentMethodData) {
        var self = this;
        return new Promise(function (resolve, reject) {
          MPHB.post('create_stripe_payment_intent', {
            amount: amount,
            description: self.paymentDescription,
            paymentMethodId: paymentMethodData.paymentMethod.id
          }, {
            success: function success(response) {
              if (response.hasOwnProperty('success')) {
                if (response.success) {
                  var paymentIntent = {
                    id: response.data.id,
                    client_secret: response.data.client_secret,
                    object: 'payment_intent'
                  };
                  resolve(paymentIntent);
                } else {
                  self.showError(response.data.message);
                  reject(new Error(response.data.message));
                }
              } else {
                self.showError(self.undefinedError);
                reject(new Error(self.undefinedError));
              }
            },
            error: function error(jqXHR) {
              self.showError(self.undefinedError);
              reject(new Error(self.undefinedError));
            }
          }); // MPHB.post()
        }); // return new Promise()
      },
      confirmCardPayment: function confirmCardPayment(paymentIntent) {
        return this.api.confirmCardPayment(paymentIntent.client_secret, {
          paymentMethod: paymentIntent.paymentMethod
        });
      },
      createCardPayment: function createCardPayment(paymentIntent) {
        return this.api.handleCardPayment( // Another promise
        paymentIntent.client_secret, this.cardControl, {
          payment_method_data: {
            billing_details: {
              name: this.customer.name,
              email: this.customer.email
            }
          }
        });
      },
      createPaymentMethod: function createPaymentMethod() {
        return this.api.createPaymentMethod({
          // Another promise
          type: 'card',
          card: this.cardControl,
          billing_details: {
            name: this.customer.name,
            email: this.customer.email
          }
        });
      },

      /**
       * @param {Number} amount
       * @returns {Promise}
       */
      createSource: function createSource(amount) {
        var payment = this.payments.currentPayment;
        var customer = this.customer; // You can't even test "sepa_debit" until you get an invitation. See
        // more at https://stackoverflow.com/q/42583372/3918377

        var sourceArgs = {
          type: payment,
          amount: this.convertToSmallestUnit(amount),
          currency: this.currency.toLowerCase(),
          owner: {
            name: customer.name,
            email: customer.email
          },
          mandate: {
            notification_method: 'none'
          },
          redirect: {
            return_url: this.successUrl
          },
          statement_descriptor: this.statementDescriptor
        };
        var stripeControl = null;

        switch (payment) {
          // All except "card"
          case 'bancontact':
            // Supported locales: https://stripe.com/docs/sources/bancontact#create-source
            if (['en', 'de', 'fr', 'nl'].indexOf(this.locale) >= 0) {
              sourceArgs.bancontact = {
                preferred_language: this.locale
              };
            }

            break;

          case 'ideal':
            stripeControl = this.idealControl;
            break;

          case 'giropay':
            break;

          case 'sepa_debit':
            stripeControl = this.ibanControl;
            break;

          case 'sofort':
            sourceArgs.sofort = {
              country: customer.country
            }; // Supported locales: https://stripe.com/docs/sources/sofort#create-source

            if (['de', 'en', 'es', 'it', 'fr', 'nl', 'pl'].indexOf(this.locale) >= 0) {
              sourceArgs.sofort.preferred_language = this.locale;
            }

            break;
        }

        ;

        if (stripeControl != null) {
          return this.api.createSource(stripeControl, sourceArgs);
        } else {
          return this.api.createSource(sourceArgs);
        }
      },
      handleStripeErrors: function handleStripeErrors(stripeResponse) {
        if (stripeResponse.error) {
          this.showError(stripeResponse.error.message);
          throw new Error(stripeResponse.error.message);
        } else if (stripeResponse.paymentIntent != null) {
          return stripeResponse.paymentIntent;
        } else {
          return stripeResponse.source;
        }
      },
      completeCardPayment: function completeCardPayment(paymentIntent) {
        this.saveToCheckout('payment_method', this.payments.currentPayment);
        this.saveToCheckout('payment_intent_id', paymentIntent.id);
        return true; // Can submit
      },
      completeSourcePayment: function completeSourcePayment(source) {
        this.saveToCheckout('payment_method', this.payments.currentPayment);
        this.saveToCheckout('source_id', source.id); // The source object for SEPA Debit payment doesn't contain redirect.url property

        if (source.redirect && source.redirect.url && source.redirect.url.length > 0) {
          this.saveToCheckout('redirect_url', source.redirect.url);
        }

        return true; // Can submit
      },
      saveToCheckout: function saveToCheckout(field, value) {
        this.mountWrapper.find('#mphb_stripe_' + field).val(value);
      },
      convertToSmallestUnit: function convertToSmallestUnit(amount) {
        // See all currencies presented as links on page
        // https://stripe.com/docs/currencies#presentment-currencies
        switch (this.currency) {
          // Zero decimal currencies
          case 'BIF':
          case 'CLP':
          case 'DJF':
          case 'GNF':
          case 'JPY':
          case 'KMF':
          case 'KRW':
          case 'MGA':
          case 'PYG':
          case 'RWF':
          case 'UGX':
          case 'VND':
          case 'VUV':
          case 'XAF':
          case 'XOF':
          case 'XPF':
            amount = Math.floor(amount);
            break;

          default:
            amount = Math.round(amount * 100); // In cents

            break;
        }

        return amount;
      },
      mountHtml: function mountHtml() {
        var html = '<section id="mphb-stripe-payment-container" class="mphb-stripe-payment-container">';
        html += this.methodsHtml();
        html += this.fieldsHtml('card');
        html += this.fieldsHtml('bancontact');
        html += this.fieldsHtml('ideal');
        html += this.fieldsHtml('giropay');
        html += this.fieldsHtml('sepa_debit');
        html += this.fieldsHtml('sofort');
        html += '<div id="mphb-stripe-errors"></div>';
        html += '</section>';
        return html;
      },
      methodsHtml: function methodsHtml() {
        if (this.payments.onlyCardEnabled()) {
          return '';
        }

        var i18n = this.i18n;
        var html = '<nav id="mphb-stripe-payment-methods">';
        html += '<ul>';
        this.payments.forEach(function (payment, paymentMethod, stripePayments) {
          if (!paymentMethod.isEnabled) {
            return; // Don't show disabled methods
          }

          var isSelected = stripePayments.isSelected(payment);
          var activeClass = isSelected ? ' active' : '';
          var checkedAttr = isSelected ? ' checked="checked"' : '';
          html += '<li class="mphb-stripe-payment-method ' + payment + activeClass + '">';
          html += '<label>';
          html += '<input type="radio" name="stripe_payment_method" value="' + payment + '"' + checkedAttr + ' />' + i18n[payment];
          html += '</label>';
          html += '</li>';
        });
        html += '</ul>';
        html += '</nav>';
        return html;
      },
      fieldsHtml: function fieldsHtml(payment) {
        if (!this.payments.isEnabled(payment)) {
          return '';
        }

        var html = '';
        var hideClass = this.payments.isSelected(payment) ? '' : ' mphb-hide';
        html += '<div class="mphb-stripe-payment-fields ' + payment + hideClass + '">';
        html += '<fieldset>';

        switch (payment) {
          case 'card':
            html += this.cardHtml();
            break;

          case 'ideal':
            html += this.idealHtml();
            break;

          case 'sepa_debit':
            html += this.ibanHtml();
            break;

          default:
            html += this.redirectHtml();
            break;
        }

        html += '</fieldset>';

        if (payment == 'sepa_debit') {
          html += '<p class="notice">' + this.i18n.iban_policy + '</p>';
        }

        html += '</div>';
        return html;
      },
      cardHtml: function cardHtml() {
        var html = '';

        if (this.payments.onlyCardEnabled()) {
          html += '<label for="mphb-stripe-card-element">' + this.i18n.card_description + '</label>';
        }

        html += '<div id="mphb-stripe-card-element" class="mphb-stripe-element"></div>';
        return html;
      },
      idealHtml: function idealHtml() {
        return '<label for="mphb-stripe-ideal-element">' + this.i18n.ideal_bank + '</label>' + '<div id="mphb-stripe-ideal-element" class="mphb-stripe-element"></div>';
      },
      ibanHtml: function ibanHtml() {
        return '<label for="mphb-stripe-iban-element">' + this.i18n.iban + '</label>' + '<div id="mphb-stripe-iban-element" class="mphb-stripe-element"></div>';
      },
      redirectHtml: function redirectHtml() {
        return '<p class="notice">' + this.i18n.redirect_notice + '</p>';
      },
      showError: function showError(message) {
        this.errorsWrapper.html(message).removeClass('mphb-hide');
      },
      hideErrors: function hideErrors() {
        this.errorsWrapper.addClass('mphb-hide').text('');
      }
    });
    MPHB.DirectBooking = can.Control.extend({}, {
      reservationForm: null,
      // form.mphb-booking-form
      elementsToHide: null,
      // Quantity wrappers, price block and reservation section
      quantitySection: null,
      // div.mphb-reserve-room-section
      wrapperWithSelect: null,
      // .mphb-rooms-quantity-wrapper.mphb-rooms-quantity-multiple
      wrapperWithoutSelect: null,
      // .mphb-rooms-quantity-wrapper.mphb-rooms-quantity-single
      priceWrapper: null,
      // .mphb-period-price
      quantitySelect: null,
      // select.mphb-rooms-quantity
      availableLabel: null,
      // span.mphb-available-rooms-count
      typeId: 0,
      init: function init(el, args) {
        this.reservationForm = args.reservationForm;
        this.elementsToHide = el.find('.mphb-reserve-room-section, .mphb-rooms-quantity-wrapper, .mphb-regular-price');
        this.quantitySection = el.find('.mphb-reserve-room-section');
        this.wrapperWithSelect = this.quantitySection.find('.mphb-rooms-quantity-wrapper.mphb-rooms-quantity-multiple');
        this.wrapperWithoutSelect = this.quantitySection.find('.mphb-rooms-quantity-wrapper.mphb-rooms-quantity-single');
        this.priceWrapper = this.quantitySection.find('.mphb-period-price');
        this.quantitySelect = this.quantitySection.find('.mphb-rooms-quantity');
        this.availableLabel = this.quantitySection.find('.mphb-available-rooms-count'); // TODO: get this from reservation form? and remove input?

        this.typeId = el.find('input[name="mphb_room_type_id"]').val();
        this.typeId = parseInt(this.typeId);
      },
      hideSections: function hideSections() {
        this.elementsToHide.addClass('mphb-hide');
        this.reservationForm.reserveBtnWrapper.removeClass('mphb-hide');
      },
      showSections: function showSections(showPrice) {
        this.reservationForm.reserveBtnWrapper.addClass('mphb-hide');
        this.quantitySection.removeClass('mphb-hide');

        if (showPrice) {
          this.priceWrapper.removeClass('mphb-hide');
        }
      },
      resetQuantityOptions: function resetQuantityOptions(count) {
        this.quantitySelect.empty();

        for (var i = 1; i <= count; i++) {
          var option = '<option value="' + i + '">' + i + '</option>';
          this.quantitySelect.append(option);
        }

        this.quantitySelect.val(1); // Otherwise the last option will be active
        // Also update text "of %d accommodation(-s) available."

        this.availableLabel.text(count);

        if (count > 1) {
          this.wrapperWithSelect.removeClass('mphb-hide');
        } else {
          this.wrapperWithoutSelect.removeClass('mphb-hide');
        }
      },
      setupPrice: function setupPrice(price, priceHtml) {
        this.priceWrapper.children('.mphb-price, .mphb-price-period, .mphb-tax-information').remove();

        if (price > 0 && priceHtml != '') {
          this.priceWrapper.append(priceHtml);
        }
      },
      showError: function showError(errorMessage) {
        this.hideSections();
        this.reservationForm.showError(errorMessage);
      },
      loadAvailabilityAndPriceData: function loadAvailabilityAndPriceData() {
        var checkIn = this.reservationForm.checkInDatepicker.getDate();
        var checkOut = this.reservationForm.checkOutDatepicker.getDate();
        if (!checkIn || !checkOut) return;
        this.reservationForm.clearErrors();
        this.reservationForm.lock();
        var self = this;
        $.ajax({
          url: MPHB._data.ajaxUrl,
          type: 'GET',
          dataType: 'json',
          data: {
            action: 'mphb_get_room_type_availability_data',
            mphb_nonce: MPHB._data.nonces.mphb_get_room_type_availability_data,
            room_type_id: this.typeId,
            check_in_date: $.datepick.formatDate(MPHB._data.settings.dateTransferFormat, checkIn),
            check_out_date: $.datepick.formatDate(MPHB._data.settings.dateTransferFormat, checkOut),
            adults_count: this.reservationForm.getAdults(),
            children_count: this.reservationForm.getChildren(),
            lang: MPHB._data.settings.currentLanguage
          },
          success: function success(response) {
            if (response.success) {
              self.resetQuantityOptions(response.data.freeCount);
              self.setupPrice(response.data.price, response.data.priceHtml);
              self.showSections(response.data.price > 0);
            } else {
              self.showError(response.data.message);
            }
          },
          error: function error(jqXHR) {
            self.showError(MPHB._data.translations.errorHasOccured);
          },
          complete: function complete(jqXHR) {
            self.reservationForm.unlock();
          }
        });
      },

      /**
       * See also MPHB.ReservationForm.onDatepickChange().
       */
      'input.mphb-datepick change': function inputMphbDatepickChange(element, event) {
        this.hideSections();
      },
      '.mphb-reserve-btn click': function mphbReserveBtnClick(element, event) {
        event.preventDefault();
        event.stopPropagation();
        var checkIn = this.reservationForm.checkInDatepicker.getDate();
        var checkOut = this.reservationForm.checkOutDatepicker.getDate();

        if (!checkIn || !checkOut) {
          if (!checkIn) {
            this.showError(MPHB._data.translations.checkInNotValid);
          } else {
            this.showError(MPHB._data.translations.checkOutNotValid);
          }

          this.reservationForm.unlock();
        } else {
          this.loadAvailabilityAndPriceData();
        }
      },
      'input.mphb-datepick, select[name="mphb_children"] change': function inputMphbDatepickSelectNameMphb_childrenChange(element, event) {
        this.loadAvailabilityAndPriceData();
      },
      'select[name="mphb_adults"] change': function selectNameMphb_adultsChange(element, event) {
        // restrict children count according to max capacity and selected adult count
        var childrenSelect = jQuery('select[name="mphb_children"]');

        if (childrenSelect.length && undefined !== childrenSelect.data('max-total')) {
          var minAllowedChildren = parseInt(childrenSelect.data('min-allowed'));
          var maxAllowedChildren = parseInt(childrenSelect.data('max-allowed'));
          var totalCapacity = parseInt(childrenSelect.data('max-total'));
          var selectedAdultsCount = parseInt(element.val());
          var maxChildrenCount = Math.min(Math.max(minAllowedChildren, totalCapacity - selectedAdultsCount), maxAllowedChildren);
          var childrenSelectedCount = parseInt(childrenSelect.val());
          childrenSelect.empty();

          for (var i = minAllowedChildren; i <= maxChildrenCount; i++) {
            var $option = jQuery('<option value="' + i + '"' + (i === childrenSelectedCount ? 'selected="selected"' : '') + '>' + i + '</option>');
            childrenSelect.append($option);
          }
        }

        this.loadAvailabilityAndPriceData();
      }
    });
    MPHB.ReservationForm = can.Control.extend({}, {
      /**
       * @var jQuery
       */
      $formElement: null,

      /**
       * @var MPHB.RoomTypeCheckInDatepicker
       */
      checkInDatepicker: null,

      /**
       * @var MPHB.RoomTypeCheckOutDatepicker
       */
      checkOutDatepicker: null,

      /**
       * @var jQuery
       */
      reserveBtnWrapper: null,

      /**
       * @var jQuery
       */
      errorsWrapper: null,

      /**
       * @var {MPHB.DirectBooking|null}
       */
      directBooking: null,

      /**
       * @var int
       */
      roomTypeId: null,
      init: function init($formElement) {
        this.$formElement = $formElement;
        this.roomTypeId = parseInt(this.$formElement.attr('id').replace(/^booking-form-/, ''));
        this.errorsWrapper = this.$formElement.find('.mphb-errors-wrapper');
        var firstAvailableCheckInDateYmd = this.$formElement.attr('data-first_available_check_in_date');

        if (!firstAvailableCheckInDateYmd) {
          firstAvailableCheckInDateYmd = $.datepick.formatDate('yyyy-mm-dd', new Date());
        } // init Check-In Datepicker


        this.checkInDatepicker = new MPHB.RoomTypeCheckInDatepicker(this.$formElement.find('input[type="text"][id^=mphb_check_in_date]'), {
          form: this,
          roomTypeId: '1' == MPHB._data.settings.isDirectBooking ? this.roomTypeId : 0,
          firstAvailableCheckInDateYmd: firstAvailableCheckInDateYmd
        }); // init Check-Out Datepicker

        this.checkOutDatepicker = new MPHB.RoomTypeCheckOutDatepicker(this.$formElement.find('input[type="text"][id^=mphb_check_out_date]'), {
          form: this,
          roomTypeId: '1' == MPHB._data.settings.isDirectBooking ? this.roomTypeId : 0,
          firstAvailableCheckInDateYmd: firstAvailableCheckInDateYmd
        });
        this.reserveBtnWrapper = this.$formElement.find('.mphb-reserve-btn-wrapper'); // Init direct booking

        if ('1' == MPHB._data.settings.isDirectBooking) {
          this.directBooking = new MPHB.DirectBooking(this.$formElement, {
            reservationForm: this
          });
        }

        $(window).on('mphb-update-date-room-type-' + this.roomTypeId, this.proxy(function () {
          this.checkInDatepicker.refresh();
          this.checkOutDatepicker.refresh();
        }));
        this.unlock();
      },
      updateCheckOutLimitations: function updateCheckOutLimitations() {
        this.checkOutDatepicker.updateCheckOutLimitations(this.checkInDatepicker.getDate());
      },

      /**
       * @returns {Number|String}
       * @since 3.8.3
       */
      getAdults: function getAdults() {
        var input = this.$formElement.find('[name="mphb_adults"]');
        return input.length > 0 ? parseInt(input.val()) : '';
      },

      /**
       * @returns {Number|String}
       * @since 3.8.3
       */
      getChildren: function getChildren() {
        var input = this.$formElement.find('[name="mphb_children"]');
        return input.length > 0 ? parseInt(input.val()) : '';
      },
      showError: function showError(message) {
        this.clearErrors();
        var errorMessage = $('<p>', {
          'class': 'mphb-error',
          'html': message
        });
        this.errorsWrapper.append(errorMessage).removeClass('mphb-hide');
      },
      clearErrors: function clearErrors() {
        this.errorsWrapper.empty().addClass('mphb-hide');
      },
      lock: function lock() {
        this.element.addClass('mphb-loading');
      },
      unlock: function unlock() {
        this.element.removeClass('mphb-loading');
      },

      /**
       * See also MPHB.DirectBooking["input.mphb-datepick change"].
       */
      onDatepickChange: function onDatepickChange() {
        if (null !== this.directBooking) {
          this.directBooking.hideSections();
        }
      }
    });
    MPHB.RoomTypeCalendar = can.Control.extend({}, {
      roomTypeId: null,
      $calendarElement: null,
      isShowPrices: false,
      isTruncatePrices: true,
      isShowPricesCurrency: false,
      allShownMonthsCount: 1,
      // for clickable calendar
      isClickable: false,
      reservationFormElement: null,
      $reservationFormCheckInElement: null,
      $reservationFormCheckOutElement: null,
      isSyncWithReservationFormInitialised: false,
      isSyncWithReservationFormOn: true,
      lastDrawDate: null,
      isCheckInSelected: false,
      isCheckOutSelected: false,
      minCheckOutDateForSelection: null,
      maxCheckOutDateForSelection: null,
      minStayDateAfterCheckIn: null,
      maxStayDateAfterCheckIn: null,
      init: function init($calendarElement, args) {
        var self = this;
        this.$calendarElement = $calendarElement;
        this.roomTypeId = parseInt(this.$calendarElement.data('roomTypeId'));

        if (undefined !== this.$calendarElement.data('is_show_prices')) {
          this.isShowPrices = Boolean(this.$calendarElement.data('is_show_prices'));
        }

        if (undefined !== this.$calendarElement.data('is_truncate_prices')) {
          this.isTruncatePrices = Boolean(this.$calendarElement.data('is_truncate_prices'));
        }

        if (undefined !== this.$calendarElement.data('is_show_prices_currency')) {
          this.isShowPricesCurrency = Boolean(this.$calendarElement.data('is_show_prices_currency'));
        }

        var monthsToShow = MPHB._data.settings.numberOfMonthCalendar;
        var customMonths = this.$calendarElement.attr('data-monthstoshow');

        if (customMonths) {
          var customArray = customMonths.split(',');
          monthsToShow = customArray.length == 1 ? parseInt(customMonths) : customArray;
        }

        if (Array.isArray(monthsToShow)) {
          this.allShownMonthsCount = parseInt(monthsToShow[0]) * parseInt(monthsToShow[1]);
        } else {
          this.allShownMonthsCount = monthsToShow;
        } // check is calendar clickable


        if ('1' == MPHB._data.settings.isDirectBooking) {
          this.reservationFormElement = $('#booking-form-' + this.roomTypeId);
          this.isClickable = 0 < this.reservationFormElement.length;

          if (this.isClickable) {
            this.$reservationFormCheckInElement = this.reservationFormElement.find('input[type="text"][id^=mphb_check_in_date]');
            this.$reservationFormCheckOutElement = this.reservationFormElement.find('input[type="text"][id^=mphb_check_out_date]');
          }
        }

        var firstAvailableCheckInDateYmd = this.$calendarElement.attr('data-first_available_check_in_date');

        if (!firstAvailableCheckInDateYmd) {
          firstAvailableCheckInDateYmd = $.datepick.formatDate('yyyy-mm-dd', new Date());
        }

        var firstAvailableCheckInDate = new Date(firstAvailableCheckInDateYmd); // load first part of calendar data

        MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(firstAvailableCheckInDate.getFullYear(), firstAvailableCheckInDate.getMonth(), 1), this.allShownMonthsCount, this.roomTypeId, this.isShowPrices, this.isTruncatePrices, this.isShowPricesCurrency, function () {
          self.$calendarElement.addClass('mphb-loading');
        }, function () {
          self.doAfterNewCalendarDataLoaded(self);
          self.$calendarElement.removeClass('mphb-loading');
        });
        this.$calendarElement.hide().datepick({
          minDate: new Date(),
          defaultDate: firstAvailableCheckInDate,
          monthsToShow: monthsToShow,
          firstDay: MPHB._data.settings.firstDay,
          pickerClass: MPHB._data.settings.datepickerClass,
          useMouseWheel: false,
          rangeSelect: self.isClickable,
          showSpeed: 0,
          onChangeMonthYear: function onChangeMonthYear(year, month) {
            if (self.isClickable) {
              self.lastDrawDate = $.datepick._getInst(self.$calendarElement).drawDate;
            } // load calendar data for later use in onDate


            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(year, month - 1, 1), self.allShownMonthsCount, self.roomTypeId, self.isShowPrices, self.isTruncatePrices, self.isShowPricesCurrency, function () {
              self.$calendarElement.addClass('mphb-loading');
            }, function () {
              self.doAfterNewCalendarDataLoaded(self);
              self.$calendarElement.removeClass('mphb-loading');
            });
          },
          onDate: function onDate(date, isCurrentMonth) {
            var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(self.roomTypeId, self.isShowPrices, self.isTruncatePrices, self.isShowPricesCurrency);
            var calendarDateAttributes = MPHB.calendarHelper.getCalendarDateAttributesFromAvailability(1, date, isCurrentMonth, roomTypeCalendarData, self.isShowPrices);

            if (isCurrentMonth) {
              calendarDateAttributes = self.fillClickableCalendarDateData(calendarDateAttributes, date);
            }

            return calendarDateAttributes;
          },
          onSelect: function onSelect(selectedDates) {
            // do nothing if it is not clickable calendar or
            // if it was click to remove checkin selection
            // because checkout selection is not possible
            if (!self.isClickable || 0 === selectedDates.length) return;

            if (!self.isCheckInSelected || self.isCheckInSelected && self.isCheckOutSelected) {
              self.isCheckInSelected = true;
              self.isCheckOutSelected = false; //self.calculateMinMaxCheckOutDateForSelection(selectedDates[0]);

              var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(self.roomTypeId, self.isShowPrices, self.isTruncatePrices, self.isShowPricesCurrency);
              var result = MPHB.calendarHelper.calculateMinMaxCheckOutDateForSelection(selectedDates[0], roomTypeCalendarData);
              self.minCheckOutDateForSelection = result.minCheckOutDateForSelection;
              self.maxCheckOutDateForSelection = result.maxCheckOutDateForSelection;
              self.minStayDateAfterCheckIn = result.minStayDateAfterCheckIn;
              self.maxStayDateAfterCheckIn = result.maxStayDateAfterCheckIn;
            } else {
              self.isCheckOutSelected = true;
            } // do not change first drawing month after selection


            var instance = $.datepick._getInst(self.$calendarElement);

            instance.drawDate = self.lastDrawDate;
            instance.options.setSelectedDatesToStatusBar(instance, selectedDates);
            self.fillReservationFormWithSelectedDates(selectedDates);
          },
          onShow: function onShow(element, instance) {
            // 	remove highlight right after calendar was shown
            // 	to avoide of date highlighting even when mouse pointer not over calendar
            element.find('.datepick-highlight').removeClass('datepick-highlight');

            if (self.isClickable) {
              // save draw date to make sure it will not be change on selection
              self.lastDrawDate = instance.drawDate;
              instance.options.initStatusBar(element, instance);
              instance.options.initSelectionOnHover(element, instance);
            }
          },
          setSelectedDatesToStatusBar: function setSelectedDatesToStatusBar(instance, selectedDates) {
            var selectedDatesText = MPHB._data.translations.selectDates;

            if (self.isCheckInSelected) {
              selectedDatesText = $.datepick.formatDate(MPHB._data.settings.dateFormat, selectedDates[0]);

              if (self.isCheckOutSelected) {
                selectedDatesText += ' - ' + $.datepick.formatDate(MPHB._data.settings.dateFormat, selectedDates[1]);
              }
            }

            instance.options.renderer.picker = '<div class="datepick">' + '<div class="datepick-nav">{link:prev}{link:today}{link:next}</div>{months}' + '<div class="datepick-ctrl"><div class="mphb-calendar__selected-dates">' + selectedDatesText + '</div>{link:clear}</div>' + '<div class="datepick-clear-fix"></div></div>';
          },
          initStatusBar: function initStatusBar(element, instance) {
            // clone object to avoide of other calendars changing
            instance.options.renderer = Object.assign({}, instance.options.renderer);
            instance.options.setSelectedDatesToStatusBar(instance, self.$calendarElement.datepick('getDate'));
            instance.options.commands = Object.assign({}, instance.options.commands);
            instance.options.commands.close.keystroke = {};
            instance.options.commands.clear.keystroke = {
              keyCode: 27,
              altKey: false
            };

            instance.options.commands.clear.action = function (instance) {
              // all commands have common functions for all instances!
              // so we need to take it into account in command action function
              if (instance === $.datepick._getInst(self.$calendarElement) || instance === $.datepick._getInst(self.$reservationFormCheckInElement) || instance === $.datepick._getInst(self.$reservationFormCheckOutElement)) {
                if (self.isCheckInSelected) {
                  // we need to save and set draw date again before refresh
                  // to make sure calendar does not change current first drawn month
                  var currentDrawDate = MPHB.Utils.cloneDate(self.lastDrawDate);
                  self.isCheckInSelected = false;
                  self.isCheckOutSelected = false;
                  self.$calendarElement.datepick('setDate', null);
                  self.$reservationFormCheckInElement.datepick('setDate', null);
                  self.$reservationFormCheckOutElement.datepick('setDate', null);
                  instance.drawDate = currentDrawDate;
                  self.lastDrawDate = currentDrawDate;
                  self.refresh();
                }
              } else {
                instance.elem.datepick('clear');
              }
            };
          },
          initSelectionOnHover: function initSelectionOnHover(element, instance) {
            // mark dates as selected between check-in and potensial check-out on hover
            element.find(instance.get('renderer').daySelector + ' a').hover(function () {
              if (self.isCheckInSelected && !self.isCheckOutSelected) {
                var currentHoverDate = $.datepick.retrieveDate(self.$calendarElement, this),
                    selectedDates = self.$calendarElement.datepick('getDate'),
                    processingDate = MPHB.Utils.cloneDate(selectedDates[0]);
                processingDate.setDate(processingDate.getDate() + 1);

                if (selectedDates[0].getTime() < currentHoverDate.getTime() && self.$calendarElement.datepick('isSelectable', currentHoverDate)) {
                  while (currentHoverDate.getTime() > processingDate.getTime()) {
                    self.$calendarElement.find('.dp' + processingDate.getTime()).not('.mphb-extra-date').addClass('mphb-selected-date');
                    processingDate.setDate(processingDate.getDate() + 1);
                  }
                }
              }
            }, function () {
              if (self.isCheckInSelected && !self.isCheckOutSelected) {
                self.$calendarElement.find('.mphb-selected-date').removeClass('mphb-selected-date');
              }
            });
          }
        }).show();
      },
      doAfterNewCalendarDataLoaded: function doAfterNewCalendarDataLoaded(self) {
        if (self.isCheckInSelected && !self.isCheckOutSelected) {
          var dates = self.$calendarElement.datepick('getDate');
          var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(self.roomTypeId, self.isShowPrices, self.isTruncatePrices, self.isShowPricesCurrency);
          var result = MPHB.calendarHelper.calculateMinMaxCheckOutDateForSelection(dates[0], roomTypeCalendarData);
          self.minCheckOutDateForSelection = result.minCheckOutDateForSelection;
          self.maxCheckOutDateForSelection = result.maxCheckOutDateForSelection;
          self.minStayDateAfterCheckIn = result.minStayDateAfterCheckIn;
          self.maxStayDateAfterCheckIn = result.maxStayDateAfterCheckIn;
        }

        self.refresh();

        if (self.isClickable && !self.isSyncWithReservationFormInitialised) {
          self.isSyncWithReservationFormInitialised = true;
          self.initSyncWithReservationForm();
        }
      },
      fillClickableCalendarDateData: function fillClickableCalendarDateData(calendarDateData, date) {
        if (!this.isClickable) {
          calendarDateData.selectable = false;
          return calendarDateData;
        }

        var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(this.roomTypeId, this.isShowPrices, this.isTruncatePrices, this.isShowPricesCurrency);
        var formattedDate = $.datepick.formatDate('yyyy-mm-dd', date),
            roomTypeData = roomTypeCalendarData[formattedDate];

        if (undefined === roomTypeData || 0 === Object.keys(roomTypeData).length || !roomTypeData.hasOwnProperty('roomTypeStatus')) {
          return calendarDateData;
        } // checkIn is not selected yet or both dates are selected already
        // and user can select checkIn date again


        if (!this.isCheckInSelected || this.isCheckInSelected && this.isCheckOutSelected) {
          if (MPHB.calendarHelper.ROOM_STATUS_AVAILABLE === roomTypeData.roomTypeStatus && (!roomTypeData.hasOwnProperty('isCheckInNotAllowed') || !roomTypeData.isCheckInNotAllowed)) {
            calendarDateData.selectable = true;
            calendarDateData.dateClass += ' mphb-selectable-date--check-in';
          } else {
            calendarDateData.selectable = false;
            calendarDateData.dateClass += ' mphb-unselectable-date--check-in';
          }
        } else {
          // checkIn selected but checkOut is not
          if (null !== this.minCheckOutDateForSelection && this.minCheckOutDateForSelection.getTime() <= date.getTime() && (null === this.maxCheckOutDateForSelection || this.maxCheckOutDateForSelection.getTime() >= date.getTime()) && (!roomTypeData.hasOwnProperty('isCheckOutNotAllowed') || !roomTypeData.isCheckOutNotAllowed)) {
            calendarDateData.selectable = true;
            calendarDateData.dateClass += ' mphb-selectable-date--check-out';
          } else {
            calendarDateData.selectable = false;
            calendarDateData.dateClass += ' mphb-unselectable-date--check-out';
          }

          if (null !== this.minStayDateAfterCheckIn && this.minStayDateAfterCheckIn.getTime() > date.getTime()) {
            calendarDateData.title += '\n' + MPHB._data.translations.lessThanMinDaysStay;
          }

          if (null !== this.maxStayDateAfterCheckIn && this.maxStayDateAfterCheckIn.getTime() < date.getTime()) {
            calendarDateData.title += '\n' + MPHB._data.translations.moreThanMaxDaysStay;
          }
        }

        if (this.isCheckInSelected || this.isCheckOutSelected) {
          var dates = this.$calendarElement.datepick('getDate'),
              checkInSelectedDate = MPHB.Utils.cloneDate(dates[0]),
              checkInFormattedDate = $.datepick.formatDate('yyyy-mm-dd', checkInSelectedDate),
              checkOutSelectedDate = MPHB.Utils.cloneDate(dates[1]),
              checkOutFormattedDate = $.datepick.formatDate('yyyy-mm-dd', checkOutSelectedDate),
              currentProcessingFormattedDate = $.datepick.formatDate('yyyy-mm-dd', date); // normalise date to avoide days border fluctuations

          checkInSelectedDate.setHours(0, 0, 0, 0);
          checkOutSelectedDate.setHours(23, 59, 59, 999);

          if (checkInFormattedDate === currentProcessingFormattedDate) {
            calendarDateData.dateClass += ' mphb-selected-date--check-in';
          } else if (this.isCheckOutSelected && checkOutFormattedDate === currentProcessingFormattedDate) {
            calendarDateData.dateClass += ' mphb-selected-date--check-out';
          } else if (this.isCheckInSelected && this.isCheckOutSelected && checkInSelectedDate.getTime() <= date.getTime() && checkOutSelectedDate.getTime() >= date.getTime()) {
            calendarDateData.dateClass += ' mphb-selected-date';
          }
        }

        return calendarDateData;
      },
      selectCheckInDateInCalendar: function selectCheckInDateInCalendar($newCheckInDate) {
        var selectedDates = this.$calendarElement.datepick('getDate');

        if ($.datepick.formatDate('yyyy-mm-dd', selectedDates[0]) !== $.datepick.formatDate('yyyy-mm-dd', $newCheckInDate)) {
          if (this.isCheckInSelected && !this.isCheckOutSelected) {
            // switch to Check-In selection mode
            this.isCheckInSelected = false;
          }

          this.$calendarElement.datepick('setDate', $newCheckInDate);

          var instance = $.datepick._getInst(this.$calendarElement);

          instance.pickingRange = true;
          this.refresh();
        }
      },
      selectCheckOutDateInCalendar: function selectCheckOutDateInCalendar($newCheckOutDate) {
        if (!this.isCheckInSelected) return;
        var selectedDates = this.$calendarElement.datepick('getDate');

        if ($.datepick.formatDate('yyyy-mm-dd', selectedDates[1]) !== $.datepick.formatDate('yyyy-mm-dd', $newCheckOutDate)) {
          if (this.isCheckOutSelected) {
            // switch to Check-Out selection mode
            this.isCheckOutSelected = false;
          }

          this.$calendarElement.datepick('setDate', selectedDates[0], $newCheckOutDate);
          this.refresh();
        }
      },
      initSyncWithReservationForm: function initSyncWithReservationForm() {
        var _this2 = this;

        // get selected dates from booking form if it has them from session
        var reservationFormCheckInDate = this.$reservationFormCheckInElement.datepick('getDate')[0],
            reservationFormCheckOutDate = this.$reservationFormCheckOutElement.datepick('getDate')[0];

        if (reservationFormCheckInDate) {
          this.isSyncWithReservationFormOn = false;
          this.selectCheckInDateInCalendar(reservationFormCheckInDate);

          if (reservationFormCheckOutDate) {
            this.selectCheckOutDateInCalendar(reservationFormCheckOutDate);
          }

          this.isSyncWithReservationFormOn = true;
        }

        this.$reservationFormCheckInElement.change(function (event) {
          var reservationFormCheckInDate = _this2.$reservationFormCheckInElement.datepick('getDate')[0];

          _this2.isSyncWithReservationFormOn = false;

          _this2.selectCheckInDateInCalendar(reservationFormCheckInDate);

          _this2.isSyncWithReservationFormOn = true;
        });
        this.$reservationFormCheckOutElement.change(function (event) {
          var reservationFormCheckOutDate = _this2.$reservationFormCheckOutElement.datepick('getDate')[0]; // we do not clear check-out in calendar because it clears after check-in selected


          if (undefined === reservationFormCheckOutDate) return;
          _this2.isSyncWithReservationFormOn = false;

          _this2.selectCheckOutDateInCalendar(reservationFormCheckOutDate);

          _this2.isSyncWithReservationFormOn = true;
        });
      },
      fillReservationFormWithSelectedDates: function fillReservationFormWithSelectedDates(selectedDates) {
        if (!this.isSyncWithReservationFormOn) return;

        if (this.isCheckInSelected) {
          var reservationFormCheckInDate = this.$reservationFormCheckInElement.datepick('getDate')[0];

          if ($.datepick.formatDate('yyyy-mm-dd', selectedDates[0]) !== $.datepick.formatDate('yyyy-mm-dd', reservationFormCheckInDate)) {
            this.$reservationFormCheckInElement.datepick('setDate', selectedDates[0]);
          }
        }

        if (this.isCheckOutSelected) {
          var reservationFormCheckOutDate = this.$reservationFormCheckOutElement.datepick('getDate')[0];

          if ($.datepick.formatDate('yyyy-mm-dd', selectedDates[1]) !== $.datepick.formatDate('yyyy-mm-dd', reservationFormCheckOutDate)) {
            this.$reservationFormCheckOutElement.datepick('setDate', selectedDates[1]);
          }
        } else {
          // clear check-out date if it was set before
          this.$reservationFormCheckOutElement.datepick('setDate', null);
        }
      },
      refresh: function refresh() {
        this.$calendarElement.hide();

        $.datepick._update(this.$calendarElement, true);

        this.$calendarElement.show();
      }
    });
    /**
     *
     * @requires ./../datepicker.js
     */

    MPHB.Datepicker('MPHB.RoomTypeCheckInDatepicker', {}, {
      getDatepickSettings: function getDatepickSettings() {
        var self = this;
        return {
          minDate: new Date(),
          defaultDate: this.firstAvailableCheckInDate,
          onShow: function onShow(element, instance) {
            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(instance.drawDate.getFullYear(), instance.drawDate.getMonth(), 1), MPHB._data.settings.numberOfMonthDatepicker, self.roomTypeId, false, false, false, function () {
              self.lock();
            }, function () {
              self.form.updateCheckOutLimitations();
              self.refresh();
              self.unlock();
            }, MPHB._data.settings.numberOfMonthDatepicker); // 	remove highlight right after calendar was shown
            // 	to avoide of date highlighting when date is not selected yet

            element.find('.datepick-highlight').removeClass('datepick-highlight');
          },
          onChangeMonthYear: function onChangeMonthYear(year, month) {
            // load calendar data for later use in onDate
            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(year, month - 1, 1), MPHB._data.settings.numberOfMonthDatepicker, self.roomTypeId, false, false, false, function () {
              self.lock();
            }, function () {
              self.refresh();
              self.unlock();
            }, MPHB._data.settings.numberOfMonthDatepicker);
          },
          onSelect: function onSelect(dates) {
            self.form.updateCheckOutLimitations();
            self.form.onDatepickChange(); // we clear check-out date if a new check-in date was selected

            self.form.checkOutDatepicker.clear();
            self.element.trigger('change');
          },
          onDate: function onDate(date, isCurrentMonth) {
            var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(self.roomTypeId);
            var calendarDateAttributes = MPHB.calendarHelper.getCalendarDateAttributesFromAvailability(2, date, isCurrentMonth, roomTypeCalendarData);

            if (!isCurrentMonth || calendarDateAttributes.isPastDate || calendarDateAttributes.isUnavailable || calendarDateAttributes.isUnavailableCheckIn) {
              calendarDateAttributes.dateClass += ' mphb-unselectable-date';
            } else {
              calendarDateAttributes.selectable = true;
              calendarDateAttributes.dateClass += ' mphb-selectable-date';
            }

            return calendarDateAttributes;
          },
          pickerClass: 'mphb-datepick-popup mphb-check-in-datepick ' + MPHB._data.settings.datepickerClass
        };
      }
    });
    /**
     *
     * @requires ./../datepicker.js
     */

    MPHB.RoomTypeCheckOutDatepicker = MPHB.Datepicker.extend({}, {
      minCheckOutDateForSelection: null,
      maxCheckOutDateForSelection: null,
      minStayDateAfterCheckIn: null,
      maxStayDateAfterCheckIn: null,

      /**
       * @param {Date} checkInDate
       */
      updateCheckOutLimitations: function updateCheckOutLimitations(checkInDate) {
        if (!checkInDate) {
          this.minCheckOutDateForSelection = null;
          this.maxCheckOutDateForSelection = null;
          this.minStayDateAfterCheckIn = null;
          this.maxStayDateAfterCheckIn = null;
          this.element.datepick('option', 'defaultDate', this.firstAvailableCheckInDate);
          return;
        }

        var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(this.roomTypeId);
        var result = MPHB.calendarHelper.calculateMinMaxCheckOutDateForSelection(checkInDate, roomTypeCalendarData);
        this.minCheckOutDateForSelection = result.minCheckOutDateForSelection;
        this.maxCheckOutDateForSelection = result.maxCheckOutDateForSelection;
        this.minStayDateAfterCheckIn = result.minStayDateAfterCheckIn;
        this.maxStayDateAfterCheckIn = result.maxStayDateAfterCheckIn;
        this.element.datepick('option', 'defaultDate', this.minCheckOutDateForSelection ? this.minCheckOutDateForSelection : checkInDate);
      },
      getDatepickSettings: function getDatepickSettings() {
        var self = this;
        return {
          minDate: new Date(),
          defaultDate: this.minCheckOutDateForSelection ? this.minCheckOutDateForSelection : this.firstAvailableCheckInDate,
          onShow: function onShow(element, instance) {
            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(instance.drawDate.getFullYear(), instance.drawDate.getMonth(), 1), MPHB._data.settings.numberOfMonthDatepicker, self.roomTypeId, false, false, false, function () {
              self.lock();
            }, function () {
              self.form.updateCheckOutLimitations();
              self.refresh();
              self.unlock();
            }, MPHB._data.settings.numberOfMonthDatepicker); // 	remove highlight right after calendar was shown
            // 	to avoide of date highlighting when date is not selected yet

            element.find('.datepick-highlight').removeClass('datepick-highlight');
          },
          onChangeMonthYear: function onChangeMonthYear(year, month) {
            // load calendar data for later use in onDate
            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(year, month - 1, 1), MPHB._data.settings.numberOfMonthDatepicker, self.roomTypeId, false, false, false, function () {
              self.lock();
            }, function () {
              self.form.updateCheckOutLimitations();
              self.refresh();
              self.unlock();
            }, MPHB._data.settings.numberOfMonthDatepicker);
          },
          onSelect: function onSelect(dates) {
            self.form.onDatepickChange();
            self.element.trigger('change');
          },
          onDate: function onDate(date, isCurrentMonth) {
            var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(self.roomTypeId);
            var calendarDateAttributes = MPHB.calendarHelper.getCalendarDateAttributesFromAvailability(3, date, isCurrentMonth, roomTypeCalendarData);

            if (isCurrentMonth) {
              var checkInDate = self.form.checkInDatepicker.getDate();

              if (null !== checkInDate && MPHB.Utils.formatDateToCompare(date) === MPHB.Utils.formatDateToCompare(checkInDate)) {
                calendarDateAttributes.title += ' ' + MPHB._data.translations.checkInDate;
                calendarDateAttributes.dateClass += ' mphb-check-in-date';
              }

              if (null !== self.minStayDateAfterCheckIn && self.minStayDateAfterCheckIn.getTime() > date.getTime()) {
                calendarDateAttributes.title += '\n' + MPHB._data.translations.lessThanMinDaysStay;
                calendarDateAttributes.dateClass += ' mphb-earlier-min-date';
              }

              if (null !== self.maxStayDateAfterCheckIn && self.maxStayDateAfterCheckIn.getTime() < date.getTime()) {
                calendarDateAttributes.title += '\n' + MPHB._data.translations.moreThanMaxDaysStay;
                calendarDateAttributes.dateClass += ' mphb-later-max-date';
              }
            }

            if ((null === self.minCheckOutDateForSelection || self.minCheckOutDateForSelection.getTime() <= date.getTime()) && (null === self.maxCheckOutDateForSelection || self.maxCheckOutDateForSelection.getTime() >= date.getTime()) && !calendarDateAttributes.isUnavailableCheckOut && !calendarDateAttributes.isUnavailable) {
              calendarDateAttributes.selectable = true;
              calendarDateAttributes.dateClass += ' mphb-selectable-date';
            } else {
              calendarDateAttributes.dateClass += ' mphb-unselectable-date';
            }

            return calendarDateAttributes;
          },
          pickerClass: 'mphb-datepick-popup mphb-check-out-datepick ' + MPHB._data.settings.datepickerClass
        };
      }
    });
    /**
     *
     * @requires ./../datepicker.js
     */

    MPHB.SearchCheckInDatepicker = MPHB.Datepicker.extend({}, {
      getDatepickSettings: function getDatepickSettings() {
        var self = this;
        return {
          minDate: new Date(),
          defaultDate: this.firstAvailableCheckInDate,
          onShow: function onShow(element, instance) {
            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(instance.drawDate.getFullYear(), instance.drawDate.getMonth(), 1), MPHB._data.settings.numberOfMonthDatepicker, 0, false, false, false, function () {
              self.lock();
            }, function () {
              self.form.updateCheckOutLimitations();
              self.refresh();
              self.unlock();
            }, MPHB._data.settings.numberOfMonthDatepicker); // 	remove highlight right after calendar was shown
            // 	to avoide of date highlighting when date is not selected yet

            element.find('.datepick-highlight').removeClass('datepick-highlight');
          },
          onChangeMonthYear: function onChangeMonthYear(year, month) {
            // load calendar data for later use in onDate
            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(year, month - 1, 1), MPHB._data.settings.numberOfMonthDatepicker, 0, false, false, false, function () {
              self.lock();
            }, function () {
              self.refresh();
              self.unlock();
            }, MPHB._data.settings.numberOfMonthDatepicker);
          },
          onSelect: function onSelect(dates) {
            self.form.updateCheckOutLimitations();
          },
          onDate: function onDate(date, isCurrentMonth) {
            var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(0);
            var calendarDateAttributes = MPHB.calendarHelper.getCalendarDateAttributesFromAvailability(2, date, isCurrentMonth, roomTypeCalendarData);

            if (!isCurrentMonth || calendarDateAttributes.isPastDate || calendarDateAttributes.isUnavailable || calendarDateAttributes.isUnavailableCheckIn) {
              calendarDateAttributes.dateClass += ' mphb-unselectable-date';
            } else {
              calendarDateAttributes.selectable = true;
              calendarDateAttributes.dateClass += ' mphb-selectable-date';
            }

            return calendarDateAttributes;
          },
          pickerClass: 'mphb-datepick-popup mphb-check-in-datepick ' + MPHB._data.settings.datepickerClass
        };
      }
    });
    /**
     *
     * @requires ./../datepicker.js
     */

    MPHB.SearchCheckOutDatepicker = MPHB.Datepicker.extend({}, {
      minCheckOutDateForSelection: null,
      maxCheckOutDateForSelection: null,
      minStayDateAfterCheckIn: null,
      maxStayDateAfterCheckIn: null,

      /**
       * @param {Date} checkInDate
       */
      updateCheckOutLimitations: function updateCheckOutLimitations(checkInDate) {
        if (!checkInDate) {
          this.minCheckOutDateForSelection = null;
          this.maxCheckOutDateForSelection = null;
          this.minStayDateAfterCheckIn = null;
          this.maxStayDateAfterCheckIn = null;
          return;
        }

        var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(0);
        var result = MPHB.calendarHelper.calculateMinMaxCheckOutDateForSelection(checkInDate, roomTypeCalendarData);
        this.minCheckOutDateForSelection = result.minCheckOutDateForSelection;
        this.maxCheckOutDateForSelection = result.maxCheckOutDateForSelection;
        this.minStayDateAfterCheckIn = result.minStayDateAfterCheckIn;
        this.maxStayDateAfterCheckIn = result.maxStayDateAfterCheckIn;

        if (!this.getDate()) {
          this.setDate(this.minCheckOutDateForSelection);
        }
      },
      getDatepickSettings: function getDatepickSettings() {
        var self = this;
        return {
          minDate: new Date(),
          defaultDate: this.firstAvailableCheckInDate,
          onShow: function onShow(element, instance) {
            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(instance.drawDate.getFullYear(), instance.drawDate.getMonth(), 1), MPHB._data.settings.numberOfMonthDatepicker, 0, false, false, false, function () {
              self.lock();
            }, function () {
              self.refresh();
              self.unlock();
            }, MPHB._data.settings.numberOfMonthDatepicker); // 	remove highlight right after calendar was shown
            // 	to avoide of date highlighting when date is not selected yet

            element.find('.datepick-highlight').removeClass('datepick-highlight');
          },
          onChangeMonthYear: function onChangeMonthYear(year, month) {
            var instance = $.datepick._getInst(self.element[0]);

            var calendarDrawDate = new Date(instance.drawDate.getTime()); // load calendar data for later use in onDate

            MPHB.ajaxApiHelper.loadRoomTypeCalendarData(new Date(year, month - 1, 1), MPHB._data.settings.numberOfMonthDatepicker, 0, false, false, false, function () {
              self.lock();
            }, function () {
              self.form.updateCheckOutLimitations();

              var instance = $.datepick._getInst(self.element[0]); // we need to set draw date because it could be changed
              // after updateCheckOutLimitations() when we set calendar date


              instance.drawDate = calendarDrawDate;
              self.refresh();
              self.unlock();
            }, MPHB._data.settings.numberOfMonthDatepicker);
          },
          onDate: function onDate(date, isCurrentMonth) {
            var roomTypeCalendarData = MPHB.ajaxApiHelper.getLoadedRoomTypeCalendarData(0);
            var calendarDateAttributes = MPHB.calendarHelper.getCalendarDateAttributesFromAvailability(3, date, isCurrentMonth, roomTypeCalendarData);

            if (isCurrentMonth) {
              var checkInDate = self.form.checkInDatepicker.getDate();

              if (null !== checkInDate && MPHB.Utils.formatDateToCompare(date) === MPHB.Utils.formatDateToCompare(checkInDate)) {
                calendarDateAttributes.title += ' ' + MPHB._data.translations.checkInDate;
                calendarDateAttributes.dateClass += ' mphb-check-in-date';
              }

              if (null !== self.minStayDateAfterCheckIn && self.minStayDateAfterCheckIn.getTime() > date.getTime()) {
                calendarDateAttributes.title += '\n' + MPHB._data.translations.lessThanMinDaysStay;
                calendarDateAttributes.dateClass += ' mphb-earlier-min-date';
              }

              if (null !== self.maxStayDateAfterCheckIn && self.maxStayDateAfterCheckIn.getTime() < date.getTime()) {
                calendarDateAttributes.title += '\n' + MPHB._data.translations.moreThanMaxDaysStay;
                calendarDateAttributes.dateClass += ' mphb-later-max-date';
              }
            }

            if ((null === self.minCheckOutDateForSelection || self.minCheckOutDateForSelection.getTime() <= date.getTime()) && (null === self.maxCheckOutDateForSelection || self.maxCheckOutDateForSelection.getTime() >= date.getTime()) && !calendarDateAttributes.isUnavailableCheckOut && !calendarDateAttributes.isUnavailable) {
              calendarDateAttributes.selectable = true;
              calendarDateAttributes.dateClass += ' mphb-selectable-date';
            } else {
              calendarDateAttributes.dateClass += ' mphb-unselectable-date';
            }

            return calendarDateAttributes;
          },
          pickerClass: 'mphb-datepick-popup mphb-check-out-datepick ' + MPHB._data.settings.datepickerClass
        };
      }
    });
    MPHB.SearchForm = can.Control.extend({}, {
      checkInDatepicker: null,
      checkOutDatepicker: null,
      init: function init($formElement) {
        var firstAvailableCheckInDateYmd = $formElement.attr('data-first_available_check_in_date');

        if (!firstAvailableCheckInDateYmd) {
          firstAvailableCheckInDateYmd = $.datepick.formatDate('yyyy-mm-dd', new Date());
        }

        this.checkInDatepicker = new MPHB.SearchCheckInDatepicker($formElement.find('.mphb-datepick[id^="mphb_check_in_date"]'), {
          form: this,
          roomTypeId: 0,
          firstAvailableCheckInDateYmd: firstAvailableCheckInDateYmd
        });
        this.checkOutDatepicker = new MPHB.SearchCheckOutDatepicker($formElement.find('.mphb-datepick[id^="mphb_check_out_date"]'), {
          form: this,
          roomTypeId: 0,
          firstAvailableCheckInDateYmd: firstAvailableCheckInDateYmd
        });
      },
      updateCheckOutLimitations: function updateCheckOutLimitations() {
        this.checkOutDatepicker.updateCheckOutLimitations(this.checkInDatepicker.getDate());
      }
    });
    MPHB.RoomBookSection = can.Control.extend({}, {
      roomTypeId: null,
      roomTitle: '',
      roomPrice: 0,
      quantitySelect: null,
      bookButton: null,
      confirmButton: null,
      removeButton: null,
      messageHolder: null,
      messageWrapper: null,
      form: null,
      init: function init(el, args) {
        this.reservationCart = args.reservationCart;
        this.roomTypeId = parseInt(el.attr('data-room-type-id'));
        this.roomTitle = el.attr('data-room-type-title');
        this.roomPrice = parseFloat(el.attr('data-room-price'));
        this.confirmButton = el.find('.mphb-confirm-reservation');
        this.quantitySelect = el.find('.mphb-rooms-quantity');
        this.messageWrapper = el.find('.mphb-rooms-reservation-message-wrapper');
        this.messageHolder = el.find('.mphb-rooms-reservation-message');
      },

      /**
       *
       * @returns {int}
       */
      getRoomTypeId: function getRoomTypeId() {
        return this.roomTypeId;
      },

      /**
       *
       * @returns {Number}
       */
      getPrice: function getPrice() {
        return this.roomPrice;
      },
      '.mphb-book-button click': function mphbBookButtonClick(button, e) {
        e.preventDefault();
        e.stopPropagation();
        var quantity = this.quantitySelect.length ? parseInt(this.quantitySelect.val()) : 1;
        this.reservationCart.addToCart(this.roomTypeId, quantity);

        if (!MPHB._data.settings.isDirectBooking) {
          // Add message "N x ... has/have been added to your reservation."
          var messagePattern = 1 == quantity ? MPHB._data.translations.roomsAddedToReservation_singular : MPHB._data.translations.roomsAddedToReservation_plural;
          var message = messagePattern.replace('%1$d', quantity).replace('%2$s', this.roomTitle);
          this.messageHolder.html(message); // Show "N x ... has/have been added to your reservation." message
          // Show "Remove" button
          // Show "Confirm Reservation" button

          this.element.addClass('mphb-rooms-added');
        } else {
          button.prop('disabled', true); // Go to the Checkout immediately

          this.reservationCart.confirmReservation();
        }
      },
      '.mphb-remove-from-reservation click': function mphbRemoveFromReservationClick(el, e) {
        e.preventDefault();
        e.stopPropagation();
        this.reservationCart.removeFromCart(this.roomTypeId);
        this.messageHolder.empty();
        this.element.removeClass('mphb-rooms-added');
      },
      '.mphb-confirm-reservation click': function mphbConfirmReservationClick(el, e) {
        e.preventDefault();
        e.stopPropagation();
        this.reservationCart.confirmReservation();
      }
    });
    /**
     *
     * @requires ./room-book-section.js
     */

    MPHB.ReservationCart = can.Control.extend({}, {
      cartForm: null,
      cartDetails: null,
      roomBookSections: {},
      cartContents: {},
      init: function init(el, args) {
        this.cartForm = el.find('#mphb-reservation-cart');
        this.cartDetails = el.find('.mphb-reservation-details');
        this.initRoomBookSections(el.find('.mphb-reserve-room-section'));
      },
      initRoomBookSections: function initRoomBookSections(sections) {
        var self = this;
        var bookSection;
        $.each(sections, function (index, roomSection) {
          bookSection = new MPHB.RoomBookSection($(roomSection), {
            reservationCart: self
          });
          self.roomBookSections[bookSection.getRoomTypeId()] = bookSection;
        });
      },
      addToCart: function addToCart(roomTypeId, quantity) {
        this.cartContents[roomTypeId] = quantity;
        this.updateCartView();
        this.updateCartInputs();
      },
      removeFromCart: function removeFromCart(roomTypeId) {
        delete this.cartContents[roomTypeId];
        this.updateCartView();
        this.updateCartInputs();
      },
      calcRoomsInCart: function calcRoomsInCart() {
        var count = 0;
        $.each(this.cartContents, function (roomTypeId, quantity) {
          count += quantity;
        });
        return count;
      },
      calcTotalPrice: function calcTotalPrice() {
        var total = 0;
        var price = 0;
        var self = this;
        $.each(this.cartContents, function (roomTypeId, quantity) {
          price = self.roomBookSections[roomTypeId].getPrice();
          total += price * quantity;
        });
        return total;
      },
      updateCartView: function updateCartView() {
        if (!$.isEmptyObject(this.cartContents)) {
          var roomsCount = this.calcRoomsInCart();
          var messageTemplate = 1 == roomsCount ? MPHB._data.translations.countRoomsSelected_singular : MPHB._data.translations.countRoomsSelected_plural;
          var cartMessage = messageTemplate.replace('%s', roomsCount);
          this.cartDetails.find('.mphb-cart-message').html(cartMessage);
          var total = this.calcTotalPrice();
          var totalMessage = MPHB.format_price(total, {
            'trim_zeros': true
          });
          this.cartDetails.find('.mphb-cart-total-price>.mphb-cart-total-price-value').html(totalMessage);
          this.cartForm.removeClass('mphb-empty-cart');
        } else {
          this.cartForm.addClass('mphb-empty-cart');
        }
      },
      updateCartInputs: function updateCartInputs() {
        // empty inputs
        this.cartForm.find('[name^="mphb_rooms_details"]').remove();
        var self = this;
        $.each(this.cartContents, function (roomTypeId, quantity) {
          var input = $('<input />', {
            name: 'mphb_rooms_details[' + roomTypeId + ']',
            type: 'hidden',
            value: quantity
          });
          self.cartForm.prepend(input);
        });
      },
      confirmReservation: function confirmReservation() {
        this.cartForm.submit();
      }
    });
    /**
     * @requires ../stripe-gateway.js
     *
     * @since 3.6.0
     */

    MPHB.StripeGateway.PaymentMethods = can.Construct.extend({}, {
      listAll: ['card', 'bancontact', 'ideal', 'giropay', 'sepa_debit', 'sofort'],
      listEnabled: ['card'],
      paymentMethods: {},
      currentPayment: 'card',
      currentCountry: '',
      inputs: null,
      // input[name="stripe_payment_method"] elements
      isMounted: false,
      init: function init(enabledPayments, defaultCountry) {
        this.listEnabled = enabledPayments.slice(0); // Clone array

        this.initPayments(); // Change the country only when paymentMethods data are fully ready

        this.selectCountry(defaultCountry);
      },
      initPayments: function initPayments() {
        var self = this;
        this.forEach(function (payment) {
          self.paymentMethods[payment] = {
            isEnabled: self.listEnabled.indexOf(payment) >= 0,
            nav: null,
            // .mphb-stripe-payment-method.%payment% element
            fields: null // .mphb-stripe-payment-fields.%payment% element

          };
        });
      },
      selectPayment: function selectPayment(payment) {
        if (payment == this.currentPayment || !this.paymentMethods.hasOwnProperty(payment)) {
          return;
        }

        this.togglePayment(this.currentPayment, false);
        this.togglePayment(payment, true);
        this.currentPayment = payment;
      },
      togglePayment: function togglePayment(payment, enable) {
        if (this.isMounted) {
          this.paymentMethods[payment].nav.toggleClass('active', enable);
          this.paymentMethods[payment].fields.toggleClass('mphb-hide', !enable);
        }
      },
      selectCountry: function selectCountry(country) {
        if (this.currentCountry == country) {
          return;
        }

        this.currentCountry = country;
        this.currentPayment = 'card'; // Reset selected payment method

        this.showRelevantMethods();
      },
      showRelevantMethods: function showRelevantMethods() {
        if (!this.isMounted) {
          return;
        }

        var selectedPayment = this.currentPayment;
        this.forEach(function (payment, paymentMethod) {
          // Show only fields of the selected payment method
          paymentMethod.fields.toggleClass('mphb-hide', payment != selectedPayment);
        }); // Select proper radio button

        this.inputs.val([selectedPayment]);
      },
      mount: function mount(section) {
        this.forEach(function (payment, paymentMethod) {
          paymentMethod.nav = section.find('.mphb-stripe-payment-method.' + payment);
          paymentMethod.fields = section.find('.mphb-stripe-payment-fields.' + payment);
        });
        this.inputs = section.find('input[name="stripe_payment_method"]');
        this.isMounted = true;
      },
      unmount: function unmount() {
        this.forEach(function (_, paymentMethod) {
          paymentMethod.nav = null;
          paymentMethod.fields = null;
        });
        this.inputs = null;
        this.isMounted = false;
      },
      forEach: function forEach(callback) {
        var self = this;
        this.listAll.forEach(function (payment) {
          // callback(string, Object, MPHB.StripeGateway.PaymentMethods)
          callback(payment, self.paymentMethods[payment], self);
        });
      },
      isEnabled: function isEnabled(payment) {
        return this.paymentMethods[payment].isEnabled;
      },
      onlyCardEnabled: function onlyCardEnabled() {
        return this.listEnabled.length == 1 && this.paymentMethods.card.isEnabled;
      },
      isSelected: function isSelected(payment) {
        return payment == this.currentPayment;
      }
    });

    if (MPHB._data.page.isCheckoutPage) {
      new MPHB.CheckoutForm($('.mphb_sc_checkout-form'));
    } else if (MPHB._data.page.isCreateBookingPage) {
      new MPHB.CheckoutForm($('.mphb_cb_checkout_form'));
    }

    if (MPHB._data.page.isSearchResultsPage) {
      new MPHB.ReservationCart($('.mphb_sc_search_results-wrapper'));
    }

    var calendars = $('.mphb-calendar.mphb-datepick');
    $.each(calendars, function (index, calendarEl) {
      new MPHB.RoomTypeCalendar($(calendarEl));
    });
    var reservationForms = $('.mphb-booking-form');
    $.each(reservationForms, function (index, formEl) {
      new MPHB.ReservationForm($(formEl));
    });
    var searchForms = $('form.mphb_sc_search-form, form.mphb_widget_search-form, form.mphb_cb_search_form');
    $.each(searchForms, function (index, formEl) {
      new MPHB.SearchForm($(formEl));
    });
    var flexsliderGalleries = $('.mphb-flexslider-gallery-wrapper');
    $.each(flexsliderGalleries, function (index, flexsliderGallery) {
      new MPHB.FlexsliderGallery(flexsliderGallery);
    });
    var termsAndConditions = $('.mphb-checkout-terms-wrapper');

    if (termsAndConditions.length > 0) {
      new MPHB.TermsSwitcher(termsAndConditions);
    } // Fix for kbwood/datepick (function show() -> $.ui.version.substring(2))


    if ($.ui == undefined) {
      $.ui = {};
    }

    if ($.ui.version == undefined) {
      $.ui.version = '1.5-';
    }
  });
})(jQuery);