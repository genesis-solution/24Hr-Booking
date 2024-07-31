<div class="milenia-grid-item">
    <figure class="milenia-tabbed-carousel-thumb">
        <?php if($room->hasFeaturedImage()) : ?>
            <div data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url($room->getId(), 'entity-thumb-standard')); ?>" class="milenia-tabbed-carousel-thumb-image"></div>
        <?php endif; ?>
        <?php if(!empty($room->getTitle())) : ?>
            <figcaption class="milenia-tabbed-carousel-thumb-caption milenia-text-color--darkest"><?php echo esc_html($room->getTitle()); ?></figcaption>
        <?php endif; ?>
    </figure>
</div>
