;(function($){
    'use strict';

    var WidgetAreaSettings = {
        _collection: $(),

        init: function($collection) {
            var self = this;

            if(!$collection || !$collection.length || !window.Handlebars) return;

            $collection.each(function(index, container) {
                var $container = $(container);

                if(self._collection.filter($container).length) return;

                self.initContainer($container);
                self._collection = self._collection.add($container);
            });
        },

        initContainer: function($container) {
            var $template,
                $configurations,
                $formItems;

            $container.on('click.ReduxWidgetAreaSettings', '.redux-widgets-area-settings-add-btn', function(event){
                $template = $container.find('.redux-widgets-area-settings-configuration-template');
                $configurations = $container.find('.redux-widgets-area-settings-configurations');

                if($template.length && $configurations.length) {
                    $configurations.append(Handlebars.compile($template.html())({
                        counter: $container.data('counter')
                    }));
                    $formItems = $container.find('.milenia-styled-select, .milenia-styled-input');
                    $container.data('counter', $container.data('counter')+1);

                    if($formItems && $.fn.styler) {
                        $formItems.styler();
                    }
                }
            }).on('click.ReduxWidgetAreaSettings', '.redux-widgets-area-settings-remove-btn', function(event){
                $(this).closest('.redux-widgets-area-settings-configuration').remove();
            });
        }
    };

    $(function() {
        WidgetAreaSettings.init($('.redux-widgets-area-settings-container'));
    });
})(window.jQuery);
