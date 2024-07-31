(function (window) {
	'use strict';

	window.vc.htmlHelpers = {
		fixUnclosedTags: function ( string ) {
			// Replace opening < with an entity &#60; to avoid editor breaking
			var regex = /<([^>]+)$/g;
			return string.replace(regex, '&#60;');
		}
	};
})(window);
