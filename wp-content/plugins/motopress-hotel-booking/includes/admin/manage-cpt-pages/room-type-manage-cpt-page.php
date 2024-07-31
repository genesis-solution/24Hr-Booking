<?php

namespace MPHB\Admin\ManageCPTPages;

use \MPHB\Entities;

class RoomTypeManageCPTPage extends ManageCPTPage {

	public function __construct( $postType, $atts = array() ) {
		parent::__construct( $postType, $atts );
		$this->description = __( 'These are not physical accommodations, but their types. E.g. standard double room. To specify the real number of existing accommodations, you\'ll need to use Generate Accommodations menu.', 'motopress-hotel-booking' );
		add_action( 'parse_query', array( $this, 'parseQuery' ) );
		add_filter( 'request', array( $this, 'filterCustomOrderBy' ) );
		add_filter( 'post_row_actions', array( $this, 'filterRowActions' ) );
	}


	public function filterColumns( $columns ) {

		$customColumns = array(
			'capacity' => __( 'Capacity', 'motopress-hotel-booking' ),
			'bed'      => __( 'Bed Type', 'motopress-hotel-booking' ),
			'rooms'    => __( 'Accommodations', 'motopress-hotel-booking' ),
		);

		// Set custom columns position before "DATE" column
		$offset  = array_search( 'date', array_keys( $columns ) );
		$columns = array_slice( $columns, 0, $offset, true ) + $customColumns + array_slice( $columns, $offset, count( $columns ) - 1, true );

		return $columns;
	}

	public function filterSortableColumns( $columns ) {
		$columns['bed'] = 'mphb_bed';

		return $columns;
	}

	public function renderColumns( $column, $postId ) {
		$roomType = MPHB()->getRoomTypeRepository()->findById( $postId );
		switch ( $column ) {
			case 'id':
				echo esc_html( $roomType->getId() );
				break;
			case 'capacity':
				$roomSize = $roomType->getSize();
				?>
				<p>
					<?php if ( $roomType->hasLimitedTotalCapacity() ) { ?>
						<?php esc_html_e( 'Total:', 'motopress-hotel-booking' ); ?>&nbsp;<?php echo esc_html( $roomType->getTotalCapacity() ); ?><br />
					<?php } ?>
					<?php esc_html_e( 'Adults:', 'motopress-hotel-booking' ); ?>&nbsp;<?php echo esc_html( $roomType->getAdultsCapacity() ); ?><br />
					<?php esc_html_e( 'Children:', 'motopress-hotel-booking' ); ?>&nbsp;<?php echo esc_html( $roomType->getChildrenCapacity() ); ?><br />
                    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_html_e( 'Size:', 'motopress-hotel-booking' );
					?>
					&nbsp;<?php echo ! empty( $roomSize ) ? esc_html( $roomType->getSize( true ) ) : self::EMPTY_VALUE_PLACEHOLDER; ?>
				</p>
				<?php
				break;
			case 'bed':
				$bedType = $roomType->getBedType();
				if ( ! empty( $bedType ) ) {
					printf( '<a href="%s">%s</a>', esc_url( add_query_arg( 'mphb_bed', urlencode( $bedType ) ) ), esc_html( $bedType ) );
				} else {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<span aria-hidden="true">' . static::EMPTY_VALUE_PLACEHOLDER . '</span>';
				}
				break;
			case 'rooms':
				$totalRoomsCount  = MPHB()->getRoomPersistence()->getCount(
					array(
						'room_type_id' => $roomType->getOriginalId(),
						'post_status'  => 'all',
					)
				);
				$activeRoomsCount = MPHB()->getRoomPersistence()->getCount(
					array(
						'room_type_id' => $roomType->getOriginalId(),
						'post_status'  => 'publish',
					)
				);

				$totalRoomsLink  = MPHB()->postTypes()->room()->getManagePage()->getUrl(
					array(
						'mphb_room_type_id' => $roomType->getOriginalId(),
					)
				);
				$activeRoomsLink = MPHB()->postTypes()->room()->getManagePage()->getUrl(
					array(
						'mphb_room_type_id' => $roomType->getOriginalId(),
						'post_status'       => 'publish',
					)
				);
				?>
				<p>
					<?php esc_html_e( 'Total:', 'motopress-hotel-booking' ); ?>
					<a href="<?php echo esc_url( $totalRoomsLink ); ?>">
						<?php echo esc_html( $totalRoomsCount ); ?>
					</a><br/>
					<?php esc_html_e( 'Active:', 'motopress-hotel-booking' ); ?>
					<a href="<?php echo esc_url( $activeRoomsLink ); ?>">
						<?php echo esc_html( $activeRoomsCount ); ?>
					</a>
				</p>
				<?php
				break;
		}
	}

	/**
	 *
	 * @param array $actions
	 * @return array
	 */
	public function filterRowActions( $actions ) {

		if ( ! $this->isCurrentPage() ) {
			return $actions;
		}

		$customActions = array(
			'mphb_id' => sprintf( '<span style="color:#999">ID: %d</span>', get_the_ID() ),
		);

		return $customActions + $actions;
	}

	/**
	 *
	 * @param \WP_Query $query
	 */
	public function parseQuery( $query ) {
		if ( $this->isCurrentPage() && $query->is_main_query() ) {
			if ( isset( $_GET['mphb_bed'] ) && $_GET['mphb_bed'] != '' ) {
				$query->query_vars['meta_key']     = 'mphb_bed';
				$query->query_vars['meta_value']   = sanitize_text_field( wp_unslash( $_GET['mphb_bed'] ) );
				$query->query_vars['meta_compare'] = '=';
			}
		}
	}

	public function filterCustomOrderBy( $vars ) {
		if ( $this->isCurrentPage() ) {
			if ( isset( $vars['orderby'] ) ) {
				switch ( $vars['orderby'] ) {
					case 'mphb_bed':
						$vars = array_merge(
							$vars,
							array(
								'meta_key' => 'mphb_bed',
								'orderby'  => 'meta_value',
							)
						);
						break;
				}
			}
		}
		return $vars;
	}

}
