jQuery(function () {
    'use strict';

    var POST_ID_SELECTOR = '.check-column input';
    var POST_ID_COLUMN = '.check-column';

    var $table = jQuery('.wp-list-table.posts');

    $table.sortable({
        items: 'tbody tr',
        cursor: 'move',
        handle: '.column-handle',
        axis: 'y',
        opacity: 0.65,
        scrollSensitivity: 40,
        update: onUpdate
    });

    function onUpdate(event, ui)
    {
        var postId = parseInt(ui.item.find(POST_ID_SELECTOR).val());
        var nextPostId = ui.item.next().find(POST_ID_SELECTOR).val();

        if (nextPostId == undefined) {
            nextPostId = 0;
        } else {
            nextPostId = parseInt(nextPostId);
        }

        // Show spinner
        ui.item.find(POST_ID_SELECTOR).hide();
        ui.item.find(POST_ID_COLUMN).append('<span class="mphb-preloader"></span>');

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'mphb_cf_reorder_posts',
                mphb_nonce: MPHBCheckoutFields.nonces.reorder_posts,
                post_id: postId,
                next_post_id: nextPostId
            },
            complete: function () {
                ui.item.find(POST_ID_COLUMN).find('.mphb-preloader').remove();
                ui.item.find(POST_ID_SELECTOR).show();
            }
        });

        // Fix row colors
        $table.find('tbody tr').each(function (index, element) {
            jQuery(element).toggleClass('alternate', index % 2 == 0);
        });
    }
});
