<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'EDITORS_DIR', 'navbar/class-vc-navbar.php' );

/**
 *
 */
class Vc_Navbar_Frontend extends Vc_Navbar {
	/**
	 * @var array
	 */
	protected $controls = array(
		'add_element',
		'templates',
		'view_post',
		'save_update',
		'screen_size',
		'custom_css',
		'seo',
	);
	/**
	 * @var string
	 */
	protected $controls_filter_name = 'vc_nav_front_controls';
	/**
	 * @var string
	 */
	protected $brand_url = 'https://wpbakery.com/?utm_source=wpb-plugin&utm_medium=frontend-editor&utm_campaign=info&utm_content=logo';

	/**
	 * @var string
	 */
	protected $css_class = 'vc_navbar vc_navbar-frontend';

	/**
	 * @return string
	 */
	public function getControlScreenSize() {
		$disable_responsive = vc_settings()->get( 'not_responsive_css' );
		if ( '1' !== $disable_responsive ) {
            // phpcs:ignore
			$screen_sizes = apply_filters( 'wpb_navbar_getControlScreenSize', array(
				array(
					'title' => esc_html__( 'Desktop', 'js_composer' ),
					'size' => '100%',
					'key' => 'default',
					'active' => true,
				),
				array(
					'title' => esc_html__( 'Tablet landscape mode', 'js_composer' ),
					'size' => '1024px',
					'key' => 'landscape-tablets',
				),
				array(
					'title' => esc_html__( 'Tablet portrait mode', 'js_composer' ),
					'size' => '768px',
					'key' => 'portrait-tablets',
				),
				array(
					'title' => esc_html__( 'Smartphone landscape mode', 'js_composer' ),
					'size' => '480px',
					'key' => 'landscape-smartphones',
				),
				array(
					'title' => esc_html__( 'Smartphone portrait mode', 'js_composer' ),
					'size' => '320px',
					'key' => 'portrait-smartphones',
				),
			) );
			$output = '<li class="vc_pull-right">' . '<div class="vc_dropdown" id="vc_screen-size-control">' . '<a href="#" class="vc_dropdown-toggle vc_icon-btn"' . ' title="' . esc_attr__( 'Responsive preview', 'js_composer' ) . '"><i class="vc-composer-icon vc_current-layout-icon vc-c-icon-layout_default"' . ' id="vc_screen-size-current"></i></a>' . '<ul class="vc_dropdown-list">';
			$screen = current( $screen_sizes );
			while ( $screen ) {
				$output .= '<li><a href="#" title="' . esc_attr( $screen['title'] ) . '"' . ' class="vc_screen-width vc_icon-btn vc-composer-icon vc-c-icon-layout_' . esc_attr( $screen['key'] ) . ( isset( $screen['active'] ) && $screen['active'] ? ' active' : '' ) . '" data-size="' . esc_attr( $screen['size'] ) . '"></a></li>';
				next( $screen_sizes );
				$screen = current( $screen_sizes );
			}
			$output .= '</ul></div></li>';

			return $output;
		}

		return '';
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function getControlSaveUpdate() {
		$post = $this->post();
		$post_type = get_post_type_object( $this->post->post_type );
		$can_publish = current_user_can( $post_type->cap->publish_posts );
		ob_start();
		?>
		<li class="vc_pull-right">
			<div class="vc_dropdown vc_dropdown-more" id="vc_more-options">
				<a class="vc_dropdown-toggle vc_icon-btn" title="More">
					<i class="vc-composer-icon vc-c-icon-more"></i>
				</a>
				<ul class="vc_dropdown-list">
					<li class="vc_dropdown-list-item">
						<a href="<?php echo get_edit_post_link( $post ) . '&wpb-backend-editor'; ?>"><?php esc_html_e( 'Backend Editor', 'js_composer' ); ?></a>
					</li>
					<li class="vc_dropdown-list-item">
						<a href="<?php echo get_permalink( $post ); ?>"><?php esc_html_e( 'View Page', 'js_composer' ); ?></a>
					</li>
				</ul>
			</div>
		</li>
		<li class="vc_show-mobile vc_pull-right">
			<?php
			if ( ! in_array( $post->post_status, array(
				'publish',
				'future',
				'private',
			), true ) ) :
				?>
				<?php if ( 'draft' === $post->post_status ) : ?>
				<button type="button" class="vc_btn vc_btn-default vc_navbar-btn vc_btn-save-draft"
						id="vc_button-save-draft"
						title="<?php esc_attr_e( 'Save Draft', 'js_composer' ); ?>"><?php esc_html_e( 'Save Draft', 'js_composer' ); ?></button>
			<?php elseif ( 'pending' === $post->post_status && $can_publish ) : ?>
				<button type="button" class="vc_btn vc_btn-primary vc_navbar-btn vc_btn-save"
						id="vc_button-save-as-pending"
						title="<?php esc_attr_e( 'Save as Pending', 'js_composer' ); ?>"><?php esc_html_e( 'Save as Pending', 'js_composer' ); ?></button>
			<?php endif ?>
				<?php if ( $can_publish ) : ?>
				<button type="button" class="vc_btn vc_btn-primary vc_navbar-btn vc_btn-save"
						id="vc_button-update" title="<?php esc_attr_e( 'Publish', 'js_composer' ); ?>"
						data-change-status="publish"><?php esc_html_e( 'Publish', 'js_composer' ); ?></button>
			<?php else : ?>
				<button type="button" class="vc_btn vc_btn-primary vc_navbar-btn vc_btn-save"
						id="vc_button-update" title="<?php esc_attr_e( 'Submit for Review', 'js_composer' ); ?>"
						data-change-status="pending"><?php esc_html_e( 'Submit for Review', 'js_composer' ); ?></button>
			<?php endif ?>
			<?php else : ?>
				<button type="button" class="vc_btn vc_btn-primary vc_navbar-btn vc_btn-save"
						id="vc_button-update"
						title="<?php esc_attr_e( 'Update', 'js_composer' ); ?>"><?php esc_html_e( 'Update', 'js_composer' ); ?></button>
			<?php endif ?>
		</li>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * @return string
	 */
	public function getControlViewPost() {
		return '<li class="vc_pull-right">' . '<a href="' . esc_url( get_permalink( $this->post() ) ) . '" class="vc_icon-btn vc_back-button"' . ' title="' . esc_attr__( 'Exit WPBakery Page Builder edit mode', 'js_composer' ) . '"><i class="vc-composer-icon vc-c-icon-close"></i></a>' . '</li>';
	}

	/**
	 * @return string
	 */
	public function getControlSeo() {
		return '<li class="vc_pull-right"><a href="javascript:;" class="vc_icon-btn vc_seo-button" id="vc_seo-button" title="' . esc_attr__( 'WPBakery SEO', 'js_composer' ) . '"><i class="vc-composer-icon vc-c-icon-seo"></i></a></li>';
	}
}
