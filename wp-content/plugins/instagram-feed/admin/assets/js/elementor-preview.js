'use strict';


var InstagramFeedElementor = window.InstagramFeedElementor || ( function( document, window, $ ) {

	var vars = {};

	var app = {

		init: function() {
			app.events();
		},

		events: function() {

			$( window ).on('elementor/frontend/init', function ( $scope ) {

				elementorFrontend.hooks.addAction('frontend/element_ready/sbi-widget.default', app.frontendWidgetInit);
				if( 'undefined' !== typeof elementor ){
					elementor.hooks.addAction( 'panel/open_editor/widget/sbi-widget', app.widgetPanelOpen );
				}

			});

		},

		SbiInitWidget: function() {
			setTimeout(function(){
				window.sbi_init();
			}, 1000)
			jQuery('body').find('.sbi_lightbox').each(function(index, el){
				if( index != 0 )
					jQuery(el).remove();
			});
			jQuery('body').find('.sbi_lightboxOverlay').each(function(index, el){
				if( index != 0 )
					jQuery(el).remove();
			});

			//window.parent.window[0].cff_init($(window.parent.window[0]).find('.cff'));

		},

		registerWidgetEvents: function( $scope ) {
			$scope
				.on( 'change', '.sb-elementor-cta-feedselector', app.selectFeedInPreview );

		},

		frontendWidgetInit : function( $scope ){
			app.SbiInitWidget();
			app.registerWidgetEvents( $scope );
		},

		findFeedSelector: function( event ) {

			vars.$select = event && event.$el ?
				event.$el.closest( '#elementor-controls' ).find( 'select[data-setting="feed_id"]' ) :
				window.parent.jQuery( '#elementor-controls select[data-setting="feed_id"]' );
		},


		selectFeedInPreview : function( event ){

			vars.feedId = $( this ).val();

			app.findFeedSelector();

			vars.$select.val( vars.feedId ).trigger( 'change' );

		},


		widgetPanelOpen: function( panel, model ) {
			panel.$el.find( '.elementor-control.elementor-control-feed_id' ).find( 'select' ).on( 'change', function(){
				setTimeout(function(){
					app.SbiInitWidget();
				}, 400)
			});
		},



	};

	return app;



}( document, window, jQuery ) );


InstagramFeedElementor.init();