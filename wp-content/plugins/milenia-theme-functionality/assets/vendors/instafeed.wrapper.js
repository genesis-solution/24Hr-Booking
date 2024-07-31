/**
 *  @fileOverview Describes the InstafeedWrapper module. An easy to work with InstafeedJS from HTML.
 *
 *  @author       Paul K.
 *  @version      0.0.1
 *
 *  @requires     Instafeed:@link{http://instafeedjs.com/}
 */

var InstafeedWrapper = (function(Instafeed) {
    'use strict';

    /**
     * Local cache of initialized instagram feeds on the page.
     * @type {Object}
     */
    var _instances = {};

    /**
     * Presetted OAuth parameters of the users.
     * @type {Object}
     */
    var _userSecureOptions = {};

    /**
     * The InstafeedWrapper constructor.
     *
     * @class
     * @constructor
     *
     * @property {HTMLElement} container
     * @property {Number} ID
     * @property {Object} options
     * @property {Instafeed} instance
     * @property {HTMLElement} loadMoreControl
     */
    function InstafeedWrapper(container, options) {
        this.container = container;
        this.ID = this.container.getAttribute('id');

        this.instance = _instances[this.ID] = new Instafeed(this._overrideOptions(options));
        this.instance.run();

        _addClass(this.container, 'ifw-container-loading');

        this._initLoadMoreControl();
    };

    /**
     * Overrides common options by dataset of the container.
     *
     * @param {Object} options
     * @private
     * @returns {Object}
     */
    InstafeedWrapper.prototype._overrideOptions = function (options) {
        var dataStringMap = this.container.dataset;
        options.target = this.ID;
        options = this._admixCallbacks(options);

        for(var property in dataStringMap) {
            if(property == 'user') {
                if(dataStringMap[property] in _userSecureOptions) {
                    for(var userOption in _userSecureOptions[dataStringMap[property]]) {
                        options[userOption] = _userSecureOptions[dataStringMap[property]][userOption];
                    }
                }
                else {
                    throw new Error('The secure options for user ' +dataStringMap[property]+ ' isn\'t set.');
                }

                continue;
            }

            options[property] = dataStringMap[property];
        }

        return options;
    };

    /**
     * Adds some core functionality to the callbacks.
     *
     * @param {Object} options
     * @private
     * @returns {Object}
     */
    InstafeedWrapper.prototype._admixCallbacks = function (options) {
        var self = this,
            userAfterCallback = options.after;

        options.after = function() {
            if(!self._initialized) {
                _removeClass(self.container, 'ifw-container-loading');
                self._initialized = true;
            }

            if(self.loadMoreControl && self.loadMoreControl.dataset.ifwLoadingContent && self.loadMoreControl.dataset.ifwBaseText) {
                self.loadMoreControl.textContent = self.loadMoreControl.dataset.ifwBaseText;
            }

            if(!this.hasNext() && self.loadMoreControl) {
                self.loadMoreControl.setAttribute('disabled', 'disabled');
                _addClass(self.loadMoreControl, 'ifw-disabled');

                if(self.loadMoreControl.dataset.ifwDisabledContent) {
                    self.loadMoreControl.textContent = self.loadMoreControl.dataset.ifwDisabledContent;
                }
            }

            if(userAfterCallback) userAfterCallback.apply(this, Array.prototype.slice(arguments, 0));
        };

        return options;
    };

    /**
     * Initializes a button for dynamically loading images.
     *
     * @private
     * @returns {InstafeedWrapper}
     */
    InstafeedWrapper.prototype._initLoadMoreControl = function () {
        var loadMoreControlId = this.container.dataset.loadMoreControl,
            self = this;

        if(!loadMoreControlId) return this;

        this.loadMoreControl = document.getElementById(loadMoreControlId);

        if(!this.loadMoreControl) return this;

        this.loadMoreControl.dataset.ifwBaseText = this.loadMoreControl.textContent;

        this.loadMoreControl.addEventListener('click', function(event){

            if(_hasClass(this, 'ifw-disabled')) {
                event.preventDefault();
                return false;
            }

            self.instance.next();

            if(this.dataset.ifwLoadingContent) {
                this.textContent = this.dataset.ifwLoadingContent;
            }

            event.preventDefault();
        }, false);

        return this;
    };

    return {
        /**
         * Initialization of the InstafeedWrapper.
         *
         * @param {HTMLCollection} containers
         * @param {Object} options
         * @public
         */
        init: function(containers, options) {
            options = options || {};

            Array.prototype.forEach.call(containers, function(container, index, array) {
                if(container.getAttribute('id') in _instances) {
                    throw new Error('One of the specified containers duplicates an existing ID.');
                }

                if(!container.hasAttribute('id')) {
                    throw new Error('One of the specified containers doesn\'t have a unique ID.');
                }

                new InstafeedWrapper(container, options);
            });
        },

        /**
         * Sets up users OAuth secure data.
         *
         * @param {Object} users
         * @public
         */
        setUsersSecureOptions: function(users) {
            for(var user in users) {
                if(user in _userSecureOptions) {
                    throw new Error('The secure options for user ' +user+ ' is already set.');
                }

                if(!('userId' in users[user]) || !('accessToken' in users[user]) || !('clientId' in users[user])) {
                    throw new Error('You must specify "userId", "accessToken" and "clientId" option for each user.');
                }

                _userSecureOptions[user] = users[user];
            }
        }
    };

    /**
     * Adds class to the element.
     *
     * @param {HTMLElement} element element to which the class will be added
     * @param {String} className class which will be added to the element
     * @returns {HTMLElement} element with added class
     */
    function _addClass(element, className) {
        element.className = element.className + ' ' + className;

        return element;
    };

    /**
     * Removes class from the element.
     *
     * @param {HTMLElement} element element from which the class will be removed
     * @param {String} className class which will be removed from the element
     * @returns {HTMLElement} element without specified class
     */
    function _removeClass(element, className) {
        element.className = element.className.replace(className, '');

        return element;
    };

    /**
     * Returns true when the element has specified class.
     *
     * @param {HTMLElement} element element from which the class will be removed
     * @param {String} className class which will be removed from the element
     * @returns {Boolean}
     */
    function _hasClass(element, className) {
        return element.className.indexOf(className) != -1;
    };
})(window.Instafeed);
