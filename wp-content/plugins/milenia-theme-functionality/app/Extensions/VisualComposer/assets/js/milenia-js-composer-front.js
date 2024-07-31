;(function($){
    'use strict';

    if(!$) return;
    if(!$.Milenia) $.Milenia = {};
    if(!$.Milenia.helpers) $.Milenia.helpers = {};

    if(!$.Milenia.helpers.VCFrontEndEditor) $.Milenia.helpers.VCFrontEndEditor = {};

    /**
     * The 'fullPageReBuilderListener' helper
     */
    $.Milenia.helpers.fullPageReBuilderListener = {};

    $.Milenia.helpers.fullPageReBuilderListener.init = function(selector) {
        if(!selector) return;
        this.$runtimeElems = $(selector);
        this.selector = selector;
        this.$w = $(window);

        if(!this.$runtimeElems.length) return;

        this.intervalID = setInterval(this.checkElems.bind(this), 100);
    };

    $.Milenia.helpers.fullPageReBuilderListener.checkElems = function() {
        var $nowExistingElements = $(this.selector);

        if($nowExistingElements.length != this.$runtimeElems.length) {
            this.$w.trigger('vc_reload', {fromMileniaHelper: true});
            this.$runtimeElems = $(this.selector);
        }
    };


    /**
     * The 'composerRTLFix' helper
     */
    $.Milenia.helpers.composerRTLFix = {};

    $.Milenia.helpers.composerRTLFix._collection = $();

    $.Milenia.helpers.composerRTLFix.init = function(collection) {
        var _self = this;

        if(!collection || !collection.length) return;

        this.$w = $(window);

        if(!this.bindedOnceEvents) this._bindOnceEvents();

        collection.each(function(index, row){
            var $row = $(row);
            if(_self._collection.filter($row).length) return;

            _self._collection = _self._collection.add($row);
        });

        this.$w.trigger('resize.MileniacomposerRTLFix');

        return collection;
    };

    $.Milenia.helpers.composerRTLFix._bindOnceEvents = function() {
        var _self = this;

        this.$w.on('resize.MileniacomposerRTLFix', function() {
            if(_self.resizeTimeOutId) clearTimeout(_self.resizeTimeOutId);

            _self.resizeTimeOutId = setTimeout(function(){
                _self.setOffset();
            }, 100);
        });
    };

    $.Milenia.helpers.composerRTLFix.setOffset = function() {
        return this._collection.each(function(index, row){
            var $row = $(row),
                left = parseInt($row.css('left'), 10);

            $row.css({
                'right': left,
                'left': 'auto'
            });

        });
    };

    $.Milenia.helpers.composerFlexibleGrid = {};

    $.Milenia.helpers.composerFlexibleGrid.init = function($columns) {
        if($columns.length) {
            $columns.each(function(index, column){
                var $column = $(column),
                    classes = $column.data('compose-mode-col-classes'),
                    $parentCol;

                if(classes) {
                    $parentCol = $column.parent('.vc_vc_milenia_flexible_grid_column');
                    if($parentCol.length) $parentCol.addClass(classes);
                }

                $column.data('compose-mode-col-classes', null).attr('data-compose-mode-col-classes', '');
            });
        }
    };

})(window.jQuery);
