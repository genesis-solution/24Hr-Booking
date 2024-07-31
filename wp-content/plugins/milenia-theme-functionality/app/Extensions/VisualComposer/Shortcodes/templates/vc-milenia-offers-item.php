<?php
/**
 * The template file that is responsible to describe an offer element markup.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia') );
}

$milenia_offer_price = rwmb_get_value('milenia-offer-price', null, get_the_ID());
$milenia_offer_currency = rwmb_get_value('milenia-offer-currency', null, get_the_ID());
?>

<div <?php post_class('milenia-grid-item'); ?>>
    <!--================ Offer ================-->
    <article <?php post_class('milenia-pricing-table'); ?>>
        <?php if(has_post_thumbnail()) : ?>
            <div class="milenia-pricing-table-media">
                <a href="<?php the_permalink(); ?>" class="milenia-pricing-table-link milenia-ln--independent">
                    <?php the_post_thumbnail('entity-thumb-square'); ?>
                </a>
            </div>
        <?php endif; ?>

        <div class="milenia-pricing-table-content milenia-outline-over">
            <?php if(!empty(get_the_title())) : ?>
                <h2 class="milenia-pricing-table-title">
                    <a href="<?php the_permalink(); ?>" class="milenia-color--unchangeable"><?php the_title(); ?></a>
                </h2>
            <?php endif; ?>
            <?php the_content('', true); ?>
            <?php if($milenia_offer_currency && $milenia_offer_price) : ?>
                <strong class="milenia-pricing-table-price"><?php printf('%s%s', esc_html($milenia_offer_currency), esc_html($milenia_offer_price)); ?></strong>
            <?php endif; ?>

            <a href="<?php the_permalink(); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-primary"><?php esc_html_e('More Details', 'milenia'); ?></a>
        </div>
    </article>
    <!--================ End of Offer ================-->
</div>
