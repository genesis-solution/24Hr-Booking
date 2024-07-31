/**
 * Simple tooltip jQuery plugin.
 * @author Monkeysan
 * @version 1.0.0
 */
;(function($){
  'use strict';

  /**
   * Specifies whether the necessary global events has been registered.
   * @private {boolean}
   */
  var _singleEventsBinded = false;

  /**
   * Contains time out id for resizing window mechanism.
   * @private {Number}
   */
  var _resizeWindowTimeOutId = null;

  /**
   * Contains jQuery object of the window object.
   * @private {jQuery}
   */
  var _$w = $(window);

  /**
   * Contains jQuery object of the document.body element.
   * @private {jQuery}
   */
  var _$body = $('body');

  /**
   * Contains private methods.
   * @private {Object}
   */
  var _privates = {};

  /**
   * Contains all initialized tooltips on the page.
   * @private {Array}
   */
  _privates.tooltips = [];

  /**
   * Contains amount of all tooltips on the page.
   * @private {Number}
   */
  _privates.count = 0;

  /**
   * Specifies whether the browser supports css animation.
   * @private {boolean}
   */
  _privates.CSSAnimationSupported = _isAnimationSupported();

  /**
   * Creates a tooltip element.
   * @private
   */
  _privates.prepareTooltip = function () {
    var _self = this,
        tooltipsClasses = ['tooltip', 'tooltip-' + _self.config.tooltipPosition, 'tooltip-skin-' + _self.config.skin];

    this.uniqueId = _privates.getUniqueId();

    if( this.needsCSSAnimation ) tooltipsClasses.push('tooltip-hidden');
    this.$element.attr('aria-describedby', _self.uniqueId);

    this.$tooltip = $('<span></span>', {
      id: _self.uniqueId,
      class: _privates.getPrefixedClass.call(_self, tooltipsClasses),
      text: _self.tooltipText,
      'aria-hidden': 'true',
      role: 'tooltip'
    }).css( 'width', _self.config.width ).appendTo( _$body );

    if( this.config.width == 'auto' ) this.$tooltip.css('white-space', 'nowrap');

    if( this.needsCSSAnimation ) {
      this.$tooltip.addClass('animated').on("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(e) {
        var $this = $(this);

        if( $this.hasClass(_self.config.animationOut) ) {
          $this
            .addClass(_privates.getPrefixedClass('tooltip-hidden'))
            .removeClass(_self.config.animationOut);
          _self.$element.trigger('hide.MonkeysanTooltip');
        }
        e.stopPropagation();
      });
    }
    else {
      this.$tooltip.hide();
    }

    _privates.tooltips.push(this);
  };

  /**
   * Registers necessary events on the element.
   * @private
   */
  _privates.bindEvents = function() {
    var _self = this;

    this.$element.on('mouseenter.MonkeysanTooltip', function(e) {
      if( !_self._prepared ) {
        _privates.prepareTooltip.call(_self);
        _self._prepared = true;
      }

      _self.show();
  }).on('mouseleave.MonkeysanTooltip', function(e) {
      if( !_self._prepared ) return;
      _self.hide();
    });
  };

  /**
   * Returns prefixed class.
   * @param {string} className An unprefixed class.
   * @param {boolean} dotted A dot before the class.
   * @return {string}
   */
  _privates.getPrefixedClass = function(className, dotted) {
    if($.isArray(className)) {
      return $.map(className, function(element){
        return (dotted ? '.' : '') + MonkeysanTooltip._cssPrefix + element;
      }).join(' ');
    }
    return (dotted ? '.' : '') + MonkeysanTooltip._cssPrefix + className;
  };

  /**
   * Returns unique id for the new tooltip element.
   * @private
   * @return {string}
   */
  _privates.getUniqueId = function() {
    var idName = _privates.getPrefixedClass("tooltip-" + _privates.count);

    for(var i = 0; i < _privates.tooltips.length; i++) {
      if( _privates.tooltips[i].uniqueId === idName ) {
        _privates.count++;
        return _privates.getUniqueId();
      }
    }

    return idName;
  };

  /**
   * Sets up the correct position of the tooltip.
   * @private
   */
  _privates.setUpCorrectPosition = function() {
    if( !this.$tooltip.length ) return;
    this.$tooltip.removeClass( _privates.getPrefixedClass( 'tooltip-position-reversed' ) );
    _privates['setUpCorrectPosition' + this.config.tooltipPosition.toUpperCase()].call(this);
  };

  /**
   * Sets up the correct coords for the tooltip aligned by top.
   * @param {boolean} reverse Specifies whether reverse the position of the tooltip.
   * @private
   */
  _privates.setUpCorrectPositionTOP = function(reverse) {
    var _self = this,
        elCoords = _self.$element.offset();

    this.$tooltip.css({
      'bottom': 'auto',
      'top': elCoords.top - _self.$tooltip.outerHeight(),
      'left': elCoords.left + _self.$element.outerWidth() / 2,
      'margin-left': _self.$tooltip.outerWidth() / -2
    });

    if(!reverse) _privates.correctPositionByXYAxis.call(this);
  };

  /**
   * Sets up the correct coords for the tooltip aligned by bottom.
   * @param {boolean} reverse Specifies whether reverse the position of the tooltip.
   * @private
   */
  _privates.setUpCorrectPositionBOTTOM = function(reverse) {
    var _self = this,
        elCoords = _self.$element.offset();

    this.$tooltip.css({
      'bottom': 'auto',
      'top': elCoords.top + _self.$element.outerHeight(),
      'left': elCoords.left + _self.$element.outerWidth() / 2,
      'margin-left': _self.$tooltip.outerWidth() / -2
    });

    if(!reverse) _privates.correctPositionByXYAxis.call(this);
  };

  /**
   * Sets up the correct coords for the tooltip aligned by right.
   * @private
   */
  _privates.setUpCorrectPositionRIGHT = function() {
    var _self = this,
        elCoords = _self.$element.offset();

    this.$tooltip.css({
      'bottom': 'auto',
      'top': elCoords.top + _self.$element.outerHeight() / 2,
      'left': elCoords.left + _self.$element.outerWidth(),
      'right': 'auto',
      'margin-top': _self.$tooltip.outerHeight() / - 2
    });

    _privates.correctPositionByXYAxis.call(this);
  };

  /**
   * Sets up the correct coords for the tooltip aligned by left.
   * @private
   */
  _privates.setUpCorrectPositionLEFT = function() {
    var _self = this,
        elCoords = _self.$element.offset();

    this.$tooltip.css({
      'bottom': 'auto',
      'top': elCoords.top + _self.$element.outerHeight() / 2,
      'left': elCoords.left - _self.$tooltip.outerWidth(),
      'right': 'auto',
      'margin-top': _self.$tooltip.outerHeight() / - 2
    });

    _privates.correctPositionByXYAxis.call(this);
  };

  /**
   * Corrects tooltip position in the edge cases by x and y axis.
   * @private
   */
  _privates.correctPositionByXYAxis = function() {
    var tooltipCoords = this.$tooltip.offset(),
        bodyCoords = _$body.offset();

    // x axis
    if( ( tooltipCoords.left + this.$tooltip.outerWidth() > _$body.outerWidth() + bodyCoords.left ) && this.config.tooltipPosition === 'right' ) {
      _privates.setUpCorrectPositionLEFT.call(this, true);
      this.$tooltip.addClass( _privates.getPrefixedClass( 'tooltip-position-reversed' ) );

      tooltipCoords = this.$tooltip.offset();

      if( tooltipCoords.left < 0 ) {
        this.$tooltip.css( 'left', parseInt(this.$tooltip.css('left'), 10) + Math.abs(tooltipCoords.left) );
      }
    }
    else if(tooltipCoords.left < 0 && this.config.tooltipPosition === 'left') {
        _privates.setUpCorrectPositionRIGHT.call(this, true);
        this.$tooltip.addClass( _privates.getPrefixedClass( 'tooltip-position-reversed' ) );

        tooltipCoords = this.$tooltip.offset();

        if( tooltipCoords.left + this.$tooltip.outerWidth() > _$body.outerWidth() + bodyCoords.left ) {
          this.$tooltip.css('left', parseInt(this.$tooltip.css('left'), 10) - ( (tooltipCoords.left + this.$tooltip.outerWidth()) - ( _$body.outerWidth() + bodyCoords.left) ));
        }
    }

    // y axis
    if( tooltipCoords.top < _$w.scrollTop() && this.config.tooltipPosition === 'top' ) {
      _privates.setUpCorrectPositionBOTTOM.call(this, true);
      this.$tooltip.addClass( _privates.getPrefixedClass( 'tooltip-position-reversed' ) );
    }
    else if( ( (tooltipCoords.top + this.$tooltip.outerHeight()) > _$w.scrollTop() + _$w.height() ) && this.config.tooltipPosition === 'bottom' ) {
      _privates.setUpCorrectPositionTOP.call(this, true);
      this.$tooltip.addClass( _privates.getPrefixedClass( 'tooltip-position-reversed' ) );
    }

    if( this.config.tooltipPosition === 'top' || this.config.tooltipPosition === 'bottom' ) {
        if( tooltipCoords.left + this.$tooltip.outerWidth() > _$body.outerWidth() + bodyCoords.left ) {
          this.$tooltip.css('left', parseInt(this.$tooltip.css('left'), 10) - ( (tooltipCoords.left + this.$tooltip.outerWidth()) - ( _$body.outerWidth() + bodyCoords.left) ));
        }
        else if( tooltipCoords.left < 0 ) {
          this.$tooltip.css( 'left', parseInt(this.$tooltip.css('left'), 10) + Math.abs(tooltipCoords.left) );
        }
    }

  };

  /**
   * Class that describes the tooltip entity.
   * @param {jQuery} $element A jQuery element that have a tooltip.
   * @param {Object} config A configuration object.
   * @constructor
   */
  function MonkeysanTooltip($element, config) {
    this.$element = $element;
    this.config = config;
    this._prepared = false;
    _privates.bindEvents.call(this);
  };

  /**
   * Contains default configuration of the tooltip.
   * @public {Object}
   */
  MonkeysanTooltip.defaults = {
    animationIn: false,
    animationOut: false,
    tooltipPosition: 'top',
    skin: 'default',
    width: 'auto',
    jQueryAnimationEasing: 'linear',
    jQueryAnimationDuration: 500
  };

  /**
   * Contains a css prefix for all necessary tooltip classes.
   * In case you change this property you should replace all
   * prefixes in the css file.
   * @private {string}
   */
  MonkeysanTooltip._cssPrefix = 'monkeysan-';

  /**
   * Contains a delay between show and hide events.
   * @private {Number}
   */
  MonkeysanTooltip._timeOutDelay = 100;

  Object.defineProperties(MonkeysanTooltip.prototype, {
    tooltipText: {
      get: function() {
        return this.$element.data('tooltip');
      },
      set: function(value) {
        this.$element.data('tooltip', value);
      }
    },
    needsCSSAnimation: {
      get: function() {
        return _privates.CSSAnimationSupported && this.config.animationIn && this.config.animationOut;
      }
    },
    isVisible: {
      get: function() {
        return this.needsCSSAnimation ? !this.$tooltip.hasClass( _privates.getPrefixedClass('tooltip-hidden') ) : this.$tooltip.is(':visible');
      }
    }
  });

  /**
   * Displays the tooltip.
   * @return {MonkeysanTooltip}
   */
  MonkeysanTooltip.prototype.show = function() {
    var _self = this;

    if(this._hideTimeOutId) clearTimeout(this._hideTimeOutId);

    _privates.setUpCorrectPosition.call(this);

    if( this.needsCSSAnimation ) {
      this.$tooltip
        .removeClass( this.config.animationOut + " " + _privates.getPrefixedClass('tooltip-hidden'))
        .addClass( this.config.animationIn )
        .attr('aria-hidden', 'false');

        this.$element.trigger('show.MonkeysanTooltip');
    }
    else {
      this.$tooltip.stop().fadeIn({
        easing: _self.config.jQueryAnimationEasing,
        duration: _self.config.jQueryAnimationDuration,
        compltete: function() {
          _self.$element.trigger('show.MonkeysanTooltip');
        }
      });
    }
    return this;
  };

  /**
   * Hides the tooltip.
   * @return {MonkeysanTooltip}
   */
  MonkeysanTooltip.prototype.hide = function() {
    var _self = this;

    this._hideTimeOutId = setTimeout(function(){
      if( _self.needsCSSAnimation ) {
        _self.$tooltip
            .removeClass( _self.config.animationIn )
            .addClass( _self.config.animationOut )
            .attr('aria-hidden', 'true');
      }
      else {
        _self.$tooltip.stop().fadeOut({
          easing: _self.config.jQueryAnimationEasing,
          duration: _self.config.jQueryAnimationDuration,
          compltete: function() {
            _self.$element.trigger('hide.MonkeysanTooltip');
          }
        });
      }
  }, MonkeysanTooltip._timeOutDelay);

    return this;
  };

  /**
   * Registers MonkeysanTooltip jQuery method.
   * @param {Object} config
   * @return {jQuery}
   */
  $.fn.MonkeysanTooltip = function(config) {
    config = $.extend(true, {}, MonkeysanTooltip.defaults, $.isPlainObject(config) ? config : {});

    if(!_singleEventsBinded) {
      _$w.on('scroll.MonkeysanTooltip', function(e) {
        if( !_privates.tooltips.length ) return;

        $.each(_privates.tooltips, function(index, object){
          if(object.isVisible){
             _privates.setUpCorrectPosition.call(object);
           }
        });
    }).on('resize.MonkeysanTooltip', function(e) {
        if( !_privates.tooltips.length ) return;
        if( _resizeWindowTimeOutId ) clearTimeout( _resizeWindowTimeOutId );

        _resizeWindowTimeOutId = setTimeout(function(){
          $.each(_privates.tooltips, function(index, object){
            _privates.setUpCorrectPosition.call(object);
          });
      }, MonkeysanTooltip._timeOutDelay);
      });
    }

    return this.each(function(index, element){
      var $element = $(element);
          config = $.extend(true, {}, config, $element.data('tooltip-config'));

      if(!$element.data('MonkeysanTooltip')) {
        $element.data('MonkeysanTooltip', new MonkeysanTooltip($element, config));
      }
    });
  };

  /**
   * Helper function for checking if the browser supports css animations.
   * @private
   * @return {boolean}
   */
  function _isAnimationSupported() {
    var DOMPrefixes = ['Webkit', 'Moz', 'O', 'ms', 'Khtml'],
        elem = document.createElement('div');

    if(elem.style.animationName !== undefined) return true;

    for(var i = 0; i < DOMPrefixes.length; i++) {
      if( elem.style[DOMPrefixes[i] + 'AnimationName'] !== undefined ) return true;
    }

    return false;
  };

})(window.jQuery);
