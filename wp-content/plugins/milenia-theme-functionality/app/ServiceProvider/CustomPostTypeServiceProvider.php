<?php
namespace Milenia\App\ServiceProvider;

use Milenia\Core\App;
use Milenia\Core\Support\Entities\CustomPostTypeEntity;
use Milenia\Core\Support\Facades\CustomPostTypeFacade;
use Milenia\Core\Support\ServiceProvider\ServiceProviderInterface;

class CustomPostTypeServiceProvider implements ServiceProviderInterface
{
    /**
     * The service provider's initialization.
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        App::bind('CustomPostTypeFacade', new CustomPostTypeFacade());

        add_action('init', array($this, 'registerPortfolio'));
        add_action('init', array($this, 'registerTestimonials'));
        add_action('init', array($this, 'registerTeamMembers'));
        add_action('init', array($this, 'registerGalleries'));
        add_action('init', array($this, 'registerOffers'));
    }

    /**
     * Registers the 'milenia-portfolio' custom post type.
     *
     * @access protected
     * @return void
     */
    public function registerPortfolio()
    {
        App::get('CustomPostTypeFacade')->register(new CustomPostTypeEntity('milenia-portfolio', array(
            'label' => esc_html__('Portfolio', 'milenia-app-textdomain'),
            'labels' => array(
                'name' => esc_html__('Portfolio', 'milenia-app-textdomain'),
                'singular_name' => esc_html__('Project', 'milenia-app-textdomain'),
                'add_new' => esc_html__('Add Project', 'milenia-app-textdomain'),
                'add_new_item' => esc_html__('Add New Project', 'milenia-app-textdomain'),
                'edit_item' => esc_html__('Edit Project', 'milenia-app-textdomain'),
                'view_item' => esc_html__('View Project', 'milenia-app-textdomain'),
                'search_items' => esc_html__('Search Projects', 'milenia-app-textdomain'),
                'not_found' => esc_html__('Projects not found.', 'milenia-app-textdomain'),
                'not_found_in_trash' => esc_html__('Projects not found.', 'milenia-app-textdomain'),
                'all_items' => esc_html__('All Projects', 'milenia-app-textdomain'),
                'filter_items_list' => esc_html__('Filter Projects list', 'milenia-app-textdomain'),
                'items_list_navigation' => esc_html__('Navigation of Projects', 'milenia-app-textdomain'),
                'items_list' => esc_html__('List of Projects', 'milenia-app-textdomain'),
                'view_items' => esc_html__('View Projects', 'milenia-app-textdomain'),
                'attributes' => esc_html__('Projects attributes', 'milenia-app-textdomain')
            ),
            'public' => true,
            'menu_position' => 56,
            'menu_icon' => 'dashicons-portfolio',
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'taxonomies' => array('milenia-portfolio-categories'),
            'supports' => array('title', 'editor', 'thumbnail', 'comments'),
            'rewrite' => array('slug' => 'portfolio')
        )))->taxonomies(array(
            'milenia-portfolio-categories' => array(
                'label' => esc_html__('Categories', 'milenia-app-textdomain'),
                'public' => true,
                'hierarchical' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'portfolio-categories'),
                'show_admin_column' => true
            ),
        	'milenia-portfolio-tags' => array(
        		'label' => esc_html__('Tags', 'milenia-app-textdomain'),
                'public' => true,
                'hierarchical' => false,
                'query_var' => true,
                'rewrite' => array('slug' => 'portfolio-tags'),
                'show_admin_column' => true
        	)
        ))->adminColumns(array($this, 'portfolioAdminColumns'), array($this, 'portfolioAdminColumnsContent'));
    }

    /**
     * Prepares the portfolio items admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function portfolioAdminColumns($columns)
    {
        return array_merge(array(
 			'cb'=> '<input id="cb-select-all-1" type="checkbox">',
 		    'thumb column-comments' => esc_html__('Thumbnail', 'milenia-app-textdomain'),
            'title' => esc_html__('Title', 'milenia-app-textdomain'),
 		    'column-meta' => esc_html__('Meta information', 'milenia-app-textdomain'),
 		), $columns);
    }

    /**
     * Prepares the portfolio items admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function portfolioAdminColumnsContent($column)
    {
 		global $post;

 		switch ($column) {
 			case 'thumb column-comments':
 				if (has_post_thumbnail($post->ID)) {
 					echo get_the_post_thumbnail($post->ID, array(60, 60));
 				}
 			break;

            case 'column-meta':
                $project_meta = get_post_meta($post->ID, 'milenia-project-meta', true);

                if($project_meta) { ?>
                    <ul>
                        <?php foreach($project_meta as $meta) : ?>
                            <li><strong><?php echo esc_html($meta[0]) ?></strong> <?php echo esc_html($meta[1]); ?></li>
                        <?php endforeach;?>
                    </ul>
                <?php }
 			break;
 		}
 	}

    /**
     * Registers the 'milenia-team-members' custom post type.
     *
     * @access protected
     * @return void
     */
    public function registerTeamMembers()
    {
        App::get('CustomPostTypeFacade')->register(new CustomPostTypeEntity('milenia-team-members', array(
            'label' => esc_html__('Team Members', 'milenia-app-textdomain'),
            'labels' => array(
                'name' => esc_html__('Team Members', 'milenia-app-textdomain'),
                'singular_name' => esc_html__('Team Member', 'milenia-app-textdomain'),
                'add_new' => esc_html__('Add Team Member', 'milenia-app-textdomain'),
                'add_new_item' => esc_html__('Add New Team Member', 'milenia-app-textdomain'),
                'edit_item' => esc_html__('Edit Team Member', 'milenia-app-textdomain'),
                'view_item' => esc_html__('View Team Member', 'milenia-app-textdomain'),
                'search_items' => esc_html__('Search Team Members', 'milenia-app-textdomain'),
                'not_found' => esc_html__('Team Members not found.', 'milenia-app-textdomain'),
                'not_found_in_trash' => esc_html__('Team Members not found.', 'milenia-app-textdomain'),
                'all_items' => esc_html__('All Team Members', 'milenia-app-textdomain'),
                'featured_image' => esc_html__('Team Member Photo', 'milenia-app-textdomain'),
                'set_featured_image' => esc_html__('Set Team Member photo', 'milenia-app-textdomain'),
                'remove_featured_image' => esc_html__('Remove Team Member photo', 'milenia-app-textdomain'),
                'use_featured_image' => esc_html__('Use as Team Member photo', 'milenia-app-textdomain'),
                'filter_items_list' => esc_html__('Filter Team Members list', 'milenia-app-textdomain'),
                'items_list_navigation' => esc_html__('Navigation of Team Members', 'milenia-app-textdomain'),
                'items_list' => esc_html__('List of Team Members', 'milenia-app-textdomain'),
                'view_items' => esc_html__('View Team Members', 'milenia-app-textdomain'),
                'attributes' => esc_html__('Team Member attributes', 'milenia-app-textdomain')
            ),
            'public' => true,
            'menu_position' => 57,
            'menu_icon' => 'dashicons-groups',
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'taxonomies' => array('milenia-tm-categories'),
            'supports' => array('title', 'editor', 'thumbnail'),
            'rewrite' => array('slug' => 'team-members')
        )))->taxonomies(array(
            'milenia-tm-categories' => array(
                'label' => esc_html__('Categories', 'milenia-app-textdomain'),
                'public' => true,
                'hierarchical' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'team-members-categories'),
                'show_admin_column' => true
            )
        ))->adminColumns(array($this, 'teamMembersAdminColumns'), array($this, 'teamMembersAdminColumnsContent'));
    }

    /**
     * Prepares the team members admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function teamMembersAdminColumns($columns)
    {
        if(isset($columns['title'])) unset($columns['title']);

 		return array_merge(array(
 			'cb'=> '<input id="cb-select-all-1" type="checkbox">',
 		    'thumb column-comments' => esc_html__('Thumb', 'milenia-app-textdomain'),
            'title' => esc_html__('Full name', 'milenia-app-textdomain'),
            'position' => esc_html__('Position', 'milenia-app-textdomain')
 		), $columns);
    }

    /**
     * Prepares the team members admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function teamMembersAdminColumnsContent($column)
    {
        global $post;

 		switch ($column) {
 			case 'thumb column-comments':
 				if (has_post_thumbnail($post->ID)) {
 					echo get_the_post_thumbnail($post->ID, array(60, 60));
 				}
 			break;
            case 'position' :
                echo esc_html(get_post_meta($post->ID, 'milenia-team-member-position', true));
            break;

            case 'facebook' :
                printf('<a href="%s" target="_blank" rel="nofollow">%1$s</a>', esc_url(get_post_meta($post->ID, 'milenia-team-member-facebook', true))) ;
            break;

            case 'youtube' :
                printf('<a href="%s" target="_blank" rel="nofollow">%1$s</a>', esc_url(get_post_meta($post->ID, 'milenia-team-member-youtube', true))) ;
            break;

            case 'twitter' :
                printf('<a href="%s" target="_blank" rel="nofollow">%1$s</a>', esc_url(get_post_meta($post->ID, 'milenia-team-member-twitter', true))) ;
            break;

            case 'google_plus' :
                printf('<a href="%s" target="_blank" rel="nofollow">%1$s</a>', esc_url(get_post_meta($post->ID, 'milenia-team-member-google-plus', true))) ;
            break;

            case 'instagram' :
                printf('<a href="%s" target="_blank" rel="nofollow">%1$s</a>', esc_url(get_post_meta($post->ID, 'milenia-team-member-instagram', true))) ;
            break;
 		}
 	}

    /**
     * Registers the 'milenia-testimonials' custom post type.
     *
     * @access protected
     * @return void
     */
    public function registerTestimonials()
    {
        App::get('CustomPostTypeFacade')->register(new CustomPostTypeEntity('milenia-testimonials', array(
            'label' => esc_html__('Testimonials', 'milenia-app-textdomain'),
            'labels' => array(
                'name' => esc_html__('Testimonials', 'milenia-app-textdomain'),
                'singular_name' => esc_html__('Testimonial', 'milenia-app-textdomain'),
                'add_new' => esc_html__('Add Testimonial', 'milenia-app-textdomain'),
                'add_new_item' => esc_html__('Add New Testimonial', 'milenia-app-textdomain'),
                'edit_item' => esc_html__('Edit Testimonial', 'milenia-app-textdomain'),
                'view_item' => esc_html__('View Testimonial', 'milenia-app-textdomain'),
                'search_items' => esc_html__('Search Testimonials', 'milenia-app-textdomain'),
                'not_found' => esc_html__('Testimonials not found.', 'milenia-app-textdomain'),
                'not_found_in_trash' => esc_html__('Testimonials not found.', 'milenia-app-textdomain'),
                'all_items' => esc_html__('All Testimonials', 'milenia-app-textdomain'),
                'featured_image' => esc_html__('Author photo', 'milenia-app-textdomain'),
                'set_featured_image' => esc_html__('Set Author photo', 'milenia-app-textdomain'),
                'remove_featured_image' => esc_html__('Remove Author photo', 'milenia-app-textdomain'),
                'use_featured_image' => esc_html__('Use as Author photo', 'milenia-app-textdomain'),
                'filter_items_list' => esc_html__('Filter Testimonials list', 'milenia-app-textdomain'),
                'items_list_navigation' => esc_html__('Navigation of Testimonials', 'milenia-app-textdomain'),
                'items_list' => esc_html__('List of Testimonials', 'milenia-app-textdomain'),
                'view_items' => esc_html__('View Testimonials', 'milenia-app-textdomain'),
                'attributes' => esc_html__('Testimonial attributes', 'milenia-app-textdomain')
            ),
            'public' => true,
            'menu_position' => 58,
            'menu_icon' => 'dashicons-edit',
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'taxonomies' => array('milenia-testimonials-categories'),
            'supports' => array('title', 'editor', 'thumbnail', 'comments'),
            'rewrite' => array('slug' => 'testimonials')
        )))->taxonomies(array(
            'milenia-testimonials-categories' => array(
                'label' => esc_html__('Categories', 'milenia-app-textdomain'),
                'public' => true,
                'hierarchical' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'testimonials-categories'),
                'show_admin_column' => true
            )
        ))->adminColumns(array($this, 'testimonialsAdminColumns'), array($this, 'testimonialsAdminColumnsContent'));
    }

    /**
     * Prepares testimonials admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function testimonialsAdminColumns($columns)
    {
        if(isset($columns['title'])) unset($columns['title']);

 		return array_merge(array(
 			'cb'=> '<input id="cb-select-all-1" type="checkbox">',
 		    'thumb column-comments' => esc_html__('Photo', 'milenia-app-textdomain'),
            'title' => esc_html__('Full name', 'milenia-app-textdomain'),
            'location' => esc_html__("Author's location", 'milenia-app-textdomain'),
            'summary' => esc_html__('Summary', 'milenia-app-textdomain')
 		), $columns);
    }

    /**
     * Prepares testimonials admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function testimonialsAdminColumnsContent($column)
    {
        global $post;

 		switch ($column) {
 			case 'thumb column-comments':
 				if (has_post_thumbnail($post->ID)) {
 					echo get_the_post_thumbnail($post->ID, array(60, 60));
 				}
 			break;
            case 'location' :
                echo esc_html(get_post_meta($post->ID, 'milenia-testimonial-author-location', true));
            break;
            case 'summary' :
                echo esc_html(get_the_excerpt($post->ID));
            break;
 		}
 	}

    /**
     * Registers the 'milenia-galleries' custom post type.
     *
     * @access protected
     * @return void
     */
    public function registerGalleries()
    {
        App::get('CustomPostTypeFacade')->register(new CustomPostTypeEntity('milenia-galleries', array(
            'label' => esc_html__('Galleries', 'milenia-app-textdomain'),
            'labels' => array(
                'name' => esc_html__('Galleries', 'milenia-app-textdomain'),
                'singular_name' => esc_html__('Gallery', 'milenia-app-textdomain'),
                'add_new' => esc_html__('Add Gallery', 'milenia-app-textdomain'),
                'add_new_item' => esc_html__('Add New Gallery', 'milenia-app-textdomain'),
                'edit_item' => esc_html__('Edit Gallery', 'milenia-app-textdomain'),
                'view_item' => esc_html__('View Gallery', 'milenia-app-textdomain'),
                'search_items' => esc_html__('Search Galleries', 'milenia-app-textdomain'),
                'not_found' => esc_html__('Galleries not found.', 'milenia-app-textdomain'),
                'not_found_in_trash' => esc_html__('Galleries not found.', 'milenia-app-textdomain'),
                'all_items' => esc_html__('All Galleries', 'milenia-app-textdomain'),
                'filter_items_list' => esc_html__('Filter Galleries list', 'milenia-app-textdomain'),
                'items_list_navigation' => esc_html__('Navigation of Galleries', 'milenia-app-textdomain'),
                'items_list' => esc_html__('List of Galleries', 'milenia-app-textdomain'),
                'view_items' => esc_html__('View Galleries', 'milenia-app-textdomain'),
                'attributes' => esc_html__('Galleries attributes', 'milenia-app-textdomain')
            ),
            'public' => true,
            'menu_position' => 51,
            'menu_icon' => 'dashicons-format-gallery',
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'taxonomies' => array('milenia-gallery-categories'),
            'supports' => array('title'),
            'rewrite' => array(
        		'slug' => 'galleries'
        	)
        )))->taxonomies(array(
            'milenia-gallery-categories' => array(
                'label' => esc_html__('Categories', 'milenia-app-textdomain'),
                'public' => true,
                'hierarchical' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'gallery-categories'),
                'show_admin_column' => true
            )
        ))->adminColumns(array($this, 'galleriesAdminColumns'), array($this, 'galleriesAdminColumnsContent'));
    }

    /**
     * Prepares galleries admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function galleriesAdminColumns($columns)
    {
        return array_merge(array(
 			'cb'=> '<input id="cb-select-all-1" type="checkbox">',
 		    'thumb column-collage' => esc_html__('Images', 'milenia-app-textdomain'),
            'title' => esc_html__('Title', 'milenia-app-textdomain'),
 		    'images-amount column-images-amount' => esc_html__('Amount of images', 'milenia-app-textdomain'),
 		    'single-page-type column-single-page-type' => esc_html__('Single page layout', 'milenia-app-textdomain'),
 		), $columns);
    }

    /**
     * Prepares galleries admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function galleriesAdminColumnsContent($column)
    {
        global $post;

        $milenia_gallery_builder = get_post_meta($post->ID, 'milenia_gallery_builder', true);
        $slides = null;

        if(isset($milenia_gallery_builder['sliders']) && is_array($milenia_gallery_builder['sliders']) && isset($milenia_gallery_builder['sliders']['slides']) && is_array($milenia_gallery_builder['sliders']['slides'])) {
            $slides = $milenia_gallery_builder['sliders']['slides'];
        }


 		switch ($column) {
 			case 'thumb column-collage':
                if($slides) : ?>
                    <div class="milenia-collage">
                        <?php foreach(array_slice($slides, 0, 4) as $index => $slide) : ?>
                            <?php if(isset($slide['attach_id'])) : ?>
                                <div class="milenia-collage-image">
                                    <?php echo wp_get_attachment_image($slide['attach_id'], array(50, 50)); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif;
 			break;

            case 'images-amount column-images-amount':
                echo esc_html(is_array($slides) ? count($slides) : 0);
 			break;

            case 'single-page-type column-single-page-type':
                $page_type_title_map = array(
                    'grid' => esc_html__('Grid', 'milenia-app-textdomain'),
                    'masonry' => esc_html__('Masonry', 'milenia-app-textdomain')
                );

                if(isset($milenia_gallery_builder['sliders']) && is_array($milenia_gallery_builder['sliders']) && isset($milenia_gallery_builder['sliders']['single-page-layout']) && array_key_exists($milenia_gallery_builder['sliders']['single-page-layout'], $page_type_title_map)) {
                    echo esc_html($page_type_title_map[$milenia_gallery_builder['sliders']['single-page-layout']]);
                }
            break;
 		}
 	}


    /**
     * Registers the 'milenia-offers' custom post type.
     *
     * @access protected
     * @return void
     */
    public function registerOffers()
    {
        App::get('CustomPostTypeFacade')->register(new CustomPostTypeEntity('milenia-offers', array(
            'label' => esc_html__('Offers', 'milenia-app-textdomain'),
            'labels' => array(
                'name' => esc_html__('Offers', 'milenia-app-textdomain'),
                'singular_name' => esc_html__('Offer', 'milenia-app-textdomain'),
                'add_new' => esc_html__('Add an Offer', 'milenia-app-textdomain'),
                'add_new_item' => esc_html__('Add new Offer', 'milenia-app-textdomain'),
                'edit_item' => esc_html__('Edit Offer', 'milenia-app-textdomain'),
                'view_item' => esc_html__('View Offer', 'milenia-app-textdomain'),
                'search_items' => esc_html__('Search Offers', 'milenia-app-textdomain'),
                'not_found' => esc_html__('Offers not found.', 'milenia-app-textdomain'),
                'not_found_in_trash' => esc_html__('Offers not found.', 'milenia-app-textdomain'),
                'all_items' => esc_html__('All Offers', 'milenia-app-textdomain'),
                'filter_items_list' => esc_html__('Filter Offers list', 'milenia-app-textdomain'),
                'items_list_navigation' => esc_html__('Navigation of Offers', 'milenia-app-textdomain'),
                'items_list' => esc_html__('List of Offers', 'milenia-app-textdomain'),
                'view_items' => esc_html__('View Offers', 'milenia-app-textdomain'),
                'attributes' => esc_html__('Offers attributes', 'milenia-app-textdomain')
            ),
            'public' => true,
            'menu_position' => 55,
            'menu_icon' => 'dashicons-megaphone',
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'taxonomies' => array('milenia-offers-categories'),
            'supports' => array('title', 'editor', 'comments', 'thumbnail'),
            'rewrite' => array(
        		'slug' => 'offers'
        	)
        )))->taxonomies(array(
            'milenia-offers-categories' => array(
                'label' => esc_html__('Categories', 'milenia-app-textdomain'),
                'public' => true,
                'hierarchical' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'offers-categories'),
                'show_admin_column' => true
            ),
            'milenia-offers-tags' => array(
        		'label' => esc_html__('Tags', 'milenia-app-textdomain'),
                'public' => true,
                'hierarchical' => false,
                'query_var' => true,
                'rewrite' => array('slug' => 'offers-tags'),
                'show_admin_column' => true
        	)
        ))->adminColumns(array($this, 'offersAdminColumns'), array($this, 'offersAdminColumnsContent'));
    }

    /**
     * Prepares offers admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function offersAdminColumns($columns)
    {
 		return array_merge(array(
 			'cb'=> '<input id="cb-select-all-1" type="checkbox">',
 		    'thumb column-comments' => esc_html__('Thumb', 'milenia-app-textdomain'),
 		    'title' => esc_html__('Title', 'milenia-app-textdomain'),
            'period column-period' => esc_html__('Period', 'milenia-app-textdomain'),
            'price column-price' => esc_html__('Price', 'milenia-app-textdomain'),
 		), $columns);
    }

    /**
     * Prepares offers admin page.
     *
     * @param array $columns
     * @access public
     * @return array
     */
    public function offersAdminColumnsContent($column)
    {
        global $post;

 		switch ($column) {
 			case 'thumb column-comments':
 				if (has_post_thumbnail($post->ID)) {
 					echo get_the_post_thumbnail($post->ID, array(60, 60));
 				}
 			break;

            case 'period column-period':
                if(function_exists('rwmb_get_value')) {
                    $start_date = rwmb_get_value('milenia-offer-start-date', null, $post->ID );
                    $end_date = rwmb_get_value('milenia-offer-end-date', null, $post->ID );

                    if(!empty($start_date) && empty($end_date))
                    { ?>
                        <ul>
                            <li><strong><?php esc_html_e('Start date:', 'milenia-app-textdomain') ?></strong> <?php echo mysql2date('F j, Y g:i', $start_date, true); ?></li>
                        </ul>
                    <?php
                    }
                    elseif(empty($start_date) && !empty($end_date))
                    { ?>
                        <ul>
                            <li><strong><?php esc_html_e('End date:', 'milenia-app-textdomain') ?></strong> <?php echo mysql2date('F j, Y g:i', $end_date, true); ?></li>
                        </ul>
                    <?php
                    }
                    elseif(!empty($start_date) && !empty($end_date))
                    {
                        ?>
                        <ul>
                            <li><strong><?php esc_html_e('Start date:', 'milenia-app-textdomain') ?></strong> <?php echo mysql2date('F j, Y g:i', $start_date, true); ?></li>
                            <li><strong><?php esc_html_e('End date:', 'milenia-app-textdomain') ?></strong> <?php echo mysql2date('F j, Y g:i', $end_date, true); ?></li>
                        </ul>
                        <?php
                    }
                }
            break;

            case 'price column-price':
                if(function_exists('rwmb_get_value')) {
                    $price = rwmb_get_value('milenia-offer-price', null, $post->ID );
                    $currency = rwmb_get_value('milenia-offer-currency', null, $post->ID );

                    if(!empty($price) && !empty($currency)) : ?>
                        <strong><?php printf('%s%s', esc_html($currency), esc_html($price)); ?></strong>
                    <?php endif;
                }
            break;
 		}
 	}

}
?>
