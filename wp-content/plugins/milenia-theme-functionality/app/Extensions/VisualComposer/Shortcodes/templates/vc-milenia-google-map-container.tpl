<!--================ Google Map ================-->
<script>
	(function($){
		'use strict';
		if(!$) return;

		$(function(){
			var $container = $('#${unique_id}');
			if( !$container.length || !window.Milenia ) return;

			if(window.appear) {
                appear({
                    elements: function(){
                        return document.querySelectorAll('#${unique_id}');
                    },
                    appear: function(element) {
                        var $el = $(element);
                        $el.addClass('milenia-visible').addClass($el.data('animation'));
                    },
                    reappear: false,
                    bounds: -200
                });
            }

			// Initialize googleMaps module after updating a container on the page
			if( window.Milenia.modules && window.Milenia.modules.googleMaps ) {
				window.Milenia.modules.googleMaps.init( $container.filter( function(index, element){
					return !$(element).data('Maplace');
				} ) );
			}
		});
	})(window.jQuery);
</script>
 <div id="${unique_id}"
      class="${container_classes}"
      data-locations='${locations}'
      data-map-options='${map_options}'
      data-animation="${css_animation}"
	  data-css-row="${data_row_css}"></div>
<!--================ End of Google Map ================-->
