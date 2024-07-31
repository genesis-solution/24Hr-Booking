<?php

namespace MPHB\Widgets;

class RoomsWidget extends BaseWidget {

	private $isShowTitle;
	private $isShowFeaturedImage;
	private $isShowExcerpt;
	private $isShowDetails;
	private $isShowPricePerNight;
	private $isShowBookButton;
	private $roomTypeIds;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		$baseId = 'mphb_rooms_widget';
		$name   = __( 'Accommodation Types', 'motopress-hotel-booking' );

		$widgetOptions = array(
			'description' => __( 'Display Accommodation Types', 'motopress-hotel-booking' ),
		);

		parent::__construct(
			$baseId,
			$name,
			$widgetOptions
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see \WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		if ( ! empty( $title ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$roomTypeIds = ! empty( $instance['room_type_ids'] ) ? $instance['room_type_ids'] : array();

		if ( ! empty( $roomTypeIds ) ) {
			$this->roomTypeIds = $roomTypeIds;
			$this->enqueueScriptStyles();
			$this->isShowTitle         = \MPHB\Utils\ValidateUtils::validateBool( $instance['show_title'] );
			$this->isShowFeaturedImage = \MPHB\Utils\ValidateUtils::validateBool( $instance['show_featured_image'] );
			$this->isShowExcerpt       = \MPHB\Utils\ValidateUtils::validateBool( $instance['show_excerpt'] );
			$this->isShowDetails       = \MPHB\Utils\ValidateUtils::validateBool( $instance['show_details'] );
			$this->isShowPricePerNight = \MPHB\Utils\ValidateUtils::validateBool( $instance['show_price'] );
			$this->isShowBookButton    = \MPHB\Utils\ValidateUtils::validateBool( $instance['show_book_button'] );

			$roomQuery = $this->getMainQuery();

			ob_start();

			if ( $roomQuery->have_posts() ) {

				do_action( 'mphb_widget_rooms_before_loop' );

				while ( $roomQuery->have_posts() ) :
					$roomQuery->the_post();

					do_action( 'mphb_widget_rooms_before_item' );

					$this->renderRoom();

					do_action( 'mphb_widget_rooms_after_item' );

				endwhile;

				wp_reset_postdata();

				do_action( 'mphb_widget_rooms_after_loop' );
			} else {
				mphb_get_template_part( 'widgets/rooms/not-found' );
			}

			$content = ob_get_clean();

			$wrapperClass = apply_filters( 'mphb_widget_rooms_wrapper-class', 'mphb_widget_rooms-wrapper' );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="' . esc_attr( $wrapperClass ) . '">' . $content . '</div>';
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	private function getMainQuery() {
		$queryAtts = array(
			'post_type'           => MPHB()->postTypes()->roomType()->getPostType(),
			'post__in'            => $this->roomTypeIds,
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
		);
		return new \WP_Query( $queryAtts );
	}

	private function isValidRoom( $id ) {
		return get_post_type( $id ) === MPHB()->postTypes()->roomType()->getPostType() && get_post_status( $id ) === 'publish';
	}

	private function renderRoom() {
		$templateAtts = array(
			'isShowTitle'      => $this->isShowTitle,
			'isShowImage'      => $this->isShowFeaturedImage,
			'isShowExcerpt'    => $this->isShowExcerpt,
			'isShowDetails'    => $this->isShowDetails,
			'isShowPrice'      => $this->isShowPricePerNight,
			'isShowBookButton' => $this->isShowBookButton,
			'categories'       => MPHB()->getCurrentRoomType()->getCategories(),
			'facilities'       => MPHB()->getCurrentRoomType()->getFacilities(),
			'attributes'       => mphb_tmpl_get_room_type_attributes(),
			'view'             => mphb_tmpl_get_room_type_view(),
			'size'             => mphb_tmpl_get_room_type_size(),
			'sizeNumber'       => mphb_tmpl_get_room_type_size( false ),
			'bedType'          => mphb_tmpl_get_room_type_bed_type(),
			'adults'           => mphb_tmpl_get_room_type_adults_capacity(),
			'children'         => mphb_tmpl_get_room_type_children_capacity(),
			'totalCapacity'    => mphb_tmpl_get_room_type_total_capacity(),
		);

		mphb_get_template_part( 'widgets/rooms/room-content', $templateAtts );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see \WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$instance = wp_parse_args(
			$instance,
			array(
				'title'               => '',
				'room_type_ids'       => array(),
				'show_title'          => true,
				'show_featured_image' => true,
				'show_excerpt'        => true,
				'show_details'        => true,
				'show_price'          => true,
				'show_book_button'    => true,
			)
		);

		extract( $instance );
		if ( $room_type_ids === '' ) {
			$room_type_ids = array();
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( esc_attr( 'Title:' ) ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'room_type_ids' ) ); ?>"><?php esc_attr_e( 'Select Accommodation Types:' ); ?></label><br/>
			<select id="<?php echo esc_attr( $this->get_field_id( 'room_type_ids' ) ); ?>" multiple="multiple" name="<?php echo esc_attr( $this->get_field_name( 'room_type_ids' ) ); ?>[]" >
				<?php foreach ( MPHB()->getRoomTypePersistence()->getIdTitleList() as $roomTypeId => $roomTypeTitle ) : ?>
					<?php
					$selected = in_array( $roomTypeId, $room_type_ids ) ? ' selected="selected"' : '';
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<option value="<?php echo esc_attr( $roomTypeId ); ?>" <?php echo $selected; ?>><?php echo esc_html( $roomTypeTitle ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_title' ) ); ?>" <?php checked( $show_title ); ?> style="margin-top: 0;">
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>"><?php esc_html_e( 'Title', 'motopress-hotel-booking' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_featured_image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_featured_image' ) ); ?>" <?php checked( $show_featured_image ); ?> style="margin-top: 0;" >
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_featured_image' ) ); ?>"><?php esc_html_e( 'Featured Image', 'motopress-hotel-booking' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_excerpt' ) ); ?>" <?php checked( $show_excerpt ); ?> style="margin-top: 0;" >
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>"><?php esc_html_e( 'Excerpt (short description)', 'motopress-hotel-booking' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_details' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_details' ) ); ?>" <?php checked( $show_details ); ?> style="margin-top: 0;" >
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_details' ) ); ?>"><?php esc_html_e( 'Details', 'motopress-hotel-booking' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_price' ) ); ?>" <?php checked( $show_price ); ?> style="margin-top: 0;" >
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>"><?php esc_html_e( 'Price', 'motopress-hotel-booking' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_book_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_book_button' ) ); ?>" <?php checked( $show_book_button ); ?> style="margin-top: 0;" >
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_book_button' ) ); ?>"><?php esc_html_e( 'Book Button', 'motopress-hotel-booking' ); ?></label>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see \WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title']         = ( isset( $new_instance['title'] ) && $new_instance['title'] !== '' ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['room_type_ids'] = ( isset( $new_instance['room_type_ids'] ) && $new_instance['room_type_ids'] !== '' ) ? $this->sanitizeRoomTypeIdsArray( $new_instance['room_type_ids'] ) : '';

		$instance['show_title']          = ( isset( $new_instance['show_title'] ) && $new_instance['show_title'] !== '' ) ? (bool) $new_instance['show_title'] : '';
		$instance['show_featured_image'] = ( isset( $new_instance['show_featured_image'] ) && $new_instance['show_featured_image'] !== '' ) ? (bool) $new_instance['show_featured_image'] : '';
		$instance['show_excerpt']        = ( isset( $new_instance['show_excerpt'] ) && $new_instance['show_excerpt'] !== '' ) ? (bool) $new_instance['show_excerpt'] : '';
		$instance['show_details']        = ( isset( $new_instance['show_details'] ) && $new_instance['show_details'] !== '' ) ? (bool) $new_instance['show_details'] : '';
		$instance['show_price']          = ( isset( $new_instance['show_price'] ) && $new_instance['show_price'] !== '' ) ? (bool) $new_instance['show_price'] : '';
		$instance['show_book_button']    = ( isset( $new_instance['show_book_button'] ) && $new_instance['show_book_button'] !== '' ) ? (bool) $new_instance['show_book_button'] : '';

		return $instance;
	}

	protected function sanitizeRoomTypeIdsArray( $value ) {
		$sanitizeValue = array();
		if ( is_array( $value ) ) {
			$sanitizeValue = array_filter( array_map( array( $this, 'sanitizeRoomTypeId' ), $value ) );
		}
		return $sanitizeValue;
	}

	/**
	 *
	 * @param string $value
	 * @return string Empty string for uncorrect value
	 */
	public function sanitizeRoomTypeId( $value ) {
		$value = absint( $value );
		return ( $this->isValidRoom( $value ) ) ? (string) $value : '';
	}

	private function enqueueScriptStyles() {
		wp_enqueue_style( 'mphb' );
	}

}
