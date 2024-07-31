<!--================ Flexible Grid Column ================-->
<div id="${unique_id}" class="milenia-aligner ${container_classes}" data-compose-mode-col-classes="${container_classes}" data-css-row="${data_row_css}">
    <div class="milenia-aligner-outer">
        <div class="milenia-aligner-inner">
            ${content}
        </div>
    </div>
    <div class="${colorizer_classes}" ${data_bg_color_attribute} ${data_bg_image_attribute} ${data_bg_image_opacity_attribute}></div>
    <script>
        (function($){
            'use strict';

            if(!$) return;

            $(function() {
                var $container = $('#${unique_id}'), script, $colorizer;

                if(!$container.length) return;

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

                if($.Milenia && $.Milenia.helpers && $.Milenia.helpers.composerFlexibleGrid) {
                    $.Milenia.helpers.composerFlexibleGrid.init($container);
                }

                script = $container.find('script');
                $colorizer = $container.find('[class*="milenia-colorizer-"]');

                if(script.length) script.not(script.first()).remove();
                if($colorizer.length) {
                    $colorizer.not($colorizer.first()).remove();
                    if(window.Milenia && window.Milenia.helpers && window.Milenia.helpers.Colorizer) window.Milenia.helpers.Colorizer.init($colorizer);
                }
            });
        })(window.jQuery);
    </script>
</div>
<!--================ End of Flexible Grid Column  ================-->
